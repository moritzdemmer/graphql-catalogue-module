<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Catalogue\Tests\Integration\Controller;

use OxidEsales\Eshop\Core\Element2ShopRelations;
use OxidEsales\GraphQL\Base\Tests\Integration\MultishopTestCase;

/**
 * Class VendorEnterpriseTest
 *
 * @package OxidEsales\GraphQL\Catalogue\Tests\Integration\Controller
 */
class VendorEnterpriseTest extends MultishopTestCase
{
    private const VENDOR_ID = 'fe07958b49de225bd1dbc7594fb9a6b0';

    /**
     * Check if active vendor from shop 1 is not accessible for
     * shop 2 if its not yet related to shop 2
     */
    public function testGetSingleNotInShopActiveVendorWillFail()
    {
        $this->setGETRequestParameter('shp', '2');

        $result = $this->query('query {
            vendor (id: "' . self::VENDOR_ID . '") {
                id
            }
        }');

        $this->assertEquals(
            404,
            $result['status']
        );
    }

    /**
     * Check if no vendors available while they are not related to the shop 2
     */
    public function testGetEmptyVendorListOfNotMainShop()
    {
        $this->setGETRequestParameter('shp', '2');

        $result = $this->query('query{
            vendors {
                id
            }
        }');
        $this->assertResponseStatus(
            200,
            $result
        );
        // fixtures have 2 active vendors
        $this->assertEquals(
            0,
            count($result['body']['data']['vendors'])
        );
    }

    /**
     * Check if active vendor from shop 1 is accessible for
     * shop 2 if its related to shop 2
     */
    public function testGetSingleInShopActiveVendorWillWork()
    {
        $this->setGETRequestParameter('shp', '2');
        $this->setGETRequestParameter('lang', '0');
        $this->addVendorToShops([2]);

        $result = $this->query('query {
            vendor (id: "' . self::VENDOR_ID . '") {
                id,
                title
            }
        }');

        $this->assertEquals(
            200,
            $result['status']
        );

        $this->assertEquals(
            [
                'id' => self::VENDOR_ID,
                'title' => 'https://fashioncity.com/de'
            ],
            $result['body']['data']['vendor']
        );
    }

    /**
     * Check if only one, related to the shop 2 vendor is available in list
     */
    public function testGetOneVendorInListOfNotMainShop()
    {
        $this->setGETRequestParameter('shp', '2');
        $this->addVendorToShops([2]);

        $result = $this->query('query{
            vendors {
                id
            }
        }');
        $this->assertResponseStatus(
            200,
            $result
        );
        // fixtures have 2 active vendors
        $this->assertEquals(
            1,
            count($result['body']['data']['vendors'])
        );
    }

    /**
     * @return array
     */
    public function providerGetVendorMultilanguage()
    {
        return [
            'shop_1_de' => [
                'shopId' => '1',
                'languageId' => '0',
                'title' => 'https://fashioncity.com/de'
            ],
            'shop_1_en' => [
                'shopId' => '1',
                'languageId' => '1',
                'title' => 'https://fashioncity.com/en'
            ],
            'shop_2_de' => [
                'shopId' => '2',
                'languageId' => '0',
                'title' => 'https://fashioncity.com/de'
            ],
            'shop_2_en' => [
                'shopId' => '2',
                'languageId' => '1',
                'title' => 'https://fashioncity.com/en'
            ],
        ];
    }

    /**
     * Check multishop multilanguage data is accessible
     *
     * @dataProvider providerGetVendorMultilanguage
     */
    public function testGetSingleTranslatedSecondShopVendor($shopId, $languageId, $title)
    {
        $this->setGETRequestParameter('shp', $shopId);
        $this->setGETRequestParameter('lang', $languageId);
        $this->addVendorToShops([2]);

        $result = $this->query('query {
            vendor (id: "' . self::VENDOR_ID . '") {
                id
                title
            }
        }');

        $this->assertEquals(
            200,
            $result['status']
        );

        $this->assertEquals(
            [
                'id' => self::VENDOR_ID,
                'title' => $title
            ],
            $result['body']['data']['vendor']
        );
    }

    /**
     * Check multishop multilanguage data is accessible
     *
     * @dataProvider providerGetVendorMultilanguage
     */
    public function testGetListTranslatedSecondShopVendors($shopId, $languageId, $title)
    {
        $this->setGETRequestParameter('shp', $shopId);
        $this->setGETRequestParameter('lang', $languageId);
        $this->addVendorToShops([2]);

        $result = $this->query('query {
            vendors(filter: {
                title: {
                    equals: "' . $title . '"
                }
            }) {
                id,
                title
            }
        }');

        $this->assertEquals(
            200,
            $result['status']
        );

        $this->assertEquals(
            [
                'id' => self::VENDOR_ID,
                'title' => $title
            ],
            $result['body']['data']['vendors'][0]
        );
    }



    private function addVendorToShops($shops)
    {
        $oElement2ShopRelations = oxNew(Element2ShopRelations::class, 'oxvendor');
        $oElement2ShopRelations->setShopIds($shops);
        $oElement2ShopRelations->addToShop(self::VENDOR_ID);
    }
}