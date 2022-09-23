<?php

namespace Mautic\NotificationBundle\Controller\Api;

use Mautic\ApiBundle\Controller\CommonApiController;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\LeadBundle\Tracker\ContactTracker;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;

/**
 * Class NotificationApiController.
 */
class NotificationApiController extends CommonApiController
{
    /**
     * @var ContactTracker
     */
    protected $contactTracker;

    /**
     * {@inheritdoc}
     */
    public function initialize(ControllerArgumentsEvent $event)
    {
        $this->contactTracker  = $this->container->get('mautic.tracker.contact');
        $this->model           = $this->getModel('notification');
        $this->entityClass     = 'Mautic\NotificationBundle\Entity\Notification';
        $this->entityNameOne   = 'notification';
        $this->entityNameMulti = 'notifications';

        parent::initialize($event);
    }

    /**
     * Receive Web Push subscription request.
     *
     * @return JsonResponse
     */
    public function subscribeAction()
    {
        $osid = $this->request->get('osid');
        if ($osid) {
            /** @var LeadModel $leadModel */
            $leadModel = $this->getModel('lead');

            if ($currentLead = $this->contactTracker->getContact()) {
                $currentLead->addPushIDEntry($osid);
                $leadModel->saveEntity($currentLead);
            }

            return new JsonResponse(['success' => true, 'osid' => $osid], 200, ['Access-Control-Allow-Origin' => '*']);
        }

        return new JsonResponse(['success' => 'false'], 200, ['Access-Control-Allow-Origin' => '*']);
    }
}
