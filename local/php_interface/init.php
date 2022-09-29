<?php

use Bitrix\Main\EventManager;
use Bitrix\Sale;
use Bitrix\Main\Localization\Loc;

Loc::LoadMessages(__FILE__);
$eventManager = EventManager::getInstance();
$eventManager->addEventHandler(
    'sale',
    'OnSaleComponentOrderCreated',
    array('CustomBehavior', 'OnSaleOrderBeforeSavedHandler')
);

$eventManager->addEventHandler(
    'main',
    'OnBeforeEventAdd',
    array('CustomBehavior', 'OnBeforeEventAddHandler')
);

class CustomBehavior
{
    public static function OnBeforeEventAddHandler(&$event, &$lid, &$arFields)
    {
        global $USER;
        if ($event === 'FEEDBACK_FORM') {
            //Пользователь авторизован
            if ($USER->IsAuthorized()) {
                $arFields['AUTHOR'] = Loc::getMessage(
                    'USER_AUTHORIZED',
                     array(
                         '#ID#' => $USER->GetID(),
                         '#LOGIN#' => $USER->GetLogin(),
                         '#FULLNAME#' => $USER->GetFullName(),
                         '#FIELDNAME#' => $arFields['AUTHOR']
                     )
                );
            //Пользователь не авторизован
            } else {
               $arFields['AUTHOR'] = Loc::getMessage(
                    'USER_NOT_AUTHORIZED',
                    array('#FIELDNAME#' => $arFields['AUTHOR'])
                );
            }

            //Добавить запись в журнал событий
            CEventLog::Add(array(
                'SEVERITY' => 'SECURITY',
                'AUDIT_TYPE_ID' => Loc::getMessage('USER_REPLACE'),
                'MODULE_ID' => 'main',
                'DESCRIPTION' => Loc::getMessage(
                    'USER_REPLACE_LOG',
                    array('#AUTHOR#' => $arFields['AUTHOR'])
                ),
            ));
        }
    }

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
