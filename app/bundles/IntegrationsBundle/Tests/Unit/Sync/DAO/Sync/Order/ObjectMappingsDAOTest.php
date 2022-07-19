<?php

declare(strict_types=1);

/*
 * @copyright   2020 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://www.mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\IntegrationsBundle\Tests\Unit\Sync\DAO\Sync\Order;

use Mautic\IntegrationsBundle\Entity\ObjectMapping;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectMappingsDAO;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class ObjectMappingsDAOTest extends TestCase
{
    public function testGetters(): void
    {
        $objectMappings = new ObjectMappingsDAO();

        $objectMappings->addNewObjectMapping((new ObjectMapping())->setIntegrationObjectName('foonew'));
        $objectMappings->addNewObjectMapping((new ObjectMapping())->setIntegrationObjectName('barnew'));
        $mappings = $objectMappings->getNewMappings();
        Assert::assertCount(2, $mappings);
        Assert::assertEquals('foonew', $mappings[0]->getIntegrationObjectName());
        Assert::assertEquals('barnew', $mappings[1]->getIntegrationObjectName());

        $objectMappings->addUpdatedObjectMapping((new ObjectMapping())->setIntegrationObjectName('fooupdate'));
        $objectMappings->addUpdatedObjectMapping((new ObjectMapping())->setIntegrationObjectName('barupdate'));
        $mappings = $objectMappings->getUpdatedMappings();
        Assert::assertCount(2, $mappings);
        Assert::assertEquals('fooupdate', $mappings[0]->getIntegrationObjectName());
        Assert::assertEquals('barupdate', $mappings[1]->getIntegrationObjectName());
    }
}
