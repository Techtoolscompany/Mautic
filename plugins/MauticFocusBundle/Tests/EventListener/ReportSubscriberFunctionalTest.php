<?php

declare(strict_types=1);

namespace MauticPlugin\MauticFocusBundle\Tests\EventListener;

use Mautic\CoreBundle\Test\MauticMysqlTestCase;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\PageBundle\Entity\Redirect;
use Mautic\PageBundle\Entity\Trackable;
use Mautic\ReportBundle\Entity\Report;
use MauticPlugin\MauticFocusBundle\Entity\Focus;
use MauticPlugin\MauticFocusBundle\Entity\Stat;
use MauticPlugin\MauticFocusBundle\EventListener\ReportSubscriber;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;

final class ReportSubscriberFunctionalTest extends MauticMysqlTestCase
{
    public function testEmailReportGraphWithMostClickedLinks(): void
    {
        $this->fillDatabase();

        $report = new Report();
        $report->setName('Focus Stats Report');
        $report->setSource('focus_stats');
        $report->setColumns([ReportSubscriber::PREFIX_FOCUS.'.name', ReportSubscriber::PREFIX_FOCUS.'.description', ReportSubscriber::PREFIX_FOCUS.'.focus_type', ReportSubscriber::PREFIX_FOCUS.'.style', ReportSubscriber::PREFIX_STATS.'.type', ReportSubscriber::PREFIX_TRACKABLES.'.hits', ReportSubscriber::PREFIX_TRACKABLES.'.unique_hits', ReportSubscriber::PREFIX_REDIRECTS.'.url',
        ]);
        $this->em->persist($report);
        $this->em->flush();

        $crawler      = $this->client->request(Request::METHOD_GET, "/s/reports/view/{$report->getId()}");
        $this->assertTrue($this->client->getResponse()->isOk());
        $crawlerTable = $crawler->filter('#reportTable');
        $table        = array_slice($this->domTableToArray($crawlerTable), 1);
        array_pop($table);

        $this->assertSame([
            ['1', 'FocusItem1', 'doesAbc', 'link', 'modal', 'click', '1', '1', 'http://example1.com'],
            ['2', 'FocusItem1', 'doesAbc', 'link', 'modal', 'view', '3', '2', 'http://example1.com'],
            ['3', 'FocusItem2', 'doesAbcd', 'link', 'modal', 'click', '2', '1', 'http://example2.com'],
            ['4', 'FocusItem2', 'doesAbcd', 'link', 'modal', 'view', '1', '1', 'http://example2.com'],
        ], $table);
    }

    private function createTrackableAndRedirects(string $url, int $channelId, int $hits = 0, int $uniqueHits = 0): Trackable
    {
        $redirect = new Redirect();
        $redirect->setRedirectId(uniqid());
        $redirect->setUrl($url);
        $redirect->setHits($hits);
        $redirect->setUniqueHits($uniqueHits);
        $this->em->persist($redirect);

        $trackable = new Trackable();
        $trackable->setChannelId($channelId);
        $trackable->setChannel('focus');
        $trackable->setHits($hits);
        $trackable->setUniqueHits($uniqueHits);
        $trackable->setRedirect($redirect);
        $this->em->persist($trackable);

        return $trackable;
    }

    public function createFocusItem(string $name, string $description, string $focusType, string $style): void
    {
        $focus = new Focus();
        $focus->setName($name);
        $focus->setDescription($description);
        $focus->setType($focusType);
        $focus->setStyle($style);

        $this->em->persist($focus);
    }

    public function createFocusStats(string $type, Focus $focus, Lead $lead, \DateTime $dateAdded): void
    {
        $focusStats = new Stat();
        $focusStats->setType($type);
        $focusStats->setFocus($focus);
        $focusStats->setLead($lead);
        $focusStats->setDateAdded($dateAdded);
        $this->em->persist($focusStats);
    }

    private function createContact(string $email): Lead
    {
        $contact = new Lead();
        $contact->setEmail($email);
        $this->em->persist($contact);

        return $contact;
    }

    public function fillDatabase(): void
    {
        $this->createContact('abc@example.com');
        $this->createContact('abcd@example.com');
        $this->em->flush();

        $lead1 = $this->em->getRepository(Lead::class)->findOneBy(['email' => 'abc@example.com']);
        $lead2 = $this->em->getRepository(Lead::class)->findOneBy(['email' => 'abcd@example.com']);

        if (!$lead1 || !$lead2) {
            throw new \RuntimeException('Failed to create test contacts');
        }

        $this->createFocusItem('FocusItem1', 'doesAbc', 'link', 'modal');
        $this->createFocusItem('FocusItem2', 'doesAbcd', 'link', 'modal');
        $this->em->flush();

        $focus1 = $this->em->getRepository(Focus::class)->findOneBy(['name' => 'FocusItem1']);
        $focus2 = $this->em->getRepository(Focus::class)->findOneBy(['name' => 'FocusItem2']);

        if (!$focus1 || !$focus2) {
            throw new \RuntimeException('Failed to create focus items');
        }

        $date = new \DateTime();
        $this->createFocusStats('click', $focus1, $lead1, $date);
        $this->createFocusStats('click', $focus2, $lead2, $date);
        $this->createFocusStats('view', $focus1, $lead1, $date);
        $this->createFocusStats('view', $focus1, $lead1, $date);
        $this->createFocusStats('view', $focus1, $lead2, $date);
        $this->createFocusStats('view', $focus2, $lead2, $date);
        $this->em->flush();

        /** @var int $focusId1 */
        $focusId1 = $focus1->getId();
        /** @var int $focusId2 */
        $focusId2 = $focus2->getId();
        $this->createTrackableAndRedirects('http://example1.com', $focusId1, 1, 1);
        $this->createTrackableAndRedirects('http://example2.com', $focusId2, 2, 1);
        $this->em->flush();
        $this->em->clear();
    }

    /**
     * @return array<int,array<int,mixed>>
     */
    private function domTableToArray(Crawler $crawler): array
    {
        return $crawler->filter('tr')->each(fn ($tr) => $tr->filter('td')->each(fn ($td) => trim($td->text())));
    }
}
