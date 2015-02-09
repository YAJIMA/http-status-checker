<?php
/**
 * HTTP STATUS CHECKER
 *
 * RESULT.PHP
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
$template = 'results.html';


/*====================
  BEFORE ACTIONS
  ====================*/
if(isset($_POST['mode'])){
    if($_POST['mode'] == 'delete'){
        $list_id = $_POST['list_id'];
        if(is_array($list_id)){
            $lists = array();
            foreach($list_id as $key => $val){
                if(preg_match('/\A[0-9]+\Z/i', $val)){
                    $lists[] = $val;
                }
            }
            unset($key,$val);
            $filter = "IN (".implode(",", $lists).")";
        }else{
            $filter = "= ".$list_id;
        }
        $sql = "DELETE FROM `results` WHERE `list_id` ".$filter;
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        $tableData = array();
        $tableData[] = array('fieldName'=>'deleteflg','value'=>1,'type'=>'integer');
        $db->perform('lists',$tableData,'UPDATE','`id` '.$filter,false);
        
        header("Location: results.php");exit();
    }
    if($_POST['mode'] == 'historydelete'){
        $sql = "DELETE FROM `results` WHERE `id` > 0";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        header("Location: results.php");exit();
    }
}

/*====================
  MAIN ACTIONS
  ====================*/
$sql = "SELECT * FROM `lists` WHERE `deleteflg` = 0 ORDER BY `id` ASC";
$stmt = $db->prepare($sql);
$stmt->execute();
$lists = $stmt->fetchall(PDO::FETCH_ASSOC);
$assets['lists'] = $lists;

$sql = "SELECT L.`id`, L.`target`, L.`deleteflg`, R.`httpstatus`, R.`message`, R.`checkdate` FROM `lists` L LEFT JOIN `results` R ON L.`id` = R.`list_id` WHERE L.`deleteflg` = 0 ORDER BY L.`id` ASC, R.`checkdate` ASC";
$stmt = $db->prepare($sql);
$stmt->execute();
$results = $stmt->fetchall(PDO::FETCH_ASSOC);
foreach($results as &$res){
    $target = $res['target'];
    if(strpos($target, 'http') === 0){
        $res['url'] = $target;
    }elseif(preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/i', $target)){
        $res['url'] = 'http://'.$target;
    }
}
unset($res);
$assets['results'] = $results;


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