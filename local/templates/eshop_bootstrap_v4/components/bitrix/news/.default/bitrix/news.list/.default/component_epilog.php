<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $APPLICATION;

if ($arParams['SPECIALDATE'] == 'Y')
{
    $APPLICATION->SetPageProperty("specialdate", $arResult['SPECIALDATE']);
}