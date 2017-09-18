<?php

namespace Shopware\Components;

if (!interface_exists('\Shopware\Components\CSRFWhitelistAware')) {
    /** @noinspection PhpUndefinedClassInspection */
    /**
     * \Shopware\Components\CSRFWhitelistAware was implemented in SW 5.2 –
     * this empty interface definition is used for earlier versions of SW
     *
     * @package Shopware\Components
     */
    interface CSRFWhitelistAware
    {
    }
}
