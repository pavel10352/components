<?
CModule::IncludeModule("iblock");

$iblockList = [];

$res = \Bitrix\Iblock\IblockTable::getList([
    'select' => ['ID', 'NAME']
])->fetchCollection();

foreach($res as $element){
    $iblockList[$element->getId()] = $element->getName();
}

$arComponentParameters = array(
    "GROUPS" => array(
        "PARAMS" => array(
            "NAME" => GetMessage("WL_MAIN_PARAMS")
        ),
        "DISPLAY_OPTIONS" => array(
            "NAME" => GetMessage("WL_DISPLAY_OPTIONS")
        ),
    ),
    "PARAMETERS" => array(
        "IBLOCK_ID" => array(
            "PARENT" => "PARAMS",
            "NAME" => GetMessage("WL_IBLOCK_ID"),
            "TYPE" => "LIST",
            "VALUES" => $iblockList
        ),
        "DISPLAY_BASKET_BUTTON" => array(
            "PARENT" => "DISPLAY_OPTIONS",
            "NAME" => GetMessage("WL_DISPLAY_BASKET_BUTTON"),
            "TYPE" => "CHECKBOX"
        ),
        "CACHE_TIME" => array()
    )
);
?>