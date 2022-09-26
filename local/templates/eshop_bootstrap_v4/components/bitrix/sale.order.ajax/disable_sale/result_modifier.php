<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arParams
 * @var array $arResult
 * @var SaleOrderAjax $component
 */

$component = $this->__component;
$component::scaleImages($arResult['JS_DATA'], $arParams['SERVICES_IMAGES_SCALING']);
foreach ($arResult["JS_DATA"]["GRID"]["ROWS"] as $row) {
	$row["data"]["PRICE"] = 1000;
}

/*
$arResult['JS_DATA']['GRID']['ROWS'][12]['data']['PRICE'] = 1000;
$arResult['JS_DATA']['GRID']['ROWS'][12]['data']['SUM_DISCOUNT_DIFF'] = 0;
$arResult['JS_DATA']['GRID']['ROWS'][12]['data']['BASE_PRICE'] = 1100;
$arResult['JS_DATA']['GRID']['ROWS'][12]['data']['SUM_NUM'] = 1000;
$arResult['JS_DATA']['GRID']['ROWS'][12]['data']['SUM_BASE'] = 1100;
$arResult['JS_DATA']['GRID']['ROWS'][12]['data']['PRICE_FORMATED'] = 1000;
$arResult['JS_DATA']['GRID']['ROWS'][12]['data']['DISCOUNT_PRICE_PERCENT'] = 1100;
$arResult['JS_DATA']['GRID']['ROWS'][12]['data']['DISCOUNT_PRICE'] = 0; */
//$arResult["JS_DATA"]["GRID"]["ROWS"][12]["data"]["PRICE"] = 1000;
//$arResult["JS_DATA"]["GRID"]["ROWS"][12]["data"]["SUM_NUM"] = 1000;
//	echo '<pre>';
	//var_dump($arResult['JS_DATA']["GRID"]["ROWS"][12]["data"]);
//var_dump($arParams);
//echo '</pre>';

//print_r($arResult["JS_DATA"]["GRID"]["ROWS"][12]["data"]);