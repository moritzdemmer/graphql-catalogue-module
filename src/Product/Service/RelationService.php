<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Catalogue\Product\Service;

use OxidEsales\Eshop\Application\Model\Attribute;
use OxidEsales\GraphQL\Base\Exception\InvalidLogin;
use OxidEsales\GraphQL\Catalogue\Category\DataType\Category;
use OxidEsales\GraphQL\Catalogue\Manufacturer\DataType\Manufacturer;
use OxidEsales\GraphQL\Catalogue\Product\DataType\Price;
use OxidEsales\GraphQL\Catalogue\Product\DataType\Product;
use OxidEsales\GraphQL\Catalogue\Product\DataType\ProductAttribute;
use OxidEsales\GraphQL\Catalogue\Product\DataType\ProductDeliveryTime;
use OxidEsales\GraphQL\Catalogue\Product\DataType\ProductDimensions;
use OxidEsales\GraphQL\Catalogue\Product\DataType\ProductImageGallery;
use OxidEsales\GraphQL\Catalogue\Product\DataType\ProductRating;
use OxidEsales\GraphQL\Catalogue\Product\DataType\ProductScalePrice;
use OxidEsales\GraphQL\Catalogue\Product\DataType\ProductStock;
use OxidEsales\GraphQL\Catalogue\Product\DataType\ProductUnit;
use OxidEsales\GraphQL\Catalogue\Product\DataType\SelectionList;
use OxidEsales\GraphQL\Catalogue\Product\Exception\ProductNotFound;
use OxidEsales\GraphQL\Catalogue\Product\Service\Product as ProductService;
use OxidEsales\GraphQL\Catalogue\Review\DataType\Review;
use OxidEsales\GraphQL\Catalogue\Shared\DataType\Seo;
use OxidEsales\GraphQL\Catalogue\Vendor\DataType\Vendor;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;

use function array_map;
use function count;
use function is_iterable;
use function strlen;

/**
 * @ExtendType(class=Product::class)
 */
final class RelationService
{
    /** @var ProductService */
    private $productService;

    public function __construct(
        ProductService $productService
    ) {
        $this->productService = $productService;
    }

    /**
     * @Field()
     */
    public function getDimensions(Product $product): ProductDimensions
    {
        return new ProductDimensions(
            $product->getEshopModel()
        );
    }

    /**
     * @Field()
     */
    public function getPrice(Product $product): Price
    {
        return new Price(
            $product->getEshopModel()->getPrice()
        );
    }

    /**
     * @Field()
     */
    public function getListPrice(Product $product): Price
    {
        return new Price(
            $product->getEshopModel()->getTPrice()
        );
    }

    /**
     * @Field()
     */
    public function getStock(Product $product): ProductStock
    {
        return new ProductStock(
            $product->getEshopModel()
        );
    }

    /**
     * @Field()
     */
    public function getImageGallery(Product $product): ProductImageGallery
    {
        return new ProductImageGallery(
            $product->getEshopModel()
        );
    }

    /**
     * @Field()
     */
    public function getRating(Product $product): ProductRating
    {
        return new ProductRating(
            $product->getEshopModel()
        );
    }

    /**
     * @Field()
     */
    public function getDeliveryTime(Product $product): ProductDeliveryTime
    {
        return new ProductDeliveryTime(
            $product->getEshopModel()
        );
    }

    /**
     * @Field()
     *
     * @return ProductScalePrice[]
     */
    public function getScalePrices(Product $product): array
    {
        $amountPrices = $product->getEshopModel()->loadAmountPriceInfo();

        return array_map(
            function ($amountPrice) {
                return new ProductScalePrice($amountPrice);
            },
            $amountPrices
        );
    }

    /**
     * @Field()
     */
    public function getBundleProduct(Product $product): ?Product
    {
        $bundleProductId = (string) $product->getEshopModel()->getFieldData('oxbundleid');

        if (!strlen($bundleProductId)) {
            return null;
        }

        try {
            return $this->productService->product(
                $bundleProductId
            );
        } catch (ProductNotFound | InvalidLogin $e) {
        }

        return null;
    }

