<?php
/**
 * @package     Mautic
 * @copyright   2016 Mautic Contributors. All rights reserved.
 * @author      Webmecanik
 * @link        http://webmecanik.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\FeedBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;
use Symfony\Component\HttpFoundation\Response;
use Mautic\FeedBundle\Helper\FeedHelper;
use Mautic\FeedBundle\Entity\Feed;
use Mautic\FeedBundle\Entity\Snapshot;

/**
 * Class FeedController
 */
class FeedController extends FormController
{

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function testAction($id)
    {
        $em = $this->factory->getEntityManager();

        /** @var FeedHelper $feedHelper  */
        $feedHelper = $this->get('mautic.helper.feed');

        /** @var Feed $feed */
        $feed = $em->find('MauticFeedBundle:Feed', $id);

        /** @var Snapshot $snapshot */
        $snapshot = $feed->getSnapshots()->last();

        // Write the contents of the XML file into a string
        $xmlString = $snapshot->getXmlString();


        $feedContent = $feedHelper->getFeedContentFromString($xmlString);

        return new Response(var_dump($feedContent), Response::HTTP_OK, array(
            'content-type' => 'text/plain'
        ));
    }
}
