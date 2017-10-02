<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Controller\Api;

use FOS\RestBundle\Util\Codes;
use Mautic\ApiBundle\Controller\CommonApiController;
use Mautic\CoreBundle\Helper\EmojiHelper;
use Mautic\CoreBundle\Helper\InputHelper;
use Mautic\EmailBundle\Entity\Email;
use Mautic\EmailBundle\Entity\Stat;
use Mautic\EmailBundle\Helper\MailHelper;
use Mautic\LeadBundle\Controller\LeadAccessTrait;
use Mautic\LeadBundle\Entity\Lead;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class EmailApiController.
 */
class EmailApiController extends CommonApiController
{
    use LeadAccessTrait;

    public function initialize(FilterControllerEvent $event)
    {
        $this->model            = $this->getModel('email');
        $this->entityClass      = 'Mautic\EmailBundle\Entity\Email';
        $this->entityNameOne    = 'email';
        $this->entityNameMulti  = 'emails';
        $this->serializerGroups = [
            'emailDetails',
            'categoryList',
            'publishDetails',
            'assetList',
            'formList',
            'leadListList',
        ];
        $this->dataInputMasks = [
            'customHtml'     => 'html',
            'dynamicContent' => [
                'content' => 'html',
                'filters' => [
                    'content' => 'html',
                ],
            ],
        ];

        parent::initialize($event);
    }

    /**
     * Obtains a list of emails.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getEntitiesAction()
    {
        //get parent level only
        $this->listFilters[] = [
            'column' => 'e.variantParent',
            'expr'   => 'isNull',
        ];

        return parent::getEntitiesAction();
    }

    /**
     * Sends the email to it's assigned lists.
     *
     * @param int $id Email ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function sendAction($id)
    {
        $entity = $this->model->getEntity($id);
        if (null !== $entity) {
            if (!$this->checkEntityAccess($entity, 'view')) {
                return $this->accessDenied();
            }

            $lists = $this->request->request->get('lists', null);
            $limit = $this->request->request->get('limit', null);

            list($count, $failed) = $this->model->sendEmailToLists($entity, $lists, $limit);

            $view = $this->view(
                [
                    'success'          => 1,
                    'sentCount'        => $count,
                    'failedRecipients' => $failed,
                ],
                Codes::HTTP_OK
            );

            return $this->handleView($view);
        }

        return $this->notFound();
    }

    /**
     * Sends the email to a specific lead.
     *
     * @param int $id     Email ID
     * @param int $leadId Lead ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function sendLeadAction($id, $leadId)
    {
        /** @var Email $entity */
        $entity = $this->model->getEntity($id);
        if (null !== $entity) {
            if (!$this->checkEntityAccess($entity, 'view')) {
                return $this->accessDenied();
            }

            /** @var Lead $lead */
            $lead = $this->checkLeadAccess($leadId, 'edit');
            if ($lead instanceof Response) {
                return $lead;
            }

            $post      = $this->request->request->all();
            $tokens    = (!empty($post['tokens'])) ? $post['tokens'] : [];
            $ignoreDNC = isset($post['ignoreDNC']) ? $post['ignoreDNC'] : false;
            $response  = ['success' => false];

            $cleanTokens = [];

            foreach ($tokens as $token => $value) {
                $value = InputHelper::clean($value);
                if (!preg_match('/^{.*?}$/', $token)) {
                    $token = '{'.$token.'}';
                }

                $cleanTokens[$token] = $value;
            }

            $leadFields = array_merge(['id' => $leadId], $lead->getProfileFields());

            $result = $this->model->sendEmail(
                $entity,
                $leadFields,
                [
                    'source'        => ['api', 0],
                    'tokens'        => $cleanTokens,
                    'ignoreDNC'     => $ignoreDNC,
                    'return_errors' => true,
                ]
            );

            if (is_bool($result)) {
                $response['success'] = $result;
            } else {
                $response['failed'] = $result;
            }

            $view = $this->view($response, Codes::HTTP_OK);

            return $this->handleView($view);
        }

        return $this->notFound();
    }

    /**
     * Sends custom content to a specific lead.
     *
     * @param int $contactId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function sendCustomLeadAction($contactId)
    {
        /** @var Lead $lead */
        $lead = $this->checkLeadAccess($contactId, 'edit');
        if ($lead instanceof Response) {
            return $lead;
        }

        $post      = $this->request->request->all();
        $fromEmail = (!empty($post['fromEmail'])) ? $post['fromEmail'] : '';
        $fromName  = (!empty($post['fromName'])) ? $post['fromName'] : '';
        $subject   = (!empty($post['subject'])) ? $post['subject'] : '';
        $content   = (!empty($post['content'])) ? $post['content'] : '';

        $leadFields       = $lead->getProfileFields();
        $leadFields['id'] = $lead->getId();
        $leadEmail        = $leadFields['email'];
        $leadName         = $leadFields['firstname'].' '.$leadFields['lastname'];

        // Set onwer ID to be the current user ID so it will use his signature
        $leadFields['owner_id'] = $this->get('mautic.helper.user')->getUser()->getId();

        $response = ['success' => false];
        if ($lead && $lead->getEmail()) {
            /** @var MailHelper $mailer */
            $mailer = $this->get('mautic.helper.mailer')->getMailer();

            // To lead
            $mailer->addTo(
                $leadEmail,
                $leadName
            );

            $mailer->setFrom(
                $fromEmail,
                $fromName
            );

            // Set Content
            $mailer->setBody($content);
            $mailer->parsePlainText($content);

            // Set lead
            $mailer->setLead($leadFields);
            $mailer->setIdHash();

            // Ensure safe emoji for notification
            $subject = EmojiHelper::toHtml($subject);
            $mailer->setSubject($subject);

            if ($mailer->send(true, false, false)) {
                /** @var Stat $stat */
                $stat                     = $mailer->createEmailStat();
                $response['trackingHash'] = ($stat && $stat->getTrackingHash()) ? $stat->getTrackingHash() : 0;
                $response['success']      = true;
            }

            $view = $this->view($response, Codes::HTTP_OK);

            return $this->handleView($view);
        }

        return $this->notFound();
    }

    /**
     * Sends custom content to anybody.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function sendCustomAction()
    {
        $post      = $this->request->request->all();
        $toEmail   = (!empty($post['toEmail'])) ? $post['toEmail'] : '';
        $toName    = (!empty($post['toName'])) ? $post['toName'] : null;
        $fromEmail = (!empty($post['fromEmail'])) ? $post['fromEmail'] : '';
        $fromName  = (!empty($post['fromName'])) ? $post['fromName'] : '';
        $subject   = (!empty($post['subject'])) ? $post['subject'] : '';
        $content   = (!empty($post['content'])) ? $post['content'] : '';

        $response = ['success' => false];

        /** @var MailHelper $mailer */
        $mailer = $this->get('mautic.helper.mailer')->getMailer();

        // To email
        $mailer->addTo(
            $toEmail,
            $toName
        );

        $mailer->setFrom(
            $fromEmail,
            $fromName
        );

        // Set Content
        $mailer->setBody($content);
        $mailer->parsePlainText($content);

        // Ensure safe emoji for notification
        $subject = EmojiHelper::toHtml($subject);
        $mailer->setSubject($subject);

        if ($mailer->send(true, false, false)) {
            $response['success'] = true;
        }

        $view = $this->view($response, Codes::HTTP_OK);

        return $this->handleView($view);
    }
}
