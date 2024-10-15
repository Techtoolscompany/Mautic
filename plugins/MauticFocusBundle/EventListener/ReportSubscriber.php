<?php

namespace MauticPlugin\MauticFocusBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Mautic\LeadBundle\Model\CompanyReportData;
use Mautic\ReportBundle\ReportEvents;

class ReportSubscriber implements EventSubscriberInterface
{
    const CONTEXT_FOCUS_STATS = 'focus_stats';
    /**
     * @var CompanyReportData
     */
    private $companyReportData;

    public function __construct(CompanyReportData $companyReportData)
    {
        $this->companyReportData = $companyReportData;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ReportEvents::REPORT_ON_BUILD => ['onReportBuilder', 0],
            ReportEvents::REPORT_ON_GENERATE => ['onReportGenerate', 0],
            ReportEvents::REPORT_ON_DISPLAY => ['onReportDisplay', 0],
        ];
    }

    /**
     * Add available tables and columns to the report builder lookup.
     */
    public function onReportBuilder(ReportBuilderEvent $event)
    {
        if (!$event->checkContext(self::CONTEXT_FOCUS_STATS)) {
            return;
        }

        $prefix = 'f.'; //soft coden
        $prefixStats = 'fs.';
        $prefixRedirects = 'r.';
        $prefixTrackables = 't.';
        $columns = [
            $prefix . 'name' => [
                'label' => 'mautic.core.name',
                'type' => 'html',
            ],
            $prefix . 'description' => [
                'label' => 'mautic.core.description',
                'type' => 'html',
            ],
            $prefix . 'focus_type' => [
                'label' => 'mautic.focus.thead.type',
                'type' => 'html',
            ],
            $prefix . 'style' => [
                'label' => 'mautic.focus.tab.focus_style',
                'type' => 'html',
            ],
            $prefixStats . 'type' => [
                'label' => 'mautic.focus.interaction',
                'type' => 'html',
            ],
            $prefixTrackables . 'hits' => [
                'label' => 'pagehits',
                'type' => 'html',
            ],
            $prefixTrackables . 'unique_hits' => [
                'label' => 'uniquehits',
                'type' => 'html',
            ],
            $prefixRedirects . 'url' => [
                'label' => 'url',
                'type' => 'html',
            ]
        ];

        $event->addTable(
            self::CONTEXT_FOCUS,
            [
                'display_name' => 'mautic.focus',
                'columns' => $columns,
            ]
        );

        if ($event->checkContext(self::CONTEXT_FOCUS_STATS)) {

            $data = [
                'display_name' => 'mautic.focus.graph.stats',
                'columns' => $columns,
            ];
            $context = self::CONTEXT_FOCUS_STATS;

            // Register table
            $event->addTable($context, $data, self::CONTEXT_FOCUS);
        }
    }
}