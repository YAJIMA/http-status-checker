<?php
/**
 * HTTP STATUS CHECKER
 *
 * INDEX.PHP
 *
 * @package     http-status-checker
 * @author      Y.Yajima <yajima@hatchbit.jp>
 * @copyright   2014, HatchBit & Co.
 * @license     http://www.hatchbit.jp/resource/license.html
 * @link        http://www.hatchbit.jp
 * @since       Version 0.1
 * @filesource
 */

/*====================
  DEFINE
  ====================*/
if(isset($_GET['php']) && $_GET['php'] == "info"){
	phpinfo(); exit();
}
// スタートスクリプト
require './includes/start.php';
// 必要モジュールを読み込み

// SMARTY
require 'includes/smarty.class.php';
$smarty = new smartyEngine();
$assets = array();
$assets['ini'] = $ini;
$template = 'index.html';


/*====================
  BEFORE ACTIONS
  ====================*/


/*====================
  MAIN ACTIONS
  ====================*/


/*====================
  AFTER ACTIONS
  ====================*/
// SMARTY出力
$smarty->assign($assets);
$smarty->display($template);
// エンドスクリプト
require './includes/end.php';


/*====================
  FUNCTIONS
  ====================*/


?>