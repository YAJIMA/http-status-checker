<?php
/**
 * HTTP STATUS CHECKER
 *
 * EXEC.PHP
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
require dirname(__FILE__).'/includes/start.php';
// 必要モジュールを読み込み

$checkDateStr = date("Y-n-j H:00:00");
echo $checkDateStr.PHP_EOL;

$lifetime = $ini['results']['lifetime'];

/*====================
  BEFORE ACTIONS
  ====================*/


/*====================
  MAIN ACTIONS
  ====================*/
$deleteDateTime = time() - $lifetime;
$deleteDateStr = date("Y-n-j H:00:00", $deleteDateTime);
$sql = "DELETE FROM `results` WHERE `checkdate` < '$deleteDateStr'";
//var_dump($sql);exit();
$stmt = $db->prepare($sql);
$stmt->execute();

/*====================
  AFTER ACTIONS
  ====================*/
// エンドスクリプト
require dirname(__FILE__).'/includes/end.php';
echo PHP_EOL;


/*====================
  FUNCTIONS
  ====================*/


?>