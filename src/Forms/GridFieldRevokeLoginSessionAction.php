<?php

namespace SilverStripe\SessionManager\Forms;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\ORM\ValidationException;
use SilverStripe\SessionManager\Security\LogInAuthenticationHandler;
use SilverStripe\View\Requirements;

class GridFieldRevokeLoginSessionAction implements GridField_ColumnProvider, GridField_ActionProvider
{
    use Injectable;

    public function augmentColumns($gridField, &$columns)
    {
        if (!in_array('Actions', $columns)) {
            $columns[] = 'Actions';
        }
    }

    public function getColumnAttributes($gridField, $record, $columnName)
    {
        return ['class' => 'grid-field__col-compact'];
    }

    public function getColumnMetadata($gridField, $columnName)
    {
        if ($columnName == 'Actions') {
            return ['title' => 'Revoke'];
        }
    }

    public function getColumnsHandled($gridField)
    {
        return ['Actions'];
    }

    public function getActions($gridField)
    {
        return ['revoke'];
    }

    public function getColumnContent($gridField, $record, $columnName)
    {
        Requirements::javascript('silverstripe/silverstripe-session-manager:client/dist/js/GridFieldRevokeLoginSessionAction.js');

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

    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        $item = $gridField->getList()->byID($arguments['RecordID']);
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
