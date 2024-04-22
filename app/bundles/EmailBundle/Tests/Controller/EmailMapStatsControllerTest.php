<?php

declare(strict_types=1);

namespace Mautic\EmailBundle\Tests\Controller;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Mautic\CoreBundle\Test\MauticMysqlTestCase;
use Mautic\EmailBundle\Controller\EmailMapStatsController;
use Mautic\EmailBundle\Entity\Email;
use Mautic\EmailBundle\Model\EmailModel;
use Mautic\UserBundle\Entity\Role;
use Mautic\UserBundle\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EmailMapStatsControllerTest extends MauticMysqlTestCase
{
    private MockObject $emailModelMock;

    private EmailMapStatsController $mapController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->emailModelMock = $this->createMock(EmailModel::class);
        $this->mapController  = new EmailMapStatsController($this->emailModelMock);
    }

    /**
     * @throws ORMException
     */
    public function testHasAccess(): void
    {
        $corePermissionsMock = $this->createMock(CorePermissions::class);

        $role = new Role();
        $role->setName('Example admin');
        $this->em->persist($role);
        $this->em->flush();

        $user = new User();
        $user->setFirstName('Example');
        $user->setLastName('Example');
        $user->setUsername('Example');
        $user->setPassword('123456');
        $user->setEmail('example@example.com');
        $user->setRole($role);
        $this->em->persist($user);
        $this->em->flush();

        $email = new Email();
        $email->setName('Test email 1');
        $email->setCreatedBy($user);
        $this->em->persist($email);
        $this->em->flush();

        $corePermissionsMock->method('hasEntityAccess')
            ->with(
                'email:emails:viewown',
                'email:emails:viewother',
                $user->getId()
            )
            ->willReturn(false);

        $result = $this->mapController->hasAccess($corePermissionsMock, $email);

        try {
            $this->mapController->viewAction($corePermissionsMock, $email->getId(), '2023-07-20', '2023-07-27');
        } catch (AccessDeniedHttpException|\Exception $e) {
            $this->assertTrue($e instanceof AccessDeniedHttpException);
        }

        $this->assertFalse($result);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testGetMapOptions(): void
    {
        $email = new Email();
        $email->setName('Test email');
        $this->em->persist($email);
        $this->em->flush();

        $result = $this->mapController->getMapOptions($email);
        $this->assertSame(EmailMapStatsController::MAP_OPTIONS, $result);
    }

    /**
     * @return array<string, array<int, array<string, string>>>
     */
    private function getStats(): array
    {
        return [
            'clicked_through_count' => [
                [
                    'clicked_through_count' => '4',
                    'country'               => '',
                ],
                [
                    'clicked_through_count' => '7',
                    'country'               => 'Italy',
                ],
            ],
            'read_count' => [
                [
                    'read_count'            => '4',
                    'country'               => '',
                ],
                [
                    'read_count'            => '12',
                    'country'               => 'Italy',
                ],
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function testGetData(): void
    {
        $email = new Email();
        $email->setName('Test email');

        $dateFrom = new \DateTimeImmutable('2023-07-20');
        $dateTo   = new \DateTimeImmutable('2023-07-25');

        $this->emailModelMock->method('getCountryStats')
            ->with($email, $dateFrom, $dateTo, false)
            ->willReturn($this->getStats());

        $results = $this->mapController->getData($email, $dateFrom, $dateTo);

        $this->assertCount(2, $results);
        $this->assertSame($this->getStats(), $results);
    }
}
