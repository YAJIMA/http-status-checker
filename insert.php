<?php
/**
 * HTTP STATUS CHECKER
 *
 * INSERT.PHP
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
// スタートスクリプト
require './includes/start.php';
// 必要モジュールを読み込み

// SMARTY
require 'includes/smarty.class.php';
$smarty = new smartyEngine();
$assets = array();
$assets['ini'] = $ini;
$template = 'insert.html';

$uploaddir = ROOT_DIR.'uploads/';// アップロード先ディレクトリ

/*====================
  BEFORE ACTIONS
  ====================*/
if(isset($_POST['mode'])){
    // ＤＢ登録
    if($_POST['mode'] == 'finish'){
        $filename = trim($_POST['filename']);
        $insertTime = time();
        $handle = fopen($uploaddir.$filename, 'r');
        if($handle !== false){
            $c = 0;
            setlocale(LC_ALL, 'ja_JP.UTF-8');// http://blog.epitaph-t.com/?p=157
            //var_dump($uploaddir.$filename);
            while(($data = fgetcsv($handle, 0, ',', '"')) !== false){
                $c++;
                $tatgetStr = trim($data[0]);
                $commentPattern = '/\A#.+\z/i';
                if(preg_match($commentPattern, $tatgetStr)){
                    continue;
                }else{
                    $tableData = array();
                    $tableData[] = array('fieldName'=>'target', 'value'=>$tatgetStr, 'type'=>'string');
                    $tableData[] = array('fieldName'=>'deleteflg', 'value'=>0, 'type'=>'integer');
                    $tableData[] = array('fieldName'=>'byfilename', 'value'=>$filename, 'type'=>'string');
                    $tableData[] = array('fieldName'=>'inserttime', 'value'=>$insertTime, 'type'=>'integer');
                    $db->perform('`lists`', $tableData, 'UPSERT', '', false);
                }
            }
        }else{
            die('FILE OPEN ERR.');
        }
        fclose($handle);
        $template = 'insert-finish.html';
    }
    // 内容確認
    if($_POST['mode'] == 'confirm'){
        $filename = basename($_FILES['csvfile']['name']);// ファイル名
        $tempname = $_FILES['csvfile']['tmp_name'];// 一時ファイル名
        $contents = $line = array();
        if(move_uploaded_file($tempname, $uploaddir.$filename)){
            // http://blog.plastik.jp/archives/6
            $buf = mb_convert_encoding( file_get_contents($uploaddir.$filename), "UTF-8", "SJIS-win");
            $handle = tmpfile();
            fwrite($handle,$buf);
            rewind($handle);
            //var_dump($buf);
            if($handle !== false){
                $c = 0;
                setlocale(LC_ALL, 'ja_JP.UTF-8');// http://blog.epitaph-t.com/?p=157
                //var_dump($uploaddir.$filename);
                while(($data = fgetcsv($handle, 0, ',', '"')) !== false){
                    $c++;
                    $tatgetStr = trim($data[0]);
                    $commentPattern = '/\A#.+\z/i';
                    if(preg_match($commentPattern, $tatgetStr)){
                        continue;
                    }else{
                        $line[] = $tatgetStr;
                    }
                }
                fclose($handle);
            }else{
                echo 'TEMP FILE ERR.';
            }
            $_SESSION['form']['filename'] = $filename;
        }else{
            die('UPLOAD ERR '.print_r(array($tempname, $uploaddir.$filename), true));
        }
        $assets['line'] = $line;
        $assets['filename'] = $filename;
        $template = 'insert-confirm.html';
    }
}

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