    /**
     * @Field()
     */
    public function getManufacturer(Product $product): ?Manufacturer
    {
        $manufacturer = $product->getEshopModel()->getManufacturer();

        if ($manufacturer === null) {
            return null;
        }

        return new Manufacturer(
            $manufacturer
        );
    }

    /**
     * @Field()
     */
    public function getVendor(Product $product): ?Vendor
    {
        /** @var null|\OxidEsales\Eshop\Application\Model\Vendor */
        $vendor = $product->getEshopModel()->getVendor();

        if ($vendor === null) {
            return null;
        }

        return new Vendor(
            $vendor
        );
    }

    /**
     * @Field()
     */
    public function getCategory(Product $product): ?Category
    {
        /** @var null|\OxidEsales\Eshop\Application\Model\Category */
        $category = $product->getEshopModel()->getCategory();

        if (
            $category === null ||
            !$category->getId()
        ) {
            return null;
        }

        return new Category(
            $category
        );
    }

    /**
     * @Field()
     */
    public function getUnit(Product $product): ?ProductUnit
    {
        if (!$product->getEshopModel()->getUnitPrice()) {
            return null;
        }

        return new ProductUnit(
            $product->getEshopModel()
        );
    }

    /**
     * @Field()
     */
    public function getSeo(Product $product): Seo
    {
        return new Seo($product->getEshopModel());
    }

    /**
     * @Field()
     *
     * @return Product[]
     */
    public function getCrossSelling(Product $product): array
    {
        $products = $product->getEshopModel()->getCrossSelling();

        if (!is_iterable($products) || count($products) === 0) {
            return [];
        }
        $crossSellings = [];

        foreach ($products as $product) {
            $crossSellings[] = new Product($product);
        }

        return $crossSellings;
    }

    /**
     * @Field()
     *
     * @return ProductAttribute[]
     */
    public function getAttributes(Product $product): array
    {
        /** @var \OxidEsales\Eshop\Application\Model\AttributeList $productAttributes */
        $productAttributes = $product->getEshopModel()->getAttributes();

        if (!is_iterable($productAttributes) || count($productAttributes) === 0) {
            return [];
        }
        $attributes = [];

        /** @var Attribute $attribute */
        foreach ($productAttributes as $key => $attribute) {
            $attributes[$key] = new ProductAttribute($attribute);
        }

        return $attributes;
    }

    /**
     * @Field()
     *
     * @return Product[]
     */
    public function getAccessories(Product $product): array
    {
        $products = $product->getEshopModel()->getAccessoires();

        if (!is_iterable($products) || count($products) === 0) {
            return [];
        }
        $accessories = [];

        foreach ($products as $product) {
            $accessories[] = new Product($product);
        }

        return $accessories;
    }

    /**
     * @Field()
     *
     * @return SelectionList[]
     */
    public function getSelectionLists(Product $product): array
    {
        $selections = $product->getEshopModel()->getSelections();

        if (!is_iterable($selections) || count($selections) === 0) {
            return [];
        }

        $selectionLists = [];

        foreach ($selections as $selection) {
            $selectionLists[] = new SelectionList($selection);
        }

        return $selectionLists;
    }

    /**
     * @Field()
     *
     * @return Review[]
     */
    public function getReviews(Product $product): array
    {
        $result = [];

        $reviews = $product->getEshopModel()->getReviews();

        if ($reviews !== null) {
            /** @var \OxidEsales\Eshop\Application\Model\Review $oneReview */
            foreach ($reviews as $oneReview) {
                $result[] = new Review($oneReview);
            }
        }

        return $result;
    }

    /**
     * @Field()
     *
     * @return Product[]
     */
    public function getVariants(Product $product): array
    {
        $result = [];

        $variants = $product->getEshopModel()->getVariants();

        if (is_iterable($variants)) {
            foreach ($variants as $variant) {
                $result[] = new Product($variant);
            }
        }

        return $result;
    }
}