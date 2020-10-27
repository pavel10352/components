<?php
use Bitrix\Main\SystemException;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock\Iblock;
use Bitrix\Main\UserTable;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class UserWishList extends CBitrixComponent{

    private function checkModules(){
        if(!Loader::IncludeModule('iblock') || !Loader::IncludeModule('catalog') || !Loader::IncludeModule('sale')){
            throw new SystemException(Loc::getMessage('WL_MODULES_NOT_LOADED'));
        }
    }

    private function getUser(){
        global $USER;
        return $USER;
    }

    private function getWishList(){
        $USER = $this->getUser();
        if(!$USER->IsAuthorized()){
            throw new SystemException(Loc::getMessage('WL_USER_NOT_AUTHORIZED'));
        }
        $userId = $USER->GetID();
        $arUser = UserTable::getByPrimary($userId, ['select' => ['UF_WISHLIST']])->fetch();
        $arWish = explode(',', $arUser['UF_WISHLIST']);
        return $arWish;
    }

    public function onPrepareComponentParams($arParams){
        $result = [
            'CACHE_TYPE' => $arParams['CACHE_TYPE'],
            'CACHE_TIME' => intval(isset($arParams['CACHE_TIME'])? $arParams['CACHE_TIME']: 3600),
            'IBLOCK_ID' => intval($arParams['IBLOCK_ID']),
            'DISPLAY_BASKET_BUTTON' => ($arParams['DISPLAY_BASKET_BUTTON'] == 'N'? 'N': 'Y')
        ];
        return $result;
    }

    private function getProductsOffers(&$items){
        $arOffers = CCatalogSKU::getOffersList(
            array_keys($items),
            $this->arParams['IBLOCK_ID'],
            [],
            ['ID', 'QUANTITY', 'PROPERTY_SIZE']
        );
        foreach($arOffers as $id => $offer){
            $items[$id]['OFFERS'] = array_values($offer);
        }
    }

    private function getOffersPrice(&$items){
        foreach($items as $i => $arItem){
            if($arItem['OFFERS'] && $arItem['OFFERS'][0]){
                $offerId = $arItem['OFFERS'][0]['ID'];
                $arDiscounts = CCatalogDiscount::GetDiscountByProduct($offerId, $this->getUser()->GetUserGroupArray(), 'N', 1);
                $basePrice = CPrice::GetBasePrice($offerId);
                $discountPrice = CCatalogProduct::CountPriceWithDiscount($basePrice['PRICE'], $basePrice['CURRENCY'], $arDiscounts);
                $items[$i]['PRICE'] = $basePrice['PRICE'];
                $items[$i]['DISCOUNT'] = $discountPrice;
            }
        }
    }

    private function updateWishList($arItemsId){
        $obUser = new CUser;
        $obUser->Update($this->getUser()->GetID(), array('UF_WISHLIST' => implode(',', $arItemsId)));
    }

    public function deleteItem($id){
        $arWish = $this->getWishList();
        foreach($arWish as $i => $val){
            if($val == $id){
                unset($arWish[$i]);
            }
        }
        $this->updateWishList($arWish);
    }

    private function getResult($arWish){
        $arResult = [
            'ITEMS' => []
        ];
        if($arWish){
            $class = \Bitrix\Iblock\Iblock::wakeUp($this->arParams['IBLOCK_ID'])->getEntityDataClass();
            $result = $class::getList([
                'select' => ['ID', 'PREVIEW_PICTURE', 'BRAND', 'CML2_ARTICLE', 'TYPE', 'TYPE_EN', 'COLOR', 'COLOR_EN'],
                'filter' => ['ID' => $arWish]

            ])->fetchCollection();

            foreach($result as $element){
                $arFields = [
                    'ID' => $element->getId(),
                    'PREVIEW_PICTURE' => $element->getPreviewPicture(),
                    'BRAND' => $element->getBrand()->getValue(),
                    'CML2_ARTICLE' => $element->getCml2Article()->getValue(),
                    'TYPE' => $element->getType()->getValue(),
                    'TYPE_EN' => $element->getTypeEn()->getValue(),
                    'COLOR' => $element->getColor()->getValue(),
                    'COLOR_EN' => $element->getColorEn()->getValue()
                ];
                if($arFields['PREVIEW_PICTURE']){
                    $arFields['PHOTO'] = CFile::ResizeImageGet($arFields['PREVIEW_PICTURE'], ['width' => 225, 'height' => 300], BX_RESIZE_IMAGE_EXACT);
                }
                $arResult['ITEMS'][$arFields['ID']] = $arFields;
            }

            if($arResult['ITEMS']){
                $this->getProductsOffers($arResult['ITEMS']);
                $this->getOffersPrice($arResult['ITEMS']);
            }
        }
        return $arResult;
    }


    public function executeComponent(){
        try{
            $this->checkModules();
            $arWish = $this->getWishList();
            $additionalCacheID = $this->getUser()->getId().'-'.implode(',', $arWish);
            if($this->startResultCache($this->arParams['CACHE_TIME'], $additionalCacheID)){
                $this->arResult = $this->getResult($arWish);
                $this->includeComponentTemplate();
            }
            if(array_diff($arWish, array_keys($this->arResult['ITEMS']))){
                $this->updateWishList(array_keys($this->arResult['ITEMS']));
            }
        }catch(SystemException $e){
            ShowError($e->getMessage());
        }
    }

}