<?php

namespace MauticPlugin\MauticFocusBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Mautic\LeadBundle\Model\CompanyReportData;
use Mautic\ReportBundle\ReportEvents;
use Mautic\ReportBundle\Event\ReportBuilderEvent;
use Mautic\ReportBundle\Event\ReportDataEvent;
use Mautic\ReportBundle\Event\ReportGeneratorEvent;
use Symfony\Component\Routing\RouterInterface;


class ReportSubscriber implements EventSubscriberInterface
{
    const CONTEXT_FOCUS_STATS = 'focus_stats';
    const FOCUS_GROUP = 'focus';
    const   PREFIX_FOCUS = 'f';
    const PREFIX_STATS = 'fs';
    const PREFIX_REDIRECTS = 'r';
    const PREFIX_TRACKABLES = 't';



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


        $columns = [
            self::PREFIX_FOCUS . '.name' => [
                'label' => 'mautic.core.name',
                'type' => 'html',
            ],
            self::PREFIX_FOCUS . '.description' => [
                'label' => 'mautic.core.description',
                'type' => 'html',
            ],
            self::PREFIX_FOCUS . '.focus_type' => [
                'label' => 'mautic.focus.thead.type',
                'type' => 'html',
            ],
            self::PREFIX_FOCUS . '.style' => [
                'label' => 'mautic.focus.tab.focus_style',
                'type' => 'html',
            ],
            self::PREFIX_STATS . '.type' => [
                'label' => 'mautic.focus.interaction',
                'type' => 'html',
            ],
            self::PREFIX_TRACKABLES . '.hits' => [
                'label' => 'pagehits',
                'type' => 'html',
            ],
            self::PREFIX_TRACKABLES . '.unique_hits' => [
                'label' => 'uniquehits',
                'type' => 'html',
            ],
            self::PREFIX_REDIRECTS . '.url' => [
                'label' => 'url',
                'type' => 'html',
            ]
        ];

        if ($event->checkContext(self::CONTEXT_FOCUS_STATS)) {

            $data = [
                'display_name' => 'mautic.focus.graph.stats',
                'columns' => $columns,
            ];
            $context = self::CONTEXT_FOCUS_STATS;

            // Register table
            $event->addTable($context, $data, self::FOCUS_GROUP);
        }
    }
    /**
     * Initialize the QueryBuilder object to generate reports from.
     */
    public function onReportGenerate(ReportGeneratorEvent $event)
    {
        if ($event->checkContext([self::CONTEXT_FOCUS_STATS])) {
            $queryBuilder = $event->getQueryBuilder();
            $queryBuilder->from(MAUTIC_TABLE_PREFIX . 'focus_stats', self::PREFIX_STATS)
                ->leftJoin('fs', MAUTIC_TABLE_PREFIX . 'focus', self::PREFIX_FOCUS, 'f.id = fs.focus_id')
                ->leftJoin('fs', MAUTIC_TABLE_PREFIX . 'channel_url_trackables', self::PREFIX_TRACKABLES, 't.channel_id = fs.focus_id')
                ->leftJoin('fs', MAUTIC_TABLE_PREFIX . 'page_redirects', self::PREFIX_REDIRECTS, 'r.id = t.redirect_id');
            $event->applyDateFilters($queryBuilder, 'date_added', self::PREFIX_STATS);
            $event->setQueryBuilder($queryBuilder);
        }
    }

    public function onReportDisplay(ReportDataEvent $event)
    {
        $data = $event->getData();
        if ($event->checkContext([self::CONTEXT_FOCUS_STATS])) {
            if (isset($data[0]['channel']) && isset($data[0]['channel_id'])) {
                foreach ($data as &$row) {
                    $href = 'bla';
                    if (isset($row['channel'])) {
                        $row['channel'] = '<a href="' . $href . '">' . $row['channel'] . '</a>';
                    }
                    unset($row);
                }
            }
        }

        $event->setData($data);
        unset($data);
    }
}