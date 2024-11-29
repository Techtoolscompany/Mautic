<?php

declare(strict_types=1);

namespace Page\Acceptance;

class SegmentsPage
{
    public static $URL                   = '/s/segments';
    public static $newButton             = '#toolbar > div.std-toolbar.btn-group > a > span > i';
    public static $segmentName           = '#leadlist_name';
    public static $saveAndCloseButton    = '#leadlist_buttons_save_toolbar';
    public static $detailsTab            = '//*[@href="#details"]';
    public static $filtersTab            = '//*[@href="#filters"]';
    public static $editButton            = '#toolbar > div.std-toolbar.btn-group > a:nth-child(1)';
}
