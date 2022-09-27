<?php

use Bitrix\Main\EventManager;
use Bitrix\Sale;

$eventManager = EventManager::getInstance();
$eventManager->addEventHandler(
    'sale',
    'OnSaleComponentOrderCreated',
    array('customizeBehavior', 'OnSaleOrderBeforeSavedHandler')
);
$eventManager->addEventHandler(
    'iblock',
    'OnBeforeIBlockElementUpdate',
    array('customizeBehavior', 'OnDeactivationCheck')
);

class customizeBehavior
{
    const PRODUCT_IBLOCK_ID = 2;
    const SHOW_COUNT = 2;

    //Запретить деактивировать товар, если просмотров больше SHOW_COUNT
    public static function OnDeactivationCheck(&$arFields)
    {
        global $APPLICATION;
        if ($arFields['ACTIVE'] !== 'Y' && $arFields['IBLOCK_ID'] === static::PRODUCT_IBLOCK_ID) {
            $res = CIBlockElement::GetList(array(), array("ID" => $arFields["ID"]), false, false, array("SHOW_COUNTER"));
            if ($arRes = $res->GetNext()) {
                if ($arRes['SHOW_COUNTER'] > static::SHOW_COUNT) {
                    $APPLICATION->throwException('Товар невозможно деактировать, у него ' .
                        $arRes['SHOW_COUNTER'] . ' просмотров');
                    return false;
                }
            }
        }
    }

    //Отменить скидки в заказе, если товары из разных разделов
    public static function OnSaleOrderBeforeSavedHandler(&$order)
    {
        //Получить корзину пользователя
        //$basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());
        $basket = $order->getBasket();
        $abortDiscount = false;

        //Проверка принадлежности товаров к одному разделу
        foreach ($basket as $basketItem) {
            $els = CIBlockElement::GetList(array(), array("ID" => $basketItem->GetProductID()), false, false, array("PROPERTY_CML2_LINK.IBLOCK_SECTION_ID"));    // получаем товары из корзины
            if ($el = $els->GetNext()) {
                if (isset($lastSectionId) && $lastSectionId !== $el['PROPERTY_CML2_LINK_IBLOCK_SECTION_ID']) {
                    echo 'Товары из разных разделов! Скидки отменяются';
                    $abortDiscount = true;
                    break;
                }
                $lastSectionId = $el['PROPERTY_CML2_LINK_IBLOCK_SECTION_ID'];
            }
            //$basketItem->setField('DISCOUNT_PRICE', 0);
            //$basketItem->setField('PRICE',  $basketItem->getField('BASE_PRICE'));
        }
        if ($abortDiscount) {
            $discounts = \Bitrix\Sale\Discount::buildFromOrder($order);
            $currentDiscountApplied = $discounts->getApplyResult(true)["DISCOUNT_LIST"];
            foreach (array_keys($currentDiscountApplied) as $key) {
                $discountsAbort[$key] = 'N';
            }
            //отменяем все скидки
            $discounts->setApplyResult(array('DISCOUNT_LIST' => $discountsAbort));
            $discountsCalculateResult = $discounts->calculate();
            if ($discountsCalculateResult->isSuccess()) {
                $result = $discountsCalculateResult->getData();
                $order->applyDiscount($result);
                //не обновляет корзину??
                $basket->applyDiscount($result);
                $basket->save();
            }
        }
    }
}
