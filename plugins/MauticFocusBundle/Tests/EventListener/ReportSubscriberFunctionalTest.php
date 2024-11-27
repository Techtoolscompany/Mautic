<?php

declare(strict_types=1);

namespace MauticPlugin\MauticFocusBundle\Tests\EventListener;

use Mautic\CoreBundle\Test\MauticMysqlTestCase;
use Mautic\IntegrationsBundle\Entity\FieldChange;
use Mautic\IntegrationsBundle\Entity\FieldChangeRepository;
use Mautic\IntegrationsBundle\Helper\SyncIntegrationsHelper;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\PageBundle\Entity\Redirect;
use Mautic\PageBundle\Entity\Trackable;
Use MauticPlugin\MauticFocusBundle\Entity\Focus;
use Mautic\LeadBundle\Event\LeadEvent;
use Mautic\LeadBundle\LeadEvents;
use MauticPlugin\MauticFocusBundle\Entity\Stat;
use PHPUnit\Framework\Assert;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class ReportSubscriberFunctionalTest extends MauticMysqlTestCase
{
    private EventDispatcherInterface $dispatcher;

    private FieldChangeRepository $fieldChangeRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dispatcher = static::getContainer()->get('event_dispatcher');
        $this->fieldChangeRepository = $this->em->getRepository(FieldChange::class);

        static::getContainer()->set(
            'mautic.integrations.helper.sync_integrations',
            new class() extends SyncIntegrationsHelper {
                public function __construct()
                {
                }

                public function hasObjectSyncEnabled(string $object): bool
                {
                    return true;
                }

                public function getEnabledIntegrations()
                {
                    return ['unicorn'];
                }
            }
        );
    }

    public function testFocusItemReportPostSave(): void
    {
        // The contact must exist in the database in order to create a reference later.
        //focus, focus_stats, redirects, trackables --> in DB übertragen
        //Event mit richtigen Daten Dispatchen
        //Testen, ob korrekte Daten in ReportDataEvent
        $focusItem1 = new Focus();
        $focusItem2 = new Focus();
        $focusItem3 = new Focus();
        $this->em->persist($focusItem1);
        $this->em->persist($focusItem2);
        $this->em->persist($focusItem3);
        $this->em->flush();
        $this->em->clear();

        // By getting a reference we'll get a proxy class instead of the real entity class.
        /** @var Lead $contactProxy */
        $contactProxy = $this->em->getReference(Lead::class, $contactReal->getId());
        $contactProxy->__set('email', 'john@doe.email');
        $contactProxy->setPoints(100);
        $event = new LeadEvent($contactProxy, true);

        $this->dispatcher->dispatch($event, LeadEvents::LEAD_POST_SAVE);

        $fieldChanges = $this->fieldChangeRepository->findChangesForObject('unicorn', Lead::class, $contactReal->getId());
        Assert::assertCount(2, $fieldChanges, print_r($fieldChanges, true));

        Assert::assertSame('unicorn', $fieldChanges[0]['integration']);
        Assert::assertSame($contactReal->getId(), (int)$fieldChanges[0]['object_id']);
        Assert::assertSame(Lead::class, $fieldChanges[0]['object_type']);
        Assert::assertSame('email', $fieldChanges[0]['column_name']);
        Assert::assertSame('string', $fieldChanges[0]['column_type']);
        Assert::assertSame('john@doe.email', $fieldChanges[0]['column_value']);

        Assert::assertSame('unicorn', $fieldChanges[1]['integration']);
        Assert::assertSame($contactReal->getId(), (int)$fieldChanges[1]['object_id']);
        Assert::assertSame(Lead::class, $fieldChanges[1]['object_type']);
        Assert::assertSame('points', $fieldChanges[1]['column_name']);
        Assert::assertSame('int', $fieldChanges[1]['column_type']);
        Assert::assertSame('100', $fieldChanges[1]['column_value']);
    }

    private function createTrackable(string $url, int $channelId, int $hits = 0, int $uniqueHits = 0): Trackable
    {
        $redirect = new Redirect();
        $redirect->setRedirectId(uniqid());
        $redirect->setUrl($url);
        $redirect->setHits($hits);
        $redirect->setUniqueHits($uniqueHits);
        $this->em->persist($redirect);

        $trackable = new Trackable();
        $trackable->setChannelId($channelId);
        $trackable->setChannel('email');
        $trackable->setHits($hits);
        $trackable->setUniqueHits($uniqueHits);
        $trackable->setRedirect($redirect);
        $this->em->persist($trackable);

        return $trackable;
    }


    public function createFocusItem($name, $description, $focusType, $style)
    {
        $focus = new Focus();
        $focus->setName($name);
        $focus->setDescription($description);
        $focus->setFocusType($focusType);
        $focus->setStyle($style);

        $this->em->persist($focus);
    }

    public function createFocusStats($type, $focus)
    {
        $focusStats = new Stat();
        $focusStats->setType($type);
        $focusStats->setFocus($focus->getId());
        $this->em->persist($focusStats);
    }

    private function createContact(string $email): Lead
    {
        $contact = new Lead();
        $contact->setEmail($email);
        $this->em->persist($contact);

        return $contact;
    }

    public function fillDatabase()
    {
        $this->createContact('abc@example.com');
        $this->createContact('abcd@example.com');
        $this->em->flush();

        $this->createFocusItem('FocusItem1', 'doesAbc', 'link', 'modal');
        $this->createFocusItem('FocusItem2', 'doesAbcd', 'link', 'modal');
        $this->em->flush();
        //Hier ID erhalten

        $this->createFocusStats();


        $this->createFocusItem('FocusItem3');
        $this->em->flush();
        $this->em->clear();
    }
}

//self::PREFIX_TRACKABLES.'.channel_id = '.self::PREFIX_STATS.'.focus_id

//Ein Trackable und ein Redirect pro Focus item

//Zwei Contacts
//Zwei Focus items

//focus item1 -->

