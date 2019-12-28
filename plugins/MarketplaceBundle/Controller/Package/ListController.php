<?php

declare(strict_types=1);

/*
 * @copyright   2019 Mautic. All rights reserved
 * @author      Mautic.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MarketplaceBundle\Controller\Package;

use Mautic\CoreBundle\Controller\CommonController;
use MauticPlugin\MarketplaceBundle\Service\PluginCollector;
use MauticPlugin\MarketplaceBundle\Service\RouteProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class ListController extends CommonController
{
    private $pluginCollector;
    private $requestStack;
    private $routeProvider;

    public function __construct(
        PluginCollector $pluginCollector,
        RequestStack $requestStack,
        RouteProvider $routeProvider
    ) {
        $this->pluginCollector = $pluginCollector;
        $this->requestStack    = $requestStack;
        $this->routeProvider   = $routeProvider;
    }

    public function listAction(int $page = 1): Response
    {
        // @todo implement permissions
        // try {
        //     $this->permissionProvider->canViewAtAll();
        // } catch (ForbiddenException $e) {
        //     return $this->accessDenied(false, $e->getMessage());
        // }

        $request    = $this->requestStack->getCurrentRequest();
        // $search     = InputHelper::clean($request->get('search', $this->sessionProvider->getFilter()));
        // $limit      = (int) $request->get('limit', $this->sessionProvider->getPageLimit());
        // $orderBy    = $this->sessionProvider->getOrderBy(CustomObject::TABLE_ALIAS.'.id');
        // $orderByDir = $this->sessionProvider->getOrderByDir('ASC');
        $route      = $this->routeProvider->buildListRoute($page);

        // if ($request->query->has('orderby')) {
        //     $orderBy    = InputHelper::clean($request->query->get('orderby'), true);
        //     $orderByDir = 'ASC' === $orderByDir ? 'DESC' : 'ASC';
        //     $this->sessionProvider->setOrderBy($orderBy);
        //     $this->sessionProvider->setOrderByDir($orderByDir);
        // }

        // $tableConfig = new TableConfig($limit, $page, $orderBy, $orderByDir);

        // $this->sessionProvider->setPage($page);
        // $this->sessionProvider->setPageLimit($limit);
        // $this->sessionProvider->setFilter($search);

        return $this->delegateView(
            [
                'returnUrl'      => $route,
                'viewParameters' => [
                    // 'searchValue'    => $search,
                    'items'          => $this->pluginCollector->collectPackages(),
                    // 'count'          => // @todo,
                    // 'page'           => $page,
                    // 'limit'          => $limit,
                    'tmpl'           => $request->isXmlHttpRequest() ? $request->get('tmpl', 'index') : 'index',
                ],
                'contentTemplate' => 'MarketplaceBundle:Package:list.html.php',
                'passthroughVars' => [
                    'mauticContent' => 'package',
                    'route'         => $route,
                ],
            ]
        );
    }
}
