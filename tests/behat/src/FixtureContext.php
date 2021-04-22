<?php

namespace SilverStripe\SessionManager\Tests\Behat\Context;

use Behat\Mink\Element\NodeElement;
use SilverStripe\BehatExtension\Context\FixtureContext as BaseFixtureContext;
use SilverStripe\Control\Controller;
use SilverStripe\Security\Member;
use SilverStripe\SessionManager\Model\LoginSession;

/**
 * Context used to create fixtures in the SilverStripe ORM.
 */
class FixtureContext extends BaseFixtureContext
{
    /**
     * @When /^I see the "([^"]+)" element$/
     * @param $selector
     */
    public function iSeeTheElement($selector): void
    {
        $page = $this->getMainContext()->getSession()->getPage();
        $element = $page->find('css', $selector);
        assertNotNull($element, sprintf('Element %s not found', $selector));
    }

    /**
     * @When /^I should see the text "([^"]+)" in the "([^"]+)" element$/
     * @param $selector
     */
    public function iShouldSeeTheTextInTheElement(string $text, string $selector): void
    {
        $page = $this->getMainContext()->getSession()->getPage();
        /** @var NodeElement $element */
        $element = $page->find('css', $selector);
        assertNotNull($element, sprintf('Element %s not found', $selector));
        assertSame($text, $element->getText());
    }

    /**
     * @Given /^There is a login session for a second device$/
     */
    public function thereIsALoginSessionForASecondDevice(): void
    {
        $loginSession = $this->createLoginSession();
        $loginSession->UserAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows 98)';
        $loginSession->write();
    }

    private function createLoginSession(): LoginSession
    {
        $request = Controller::curr()->getRequest();
        return LoginSession::generate($this->getMember(), false, $request);
    }

    private function getMember(): Member
    {
        /** @var Member $member */
        $member = Member::get()->find('FirstName', 'ADMIN');
        return $member;
    }
}
