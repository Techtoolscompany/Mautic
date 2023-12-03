<?php

namespace Mautic\LeadBundle\Tracker\Service\DeviceTrackingService;

use Doctrine\ORM\EntityManagerInterface;
use Mautic\CoreBundle\Helper\CookieHelper;
use Mautic\CoreBundle\Helper\RandomHelper\RandomHelperInterface;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Mautic\LeadBundle\Entity\LeadDevice;
use Mautic\LeadBundle\Entity\LeadDeviceRepository;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;

final class DeviceTrackingService implements DeviceTrackingServiceInterface
{
    private \Mautic\CoreBundle\Helper\CookieHelper $cookieHelper;

    private \Doctrine\ORM\EntityManagerInterface $entityManager;

    private \Mautic\LeadBundle\Entity\LeadDeviceRepository $leadDeviceRepository;

    private \Mautic\CoreBundle\Helper\RandomHelper\RandomHelperInterface $randomHelper;

    private \Symfony\Component\HttpFoundation\RequestStack $requestStack;

    /**
     * @var LeadDevice
     */
    private $trackedDevice;

    private \Mautic\CoreBundle\Security\Permissions\CorePermissions $security;

    public function __construct(
        CookieHelper $cookieHelper,
        EntityManagerInterface $entityManager,
        LeadDeviceRepository $leadDeviceRepository,
        RandomHelperInterface $randomHelper,
        RequestStack $requestStack,
        CorePermissions $security
    ) {
        $this->cookieHelper         = $cookieHelper;
        $this->entityManager        = $entityManager;
        $this->randomHelper         = $randomHelper;
        $this->leadDeviceRepository = $leadDeviceRepository;
        $this->requestStack         = $requestStack;
        $this->security             = $security;
    }

    public function isTracked(): bool
    {
        return $this->getTrackedDevice() instanceof \Mautic\LeadBundle\Entity\LeadDevice;
    }

    public function getTrackedDevice(): ?LeadDevice
    {
        if (!$this->security->isAnonymous()) {
            // Do not track Mautic users
            return null;
        }

        if ($this->trackedDevice) {
            return $this->trackedDevice;
        }

        $trackingId = $this->getTrackedIdentifier();
        if (null === $trackingId) {
            return null;
        }

        return $this->leadDeviceRepository->getByTrackingId($trackingId);
    }

    /**
     * @param bool $replaceExistingTracking
     *
     * @return LeadDevice
     */
    public function trackCurrentDevice(LeadDevice $device, $replaceExistingTracking = false)
    {
        $trackedDevice = $this->getTrackedDevice();
        if ($trackedDevice instanceof \Mautic\LeadBundle\Entity\LeadDevice && false === $replaceExistingTracking) {
            return $trackedDevice;
        }

        // Check for an existing device for this contact to prevent blowing up the devices table
        $existingDevice = $this->leadDeviceRepository->findOneBy(
            [
                'lead'        => $device->getLead(),
                'device'      => $device->getDevice(),
                'deviceBrand' => $device->getDeviceBrand(),
                'deviceModel' => $device->getDeviceModel(),
            ]
        );

        if ($existingDevice instanceof \Mautic\LeadBundle\Entity\LeadDevice) {
            $device = $existingDevice;
        }

        if (null === $device->getTrackingId()) {
            // Ensure all devices have a tracking ID (new devices will not and pre 2.13.0 devices may not)
            $device->setTrackingId($this->getUniqueTrackingIdentifier());

            $this->entityManager->persist($device);
            $this->entityManager->flush();
        }

        $this->createTrackingCookies($device);

        // Store the device in case a service uses this within the same session
        $this->trackedDevice = $device;

        return $device;
    }

    public function clearTrackingCookies()
    {
        $this->cookieHelper->deleteCookie('mautic_device_id');
        $this->cookieHelper->deleteCookie('mtc_id');
    }

    private function getTrackedIdentifier(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof \Symfony\Component\HttpFoundation\Request) {
            return null;
        }

        if ($this->trackedDevice) {
            // Use the device tracked in case the cookies were just created
            return $this->trackedDevice->getTrackingId();
        }

        $deviceTrackingId = $this->cookieHelper->getCookie('mautic_device_id', null);
        if (null === $deviceTrackingId) {
            $deviceTrackingId = $request->get('mautic_device_id', null);
        }

        return $deviceTrackingId;
    }

    private function getUniqueTrackingIdentifier(): string
    {
        do {
            $generatedIdentifier = $this->randomHelper->generate(23);
            $device              = $this->leadDeviceRepository->getByTrackingId($generatedIdentifier);
        } while ($device instanceof \Mautic\LeadBundle\Entity\LeadDevice);

        return $generatedIdentifier;
    }

    private function createTrackingCookies(LeadDevice $device)
    {
        // Device cookie
        $this->cookieHelper->setCookie('mautic_device_id', $device->getTrackingId(), 31536000, sameSite: Cookie::SAMESITE_NONE);

        // Mainly for landing pages so that JS has the same access as 3rd party tracking code
        $this->cookieHelper->setCookie('mtc_id', $device->getLead()->getId(), null, sameSite: Cookie::SAMESITE_NONE);
    }
}
