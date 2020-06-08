<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Catalogue\Service;

use OxidEsales\GraphQL\Base\Framework\PermissionProviderInterface;

class PermissionProvider implements PermissionProviderInterface
{
    public function getPermissions(): array
    {
        return [
            'admin' => [
                'VIEW_INACTIVE_ACTION',
                'VIEW_INACTIVE_ATTRIBUTE',
                'VIEW_INACTIVE_BANNER',
                'VIEW_INACTIVE_CATEGORY',
                'VIEW_INACTIVE_CONTENT',
                'VIEW_INACTIVE_LINK',
                'VIEW_INACTIVE_MANUFACTURER',
                'VIEW_INACTIVE_PRODUCT',
                'VIEW_INACTIVE_PROMOTION',
                'VIEW_INACTIVE_REVIEW',
                'VIEW_INACTIVE_VENDOR',
                'VIEW_WISHED_PRICES',
                'DELETE_WISHED_PRICE',
            ]
        ];
    }
}
