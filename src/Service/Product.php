<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Catalogue\Service;

use OxidEsales\GraphQL\Base\Exception\InvalidLogin;
use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Base\DataType\PaginationFilter;
use OxidEsales\GraphQL\Catalogue\DataType\Product as ProductDataType;
use OxidEsales\GraphQL\Catalogue\DataType\ProductFilterList;
use OxidEsales\GraphQL\Catalogue\Exception\ProductNotFound;
use OxidEsales\GraphQL\Base\Service\Authorization;
use OxidEsales\GraphQL\Catalogue\Service\Repository;

final class Product
{
    /** @var Repository */
    private $repository;

    /** @var Authorization */
    private $authorizationService;

    public function __construct(
        Repository $repository,
        Authorization $authorizationService
    ) {
        $this->repository = $repository;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @throws ProductNotFound
     * @throws InvalidLogin
     */
    public function product(string $id): ProductDataType
    {
        try {
            /** @var ProductDataType $product */
            $product = $this->repository->getById($id, ProductDataType::class);
        } catch (NotFound $e) {
            throw ProductNotFound::byId($id);
        }

        if ($product->isActive()) {
            return $product;
        }

        if ($this->authorizationService->isAllowed('VIEW_INACTIVE_PRODUCT')) {
            return $product;
        }

        throw new InvalidLogin("Unauthorized");
    }

    /**
     * @Query()
     *
     * @return ProductDataType[]
     */
    public function products(ProductFilterList $filter, ?PaginationFilter $pagination = null): array
    {
        // In case user has VIEW_INACTIVE_PRODUCT permissions
        // return all products including inactive ones
        if ($this->authorizationService->isAllowed('VIEW_INACTIVE_PRODUCT')) {
            $filter = $filter->withActiveFilter(null);
        }

        $products = $this->repository->getByFilter(
            $filter,
            ProductDataType::class,
            $pagination
        );

        return $products;
    }
}