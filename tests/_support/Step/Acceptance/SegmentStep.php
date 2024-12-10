<?php

namespace Step\Acceptance;

use Page\Acceptance\SegmentsPage;

class SegmentStep extends \AcceptanceTester
{
    /**
     * Create a contact segment with the given name.
     */
    public function createAContactSegment(string $name): void
    {
        $I=$this;
        $I->amOnPage(SegmentsPage::$URL);
        $I->waitForElementClickable(SegmentsPage::$newButton);
        $I->click(SegmentsPage::$newButton);
        $I->waitForElementVisible(SegmentsPage::$segmentName);
        $I->fillField(SegmentsPage::$segmentName, $name);
        $I->click(SegmentsPage::$saveAndCloseButton);
    }

    /**
     * Grab the name of a segment from the segment list.
     *
     * @return string the name of the segment
     */
    public function grabSegmentNameFromList($place)
    {
        $I           = $this;
        $xpath       = "//*[@id='leadListTable']/tbody/tr[$place]/td[2]/div/a";
        $segmentName = $I->grabTextFrom($xpath);
        $I->see($segmentName, $xpath);

        return $segmentName;
    }
}
