<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\FCMNotificationBundle\Controller;

use Mautic\CoreBundle\Controller\CommonController;
use Mautic\PageBundle\Entity\Page;
use Mautic\PageBundle\Event\PageDisplayEvent;
use Mautic\PageBundle\PageEvents;




class PopupController extends CommonController
{
    public function indexAction()
    {
        /** @var \Mautic\CoreBundle\Templating\Helper\AssetsHelper $assetsHelper */
        $assetsHelper = $this->factory->getHelper('template.assets');
        $assetsHelper->addStylesheet('/plugins/FCMNotificationBundle/Assets/css/popup/popup.css');

        $this->integrationHelper = $this->get('mautic.helper.integration');       
        $integration = $this->integrationHelper->getIntegrationObject('FCM');

        $settings          = $integration->getIntegrationSettings();
        $features          = $settings->getSupportedFeatures();
        $featureSettings   = $settings->getFeatureSettings();        

        $response = $this->render(
            'FCMNotificationBundle:Popup:index.html.php',
            [
                'siteUrl' => $this->coreParametersHelper->getParameter('site_url'),
                'icon'  => $integration->getIcon(),
                'sampleNotificationTitle'  => $featureSettings['sample_notification_title'],
                'sampleNotificationText'  => $featureSettings['sample_notification_text']
            ]
        );

        $content = $response->getContent();

        $event = new PageDisplayEvent($content, new Page());
        $this->dispatcher->dispatch(PageEvents::PAGE_ON_DISPLAY, $event);
        $content = $event->getContent();

        return $response->setContent($content);
    }

    public function testAction(){
        $this->integrationHelper = $this->get('mautic.helper.integration');       
        $integration = $this->integrationHelper->getIntegrationObject('FCM');
        $keys        = $integration->getDecryptedApiKeys();


        //-- Init the service account --//        
        $serviceAccount = new ServiceAccount($keys['service_account_json']);
        $cacheHandler = new Google\Auth\Cache\MemoryCacheItemPool\MemoryCacheItemPool();
        $serviceAccount->setCacheHandler($cacheHandler);

        $message = new Message();

        $message->notification
        ->setTitle('My notification title')
        ->setBody('My notification body....');

        $message->data->fill([
            'a'=>1,
            'b'=>'2',
        ]);
        $message->data->set('x','value');
        $message->data->y='Same as above';

        $message->setTarget(new Token('dj_BwvWGX2Y:APA91bF2QavspU0jW6-0FiLwloqIQXm6gnnsTo30U9tgSEsTw1Qdu9P0GW8qCaIAT7CyQ_3byyM7NBNLQjl038T_p94Q2iSR4QTko-W4sGwtcfnEzXu08UyvgeDZpamGuvlbM4QYhGFm'));

        $client = new Client(['debug'=>true]);
        //If true the validate_only is set to true the message will not be submitted but just checked with FCM
        $validate_only = true;
        //Create a request
        $rq = new Request($serviceAccount,$validate_only,$client);
        try{
            //Use the request to submit the message
            $message->send($rq);
            //You can force the validate_only flag via the validate method, the request will be left intact
            //$message->validate($rq);
        }
        /** Catch all the exceptions @see https://firebase.google.com/docs/reference/fcm/rest/v1/ErrorCode **/
        //Like this
        catch(FcmErrorException $e){
            switch($e->getErrorCode()){
                default:
                case 'UNSPECIFIED_ERROR':
                case 'INVALID_ARGUMENT':
                case 'UNREGISTERED':
                case 'SENDER_ID_MISMATCH':
                case 'QUOTA_EXCEEDED':
                case 'APNS_AUTH_ERROR':
                case 'UNAVAILABLE':
                case 'INTERNAL':
            }
            echo 'FCM error ['.$e->getErrorCode().']: ',$e->getMessage();
        }
        catch(RequestException $e){
            //HTTP response error
            $response = $e->getResponse();
            echo 'Got an http response error:',$response->getStatusCode(),':',$response->getReasonPhrase();
            var_dump($response);
        }
        catch(GuzzleException $e){
            //GuzzleHttp generic error
            echo 'Got an http error:',$e->getMessage();
        }

        $response = $this->render(
            'FCMNotificationBundle:Popup:index.html.php'
        );
        return $response->setContent($content);
    }
}
