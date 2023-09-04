<?php

declare(strict_types=1);

namespace Mautic\LeadBundle\Tests\Command;

use Exception;
use Mautic\CoreBundle\Test\MauticMysqlTestCase;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Entity\LeadList;
use Mautic\LeadBundle\Entity\LeadListRepository;
use Mautic\LeadBundle\Entity\LeadRepository;
use Mautic\LeadBundle\Entity\ListLead;

class SegmentFilterOnUpdateCommandFunctionalTest extends MauticMysqlTestCase
{
    /**
     * @return iterable<string, string[]>
     */
    public function segmentMembershipFilterProvider(): iterable
    {
        yield 'Classic Segment Membership Filter' => ['leadlist'];
        yield 'Static Segment Membership Filter' => ['leadlist_static'];
    }

    /**
     * @dataProvider segmentMembershipFilterProvider
     */
    public function testSegmentFilterOnUpdateCommand(string $filterField): void
    {
        $this->saveContacts();
        $segmentA   = $this->saveSegmentA();
        $segmentAId = $segmentA->getId();

        // Run segments update command.
        $this->runCommand('mautic:segments:update', ['-i' => $segmentAId]);
        self::assertCount(5, $this->em->getRepository(ListLead::class)->findBy(['list' => $segmentAId]));

        $segmentB   = $this->saveSegmentB($segmentAId, $filterField);
        $segmentBId = $segmentB->getId();
        // Run segments update command.
        $this->runCommand('mautic:segments:update', ['-i' => $segmentBId]);
        self::assertCount(3, $this->em->getRepository(ListLead::class)->findBy(['list' => $segmentBId]));
    }

    private function saveContacts(): array
    {
        // Add 10 contacts
        /** @var LeadRepository $contactRepo */
        $contactRepo = $this->em->getRepository(Lead::class);
        $contacts    = [];

        for ($i = 0; $i <= 10; ++$i) {
            $contact = new Lead();
            $contact->setFirstname('fn'.$i);
            $contact->setLastname('ln'.$i);
            $contacts[] = $contact;
        }

        $contactRepo->saveEntities($contacts);

        return $contacts;
    }

    private function saveSegmentA(): LeadList
    {
        // Add 1 segment
        /** @var LeadListRepository $contactRepo */
        $segmentRepo = $this->em->getRepository(LeadList::class);
        $segment     = new LeadList();
        $filters     = [
            [
                'object'     => 'lead',
                'glue'       => 'and',
                'field'      => 'address1',
                'type'       => 'text',
                'operator'   => '!empty',
                'properties' => ['filter' => null],
                // The filter key is deprecated but sometimes it contains rubbish values including a string.
                'filter'     => 'somestring',
            ],
            [
                'object'     => 'lead',
                'glue'       => 'and',
                'field'      => 'address1',
                'type'       => 'text',
                'operator'   => '!=',
                'properties' => ['filter' => null],
                // The filter key is deprecated but sometimes it contains rubbish values including an array.
                'filter'     => ['option A', 'option B'],
            ],
            [
                'object'     => 'lead',
                'glue'       => 'and',
                'field'      => 'firstname',
                'type'       => 'text',
                'operator'   => '=',
                'properties' => ['filter' => 'fn1'],
            ],
            [
                'object'     => 'lead',
                'glue'       => 'or',
                'field'      => 'lastname',
                'type'       => 'text',
                'operator'   => '=',
                'properties' => ['filter' => 'ln1'],
            ],
            [
                'object'     => 'lead',
                'glue'       => 'or',
                'field'      => 'firstname',
                'type'       => 'text',
                'operator'   => '=',
                'properties' => ['filter' => 'fn2'],
            ],
            [
                'object'     => 'lead',
                'glue'       => 'or',
                'field'      => 'firstname',
                'type'       => 'text',
                'operator'   => '=',
                'properties' => ['filter' => 'fn3'],
            ],
            [
                'object'     => 'lead',
                'glue'       => 'and',
                'field'      => 'lastname',
                'type'       => 'text',
                'operator'   => '=',
                'properties' => ['filter' => 'ln3'],
            ],
            [
                'object'     => 'lead',
                'glue'       => 'or',
                'field'      => 'firstname',
                'type'       => 'text',
                'operator'   => '=',
                'properties' => ['filter' => 'fn4'],
            ],
            [
                'object'     => 'lead',
                'glue'       => 'or',
                'field'      => 'lastname',
                'type'       => 'text',
                'operator'   => '=',
                'properties' => ['filter' => 'ln5'],
            ],
        ];

        $segment->setName('Segment A')
            ->setFilters($filters)
            ->setAlias('segment-a');
        $segmentRepo->saveEntity($segment);

        return $segment;
    }

    private function saveSegmentB(int $segmentAId, string $filterField): LeadList
    {
        // Add 1 segment
        /** @var LeadListRepository $contactRepo */
        $segmentRepo = $this->em->getRepository(LeadList::class);
        $segment     = new LeadList();
        $filters     = [
            [
                'object'     => 'lead',
                'glue'       => 'and',
                'field'      => 'firstname',
                'type'       => 'text',
                'operator'   => '=',
                'properties' => ['filter' => 'fn6'],
            ],
            [
                'object'     => 'lead',
                'glue'       => 'or',
                'field'      => 'firstname',
                'type'       => 'text',
                'operator'   => '=',
                'properties' => ['filter' => 'fn2'],
            ],
            [
                'object'     => 'lead',
                'glue'       => 'or',
                'field'      => 'firstname',
                'type'       => 'text',
                'operator'   => '=',
                'properties' => ['filter' => 'fn3'],
            ],
            [
                'object'     => 'lead',
                'glue'       => 'and',
                'field'      => 'lastname',
                'type'       => 'text',
                'operator'   => '=',
                'properties' => ['filter' => 'ln3'],
            ],
            [
                'glue'       => 'and',
                'field'      => $filterField,
                'object'     => 'lead',
                'type'       => 'leadlist',
                'operator'   => 'in',
                'properties' => ['filter' => [$segmentAId]],
            ],
        ];

        $segment->setName('Segment B')
            ->setFilters($filters)
            ->setAlias('segment-b');
        $segmentRepo->saveEntity($segment);

        return $segment;
    }
}
