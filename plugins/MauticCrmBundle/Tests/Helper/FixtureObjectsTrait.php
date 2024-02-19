<?php

declare(strict_types=1);

/*
 * @copyright   2019 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        http://mautic.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticCrmBundle\Tests\Helper;

use Doctrine\ORM\EntityManager;
use Mautic\CoreBundle\Entity\CommonEntity;
use MauticPlugin\CustomObjectsBundle\Entity\UniqueEntityInterface;
use MauticPlugin\CustomObjectsBundle\Exception\NotFoundException;
use MauticPlugin\CustomObjectsBundle\Tests\Functional\Exception\FixtureNotFoundException;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Trait FixtureObjectsTrait implements Liip fixtures with Alice and offers helper methods for handling them.
 */
trait FixtureObjectsTrait
{
    /**
     * @var mixed[]
     */
    private $objects = [];

    /**
     * @var string[]
     */
    private $entityMap = [];

    /**
     * The result of loadFixtureFiles should be passed here to initialize the thread.
     *
     * @param mixed[] $objects
     */
    public function setFixtureObjects(array $objects): void
    {
        foreach ($objects as $key => $object) {
            $this->objects[get_class($object)][$key] = $object;
            $this->entityMap[$key]                   = get_class($object);
        }
    }

    /**
     * @param string $type
     *
     * @return mixed[]
     *
     * @throws FixtureNotFoundException
     */
    public function getFixturesByEntityClassName(string $type): array
    {
        if (!isset($this->objects[$type])) {
            throw new FixtureNotFoundException('No fixtures of type '.$type.' defined');
        }

        return $this->objects[$type];
    }

    /**
     * @param string $type
     * @param int    $index
     *
     * @return CommonEntity
     *
     * @throws FixtureNotFoundException
     */
    public function getFixtureByEntityClassNameAndIndex(string $type, int $index): CommonEntity
    {
        $fixtures = $this->getFixturesByEntityClassName($type);

        $fixtures = array_values($fixtures);

        if (!isset($fixtures[$index])) {
            throw new FixtureNotFoundException('No index "'.$index.'" defined of '.$type.'\'s');
        }

        return $fixtures[$index];
    }

    /**
     * @param string $id key specified in fixtures
     *
     * @return UniqueEntityInterface
     *
     * @throws FixtureNotFoundException
     */
    public function getFixtureById(string $id): UniqueEntityInterface
    {
        if (!isset($this->entityMap[$id])) {
            throw new FixtureNotFoundException('No fixture with id "'.$id.'"" defined');
        }

        return $this->objects[$this->entityMap[$id]][$id];
    }

    /**
     * @return mixed[]
     */
    public function getFixturesInUnloadableOrder()
    {
        $entities = [];

        $orderedKeys = $this->entityMap;
        array_reverse($orderedKeys, true);
        foreach ($orderedKeys as $key => $type) {
            $entities[$key] = $this->objects[$type][$key];
        }

        return $entities;
    }

    /**
     * @return string
     */
    private function getFixturesDirectory($bundleName)
    {
        /** @var KernelInterface $kernel */
        $kernel          = $this->getContainer()->get('kernel');
        $pluginDirectory = $kernel->locateResource('@'.$bundleName);

        if (!is_string($pluginDirectory)) {
            throw new NotFoundException("Resource $bundleName not found.");
        }

        return $pluginDirectory.'/Tests/Functional/DataFixtures/ORM/Data';
    }

    protected function tearDownFixtures(EntityManager $entityManager): void
    {
        foreach ($this->getFixturesInUnloadableOrder() as $entity) {
            $entityManager->remove($entity);
        }

        $entityManager->flush();
    }
}
