<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

global $APPLICATION;
if ($arParams['CANONICAL']) {
    $arFilter = [
        'IBLOCK_ID' => $arParams['CANONICAL'],
        'PROPERTY_CANONICAL_NEW' => $arResult['ID'],
    ];
    $arSelect = ['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_CANONICAL_NEW'];
    $r = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);

    if ($res = $r->Fetch()) {
        $arResult['CANONICAL'] = $res['NAME'];
    }
    $cp = $this->__component;
    if (is_object($cp) && !empty($arResult['CANONICAL'])) {
        $cp->arResult['CANONICAL'] = $arResult['CANONICAL'];
        $cp->SetResultCacheKeys(array('CANONICAL'));
    }
}
