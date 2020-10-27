<?
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
require_once(dirname(__DIR__).'/class.php');
$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$obWishList = new UserWishList();
$obWishList->deleteItem($request->getPost('id'));