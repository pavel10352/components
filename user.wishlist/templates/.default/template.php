<?
use Bitrix\Main\Localization\Loc;
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$this->addExternalCss($this->GetFolder().'/style.css');
$this->addExternalJS($this->GetFolder().'/script.js');
?>

<div id="wishlist">
    <? if($arResult['ITEMS']){
        foreach($arResult['ITEMS'] as $i => $arItem){
            ?><div class="item">
                <a data-id="<?=$arItem['ID']?>" data-href="<?=$this->getComponent()->GetPath()?>/ajax/delete_item.php" href="#" class="delete-item"></a>
                <div class="item-inner">
                    <div class="photo">
                        <a href="/catalog/view/<?=$arItem['ID']?>">
                            <? if($arItem['PHOTO']){ ?>
                                <img src="<?=$arItem['PHOTO']['src']?>" alt="" />
                            <? }else{ ?>
                                <img src="/static/images/no_photo.jpg" alt="" />
                            <? } ?>
                        </a>
                        <div class="labels">
                            <? if($arItem['PRICE'] > 5000){ ?>
                                <div class="isfreedelivery" data-title="<?=Loc::GetMessage('WL_FREE_SHIPPING')?>"></div>
                            <? } ?>
                            <? if($arItem['PRICE'] != $arItem['DISCOUNT']){ ?>
                                <div class="issale" data-title="<?=Loc::GetMessage('WL_PRODUCT_DISCOUNT')?>"></div>
                            <? } ?>
                        </div>
                    </div>
                    <div class="props">
                        <a href="/catalog/view/<?=$arItem['ID']?>">
                            <em><?=$arItem['BRAND']?></em>
                        </a>
                        <div class="type"><?=LANGUAGE_ID == 'en'? $arItem['TYPE_EN']: $arItem['TYPE'];?></div>
                        <div class="add-props">
                            <?=Loc::getMessage('WL_ARTICLE')?>: <?=$arItem['CML2_ARTICLE']?><br />
                            <?=Loc::GetMessage('WL_COLOR')?>: <?=LANGUAGE_ID == 'en'? $arItem['COLOR_EN']: $arItem['COLOR'];?><br />
                        </div>
                        <? if($arItem['PRICE']){
                            if($arItem['PRICE'] == $arItem['DISCOUNT']){ ?>
                                <strong><?=SaleFormatCurrency($arItem['PRICE'], 'RUB');?></strong>
                            <? }else{ ?>
                                <div class="price-block">
                                    <strong><?=SaleFormatCurrency($arItem['DISCOUNT'], 'RUB');?></strong>
                                    <strong class="discount-price"><?=SaleFormatCurrency($arItem['PRICE'], 'RUB');?></strong>
                                </div>
                            <? } ?>
                            <? if($arItem['OFFERS'] && ($arParams['DISPLAY_BASKET_BUTTON'] == 'Y')){ ?>
                                <div class="size">
                                    <select class="select" name="size">
                                        <option value="-"><?=Loc::GetMessage('WL_SELECT_SIZE')?></option>
                                        <? foreach($arItem['OFFERS'] as $offer){ ?>
                                            <option value="<?=$offer['ID']?>" data-quantity="<?=$offer['QUANTITY']?>"><?=$offer['PROPERTY_SIZE_VALUE']?></option>
                                        <? } ?>
                                    </select>
                                </div>
                                <a class="add-to-cart" href="#"><?=Loc::GetMessage('WL_ADD_TO_CART')?></a>
                            <? } ?>
                        <? }else{ ?>
                            <strong class="item-sold-out"><?=Loc::GetMessage('WL_ITEM_SOLD_OUT')?></strong>
                        <?}?>
                    </div>
                </div>
            </div><?
        }?>
    <? }else{ ?>
        <p><?=Loc::GetMessage('WL_EMPTY_WISHLIST')?></p>
    <? } ?>
</div>
