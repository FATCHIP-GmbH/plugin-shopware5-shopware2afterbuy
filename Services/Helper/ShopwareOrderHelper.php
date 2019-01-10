<?php

namespace FatchipAfterbuy\Services\Helper;

use FatchipAfterbuy\Components\Helper;
use Shopware\Components\Model\ModelEntity;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Category\Category;

class ShopwareOrderHelper extends AbstractHelper {

    public function getShop(int $id) {
        return $this->entityManager->getRepository('\Shopware\Models\Shop\Shop')->find($id);
    }
}