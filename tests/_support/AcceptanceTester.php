<?php

use Page\Acceptance\CategoriesPage;

/**
 * Inherited Methods.
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    public function login($name, $password): void
    {
        $I = $this;
        // if snapshot exists - skipping login
        if ($I->loadSessionSnapshot('login')) {
            return;
        }
        // logging in
        $I->amOnPage('/s/login');
        $I->fillField('#username', $name);
        $I->fillField('#password', $password);
        $I->click('button[type=submit]');
        $I->waitForElement('h1.page-header-title', 30);
        // saving snapshot
        $I->saveSessionSnapshot('login');
    }

    public function createACategory(string $name): void
    {
        $this->amOnPage(CategoriesPage::$URL);
        $this->waitForElementClickable(CategoriesPage::$NEW_BUTTON);
        $this->click(CategoriesPage::$NEW_BUTTON);
        $this->wait(2);
        $this->waitForElementClickable(CategoriesPage::$BUNDLE_DROPDOWN);
        $this->click(CategoriesPage::$BUNDLE_DROPDOWN);
        $this->wait(2);
        $this->waitForElementClickable(CategoriesPage::$BUNDLE_EMAIL_OPTION);
        $this->click(CategoriesPage::$BUNDLE_EMAIL_OPTION);
        $this->wait(2);
        $this->fillField(CategoriesPage::$TITLE_FIELD, $name);
        $this->click(CategoriesPage::$SAVE_AND_CLOSE);
        $this->wait(2);
    }
}
