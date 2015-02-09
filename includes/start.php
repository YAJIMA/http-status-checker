<?php
/**
 * HTTP STATUS CHECKER
 *
 * STARTファイル
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

// 全てのエラー出力をオフにする
// error_reporting(0);
// 単純な実行時エラーを表示する
// error_reporting(E_ERROR | E_WARNING | E_PARSE);
// E_NOTICE を表示させるのもおすすめ（初期化されていない
// 変数、変数名のスペルミスなど…）
// error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
// E_NOTICE 以外の全てのエラーを表示する
// これは php.ini で設定されているデフォルト値
// error_reporting(E_ALL ^ E_NOTICE);
// 全ての PHP エラーを表示する (Changelog を参照ください)
error_reporting(E_ALL);
// 全ての PHP エラーを表示する
// error_reporting(-1);
// error_reporting(E_ALL);// と同じ
// ini_set('error_reporting', E_ALL);
if(!isset($_SESSION)){
    session_save_path('/var/www/session');
    session_name('HTTPSTATUSCHECKER');
    session_start();
}
$startNowTime = microtime(true);
ini_set('default_mimetype', 'text/html');
ini_set('default_charset', 'UTF-8');
mb_language('Japanese');
mb_internal_encoding('UTF-8');
mb_http_input('pass');
mb_http_output('UTF-8');

/*====================
  BEFORE ACTIONS
  ====================*/

require 'config.php';
require 'functions.php';
switch($ini['DataBase']['type']){
    case 'mysql':
    default:
        require 'mysql.class.php';
        $dsnformat = '%s:host=%s;dbname=%s';
        $dsn = sprintf($dsnformat, $ini['DataBase']['type'], $ini['DataBase']['server'], $ini['DataBase']['dbname']);
        $db = new dbEngine($dsn, $ini['DataBase']['dbname'], $ini['DataBase']['username'], $ini['DataBase']['password']);
        break;
}


/*====================
  MAIN ACTIONS
  ====================*/


/*====================
  AFTER ACTIONS
  ====================*/

/*====================
  FUNCTIONS
  ====================*/


?>