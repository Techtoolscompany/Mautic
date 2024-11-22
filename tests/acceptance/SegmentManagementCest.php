<?php

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
        $I->wait(1);
        $I->seeInDatabase('test_lead_lists', ['name' => 'testSegment']);
    }
}
