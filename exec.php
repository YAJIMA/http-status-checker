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

/*====================
  BEFORE ACTIONS
  ====================*/


/*====================
  MAIN ACTIONS
  ====================*/
// 対象のURLをクエリー
$sql = "SELECT L.`id`, L.`target`, L.`deleteflg` FROM `lists` L WHERE L.`deleteflg` = 0 ORDER BY L.`modified` ASC LIMIT 0,500";
$stmt = $db->prepare($sql);
$stmt->execute();
$results = $stmt->fetchall(PDO::FETCH_ASSOC);

if(count($results) > 0){
    foreach($results as $res){
        $target = $res['target'];
        if(strpos($target, 'http') === 0){
            $url = $target;
        }elseif(preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/i', $target)){
            $url = 'http://'.$target;
        }
        echo 'url : '.$url.PHP_EOL;
        $curl = curl_init();
        $curl_useragent = (isset($ini['CURL']['useragent'])) ? $ini['CURL']['useragent'] : "";
        $curl_timeout = (isset($ini['CURL']['timeout'])) ? $ini['CURL']['timeout'] : 0;
        $options = array(
            CURLOPT_URL => $url
            , CURLOPT_AUTOREFERER => false
            , CURLOPT_COOKIESESSION => false
            , CURLOPT_CERTINFO => false
            , CURLOPT_FOLLOWLOCATION => false
            , CURLOPT_HEADER => false
            , CURLOPT_NOBODY => true
            , CURLOPT_RETURNTRANSFER => true
            , CURLOPT_FRESH_CONNECT => true
            , CURLOPT_USERAGENT => $curl_useragent
            , CURLOPT_TIMEOUT => $curl_timeout
            , CURLOPT_CONNECTTIMEOUT => $curl_timeout
        );
        curl_setopt_array($curl, $options);
        $curlResult = curl_exec($curl);
        if($curlResult === false){
            $errorMsg = curl_error($curl);
            $httpcode = 0;
        }else{
            // HTTPステータスを取得
            $errorMsg = NULL;
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        }
        echo 'httpcode : '.$httpcode.PHP_EOL;
        echo 'errormsg : '.$errorMsg.PHP_EOL;
        curl_close($curl);
        
        // 結果をDB登録
        $tableData = array();
        $tableData[] = array('fieldName'=>'list_id', 'value'=>$res['id'], 'type'=>'integer', 'index'=>1);
        $tableData[] = array('fieldName'=>'httpstatus', 'value'=>$httpcode, 'type'=>'integer', 'index'=>0);
        $tableData[] = array('fieldName'=>'checkdate', 'value'=>$checkDateStr, 'type'=>'string', 'index'=>1);
        if(!empty($errorMsg)) $tableData[] = array('fieldName'=>'message', 'value'=>$errorMsg, 'type'=>'string', 'index'=>0);
        $db->perform('`results`', $tableData, 'UPSERT', '', false);
    }
    unset($res);
}else{
    die('no items.');
}


// 結果をメール送信
echo 'MAIL START'.PHP_EOL;
$resultSql = "SELECT L.`id`, L.`target`, L.`deleteflg`, R.`httpstatus`, R.`checkdate`, R.`message`
    FROM `lists` L 
    LEFT JOIN `results` R ON L.`id` = R.`list_id` 
    WHERE L.`deleteflg` = 0 
    AND R.`checkdate` = :checkdate 
    ORDER BY L.`modified` ASC";
$resultStmt = $db->prepare($resultSql);
$resultStmt->bindParam(':checkdate', $checkDateStr, PDO::PARAM_STR, 20);
$resultStmt->execute();
$resultResults = $resultStmt->fetchall(PDO::FETCH_ASSOC);

$subject = "HTTP STATUS CHECKER RESULT. $checkDateStr";
echo 'subject : '.$subject.PHP_EOL;

$body = $ok = $ng = "";
foreach($resultResults as $rr){
    switch($rr['httpstatus']){
        case '200':
            $ok .= "[OK]    ".$rr['httpstatus']."   ".$rr['target'].PHP_EOL;
            break;
        default:
            $ng .= "[NG]    ".$rr['httpstatus']."   ".$rr['message']."   ".$rr['target'].PHP_EOL;
            break;
    }
}
unset($rr);
$body .= "Check date : ".$checkDateStr.PHP_EOL.$ng.PHP_EOL.$ok;
echo 'body : '.$body.PHP_EOL;

$from_email = $ini['mail']['from'];
if(!empty($ini['mail']['to'])){
    $to = strval($ini['mail']['to']);
}else{
    die('no entry to: mailaddress. check config.ini.');
}
if(!empty($ini['mail']['cc'])){
    $cc = $ini['mail']['cc'];
}else{
    $cc = NULL;
}
if(!empty($ini['mail']['bcc'])){
    $bcc = $ini['mail']['bcc'];
}else{
    $bcc = NULL;
}
$mailsend = HBsendMail($to, $subject, $body, $from_email, 'HTTP STATUS CHCKER', "UTF-8", $cc, $bcc);
if($mailsend){
    echo 'MAIL SENDING.'.PHP_EOL;
}else{
    echo 'MAIL SEND ERROR!'.PHP_EOL;
}

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