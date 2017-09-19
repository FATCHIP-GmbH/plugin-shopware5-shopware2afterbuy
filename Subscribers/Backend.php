<?php

namespace Shopware\FatchipShopware2Afterbuy\Subscribers;

use Enlight\Event\SubscriberInterface;

/**
 * Class Backend
 *
 * @package Shopware\FatchipShopware2Afterbuy\Subscribers
 */
class Backend implements SubscriberInterface
{
    /**
     * Returns the subscribed events
     *
     * @return string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatch_Backend_Index' =>
                'onPostDispatchBackendIndex',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onPostDispatchBackendIndex(\Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Backend_Index $subject */
        $subject = $args->get('subject');
        $request = $subject->Request();
        $response = $subject->Response();
        $view = $subject->View();

        $view->addTemplateDir(__DIR__ . '/../Views');

        if (!$request->isDispatched() ||
            $response->isException() ||
            $request->getModuleName() != 'backend' ||
            !$view->hasTemplate()) {
            return;
        }

        $view->extendsTemplate('backend/index/shopware2afterbuy.tpl');
    }

}
