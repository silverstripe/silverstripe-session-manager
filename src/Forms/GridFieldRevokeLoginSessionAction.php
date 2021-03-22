<?php

namespace SilverStripe\SessionManager\Forms;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\ValidationException;
use SilverStripe\SessionManager\Security\LogInAuthenticationHandler;
use SilverStripe\View\Requirements;

class GridFieldRevokeLoginSessionAction implements GridField_ColumnProvider, GridField_ActionProvider
{
    use Injectable;

    /**
     * @param GridField $gridField
     * @param array $columns
     */
    public function augmentColumns($gridField, &$columns)
    {
        if (!in_array('Actions', $columns)) {
            $columns[] = 'Actions';
        }
    }

    /**
     * @param GridField $gridField
     * @param DataObject $record
     * @param string $columnName
     * @return array
     */
    public function getColumnAttributes($gridField, $record, $columnName)
    {
        return ['class' => 'grid-field__col-compact'];
    }

    /**
     * @param GridField $gridField
     * @param string $columnName
     * @return array
     */
    public function getColumnMetadata($gridField, $columnName)
    {
        if ($columnName == 'Actions') {
            return ['title' => 'Revoke'];
        }
    }

    /**
     * @param GridField $gridField
     * @return array
     */
    public function getColumnsHandled($gridField)
    {
        return ['Actions'];
    }

    /**
     * @param GridField
     * @return array
     */
    public function getActions($gridField)
    {
        return ['revoke'];
    }

    /**
     * @param GridField $gridField
     * @param DataObject $record
     * @param string $columnName
     * @return string
     */
    public function getColumnContent($gridField, $record, $columnName)
    {
        Requirements::javascript(
            'silverstripe/session-manager:client/dist/js/GridFieldRevokeLoginSessionAction.js'
        );

        if (!$record->canDelete()) {
            return null;
        }

        $loginHandler = Injector::inst()->get(LogInAuthenticationHandler::class);
        $request = Injector::inst()->get(HTTPRequest::class);
        $loginSessionID = $request->getSession()->get($loginHandler->getSessionVariable());
        $field = GridField_FormAction::create(
            $gridField,
            'Revoke' . $record->ID,
            'Revoke Session',
            'revoke',
            ['RecordID' => $record->ID]
        )->addExtraClass('gridfield-button-revoke-session btn font-icon-cancel-circled btn-sm btn-outline-danger')
            ->setAttribute('title', 'Revoke Session')
            ->setDescription('Revoke Session');

        if ((int)$record->ID === (int)$loginSessionID) {
            $field->setAttribute('data-current-session', true);
        }

        return $field->Field();
    }

    /**
     * @param GridField $gridField
     * @param string $actionName
     * @param array $arguments
     * @param array $data
     * @throws ValidationException
     */
    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        /** @var DataList $list */
        $list = $gridField->getList();
        if (!($list instanceof DataList)) {
            return;
        }
        $item = $list->byID($arguments['RecordID']);
        if (!$item) {
            return;
        }

        if (!$item->canDelete()) {
            throw new ValidationException(
                _t(__CLASS__ . '.DeletePermissionsFailure', "No delete permissions")
            );
        }

        $item->delete();
    }
}
