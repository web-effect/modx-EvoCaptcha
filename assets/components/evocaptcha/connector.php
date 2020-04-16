<?php
require dirname(dirname(dirname(dirname(__FILE__))))."/config.core.php";
if(!defined('MODX_CORE_PATH')) require_once '../../../config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CORE_PATH.'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize( 'web' );
$modx->invokeEvent("OnLoadWebDocument");

if(empty($_REQUEST['prefix']))die();
if(empty($_SESSION[$_REQUEST['prefix'].'_evocaptcha']['config']))die();
if(empty($_SESSION[$_REQUEST['prefix'].'_evocaptcha']['word']))die();

$modx->getService('evocaptcha','evoCaptcha',MODX_CORE_PATH.'components/evocaptcha/model/evocaptcha/');
$modx->evocaptcha->initialize($_SESSION[$_REQUEST['prefix'].'_evocaptcha']['config']);

$modx->evocaptcha->imageOut();
$modx->evocaptcha->imageDestroy();