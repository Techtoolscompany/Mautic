<?php

use Page\Acceptance\SegmentsPage;
use Step\Acceptance\SegmentStep;

class SegmentManagementCest
{
    public function _before(AcceptanceTester $I): void
    {
        $I->login('admin', 'Maut1cR0cks!');
    }

    public function createSegment(
        AcceptanceTester $I,
        SegmentStep $segment
    ): void {
        $segment->createAContactSegment('testSegment');
        $I->waitForElementVisible('.page-header-title', 30);
        $I->wait(2);
        $I->seeInDatabase('test_lead_lists', ['name' => 'testSegment']);
    }

    public function editSegment(
        AcceptanceTester $I,
        SegmentStep $segment
    ): void {
        $I->amOnPage(SegmentsPage::$URL);
        $segmentName  = explode('(', $segment->grabSegmentNameFromList(1))[0];
        $segmentAlias = '('.explode('(', $segment->grabSegmentNameFromList(1))[1];
        $I->click(['link' => $segmentName.$segmentAlias]);

        $I->waitForText($segmentName, 30);
        $I->see($segmentName);

        // Click on the edit button
        $I->click(SegmentsPage::$editButton);

        // Wait for the edit form to be visible
        $I->waitForText('Edit Segment', 30);

        $I->fillField(SegmentsPage::$segmentName, 'Edited-Segment-Name');

        // Save and close the form
        $I->waitForElementClickable(SegmentsPage::$saveAndCloseButton, 30);
        $I->click(SegmentsPage::$saveAndCloseButton);

        // Verify the update message
        $I->waitForText("Edited-Segment-Name $segmentAlias has been updated!", 30);
    }
}
