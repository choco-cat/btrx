<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

global $APPLICATION;

if ($arResult['CANONICAL']) {
    $APPLICATION->SetPageProperty("canonical", $arResult['CANONICAL']);
}
