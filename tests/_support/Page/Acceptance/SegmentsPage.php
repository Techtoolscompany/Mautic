<?php

declare(strict_types=1);

namespace Page\Acceptance;

class SegmentsPage
{
    public static $URL                   = '/s/segments';
    public static $newButton             = '.list-toolbar > a#new > i';
    public static $segmentName           = '#leadlist_name';
    public static $saveAndCloseButton    = '#leadlist_buttons_save_toolbar';
    public static $detailsTab            = '//*[@href="#details"]';
    public static $filtersTab            = '//*[@href="#filters"]';
    public static $editButton            = '#toolbar > div.std-toolbar > a:nth-child(1)';
}
