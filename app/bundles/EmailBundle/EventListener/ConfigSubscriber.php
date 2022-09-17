<?php

namespace Mautic\EmailBundle\EventListener;

use Mautic\ConfigBundle\ConfigEvents;
use Mautic\ConfigBundle\Event\ConfigBuilderEvent;
use Mautic\ConfigBundle\Event\ConfigEvent;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\EmailBundle\Form\Type\ConfigType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Transport\Dsn;

class ConfigSubscriber implements EventSubscriberInterface
{
    /**
     * @var CoreParametersHelper
     */
    private $coreParametersHelper;

    public function __construct(CoreParametersHelper $coreParametersHelper)
    {
        $this->coreParametersHelper = $coreParametersHelper;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ConfigEvents::CONFIG_ON_GENERATE => ['onConfigGenerate', 0],
            ConfigEvents::CONFIG_PRE_SAVE    => ['onConfigBeforeSave', 0],
        ];
    }

    public function onConfigGenerate(ConfigBuilderEvent $event)
    {
        $event->addForm([
            'bundle'     => 'EmailBundle',
            'formType'   => ConfigType::class,
            'formAlias'  => 'emailconfig',
            'formTheme'  => 'MauticEmailBundle:FormTheme\Config',
            'parameters' => $this->getParameters($event),
        ]);
    }

    public function onConfigBeforeSave(ConfigEvent $event)
    {
        $event->unsetIfEmpty(
            [
                'mailer_password',
                'mailer_api_key',
            ]
        );

        $data = $event->getConfig('emailconfig');

        // Get the original data so that passwords aren't lost
        $monitoredEmail = $this->coreParametersHelper->get('monitored_email');
        if (isset($data['monitored_email'])) {
            foreach ($data['monitored_email'] as $key => $monitor) {
                if (empty($monitor['password']) && !empty($monitoredEmail[$key]['password'])) {
                    $data['monitored_email'][$key]['password'] = $monitoredEmail[$key]['password'];
                }

                if ('general' != $key) {
                    if (empty($monitor['host']) || empty($monitor['address']) || empty($monitor['folder'])) {
                        // Reset to defaults
                        $data['monitored_email'][$key]['override_settings'] = 0;
                        $data['monitored_email'][$key]['address']           = null;
                        $data['monitored_email'][$key]['host']              = null;
                        $data['monitored_email'][$key]['user']              = null;
                        $data['monitored_email'][$key]['password']          = null;
                        $data['monitored_email'][$key]['encryption']        = '/ssl';
                        $data['monitored_email'][$key]['port']              = '993';
                    }
                }
            }
        }

        $event->setConfig($data, 'emailconfig');
    }

    private function getParameters(ConfigBuilderEvent $event): array
    {
        $parameters = $event->getParametersFromConfig('MauticEmailBundle');

        //parse dsn parameters to user friendly
        if (!empty($parameters['mailer_dsn'])) {
            $dsn                            = Dsn::fromString($parameters['mailer_dsn']);
            $parameters['mailer_transport'] = $dsn->getScheme();
            $parameters['mailer_host']      = $dsn->getHost();
            $parameters['mailer_port']      = $dsn->getPort();
            $parameters['mailer_user']      = $dsn->getUser();
            $parameters['mailer_password']  = $dsn->getPassword();
        }

        if (!empty($parameters['mailer_messenger_dsn']) && 'async' === $parameters['mailer_spool_type']) {
            $dsn                                 = Dsn::fromString($parameters['mailer_messenger_dsn']);
            $parameters['mailer_messenger_type'] = $dsn->getScheme();
            $parameters['mailer_messenger_host'] = $dsn->getHost();
            $parameters['mailer_messenger_port'] = $dsn->getPort();
            $parameters['mailer_messenger_path'] = $dsn->getOption('path');
        }

        return $parameters;
    }
}
