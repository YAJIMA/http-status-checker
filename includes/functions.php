<?php
function execTrigger($url){
    $ch = curl_init();
    if(!$ch){
        die("Could`t initialize a cURL handler".PHP_EOL);
    }
    
    // set some cURL options
    $ret = curl_setopt($ch, CURLOPT_URL,            $url);
    $ret = curl_setopt($ch, CURLOPT_HEADER,         0);// TRUE を設定すると、ヘッダの内容も出力します。
    $ret = curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);// TRUE を設定すると、サーバーが HTTP ヘッダの一部として送ってくる "Location: " ヘッダの内容をたどります （これは再帰的に行われます。CURLOPT_MAXREDIRS が指定されていない限り、送ってくる "Location: " ヘッダの内容をずっとたどり続けることに注意しましょう）。
    $ret = curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);// TRUE を設定すると、 curl_exec() の返り値を 文字列で返します。通常はデータを直接出力します。
    $ret = curl_setopt($ch, CURLOPT_TIMEOUT,        EXEC_TIMEOUT);// cURL 関数の実行にかけられる時間の最大値。
    
    // execute
    $ret = curl_exec($ch);
    
    return $ret;
}

function randIDs($replaces, $replaceID){
    $result = "";
    // 置換先IDが複数あれば
    $r = array_rand($replaces);// rand(0, count($replaces) - 1);
    
    // 2013/07/25　置換先配列と置換IDの重複チェック
    foreach($replaces as $rk => $rp){
        // 置換IDのKeyと一致する置換先配列があれば、削除。
        if(array_key_exists($rp['id_name'], $replaceID)){
            unset($replaces[$rk]);
        }
    }
    unset($rp);
    
    // 既に同名の置換先IDがあれば、それを削除して、もう一度ランダム
    if(array_search($replaces[$r]['id_name'], $replaceID) !== false){
        unset($replaces[$r]);
        $result = randIDs($replaces, $replaceID);
    }else{
        $result = $replaces[$r]['id_name'];
    }
    return $result;
}

function removeClasses(&$replaceCLASS, &$replaceCSS){
    //class="twitter-share-button"
    //class="fb-like"
    //class="hatena-bookmark-button"
    $removeClasses = array("twitter-share-button", "fb-like", "hatena-bookmark-button");
    foreach($replaceCLASS as $classname => $replacevalue){
        if(array_search($classname, $removeClasses) !== false){
            unset($replaceCLASS[$classname]);
            unset($replaceCSS['.'.$classname]);
        }
    }
    unset($classname, $replacevalue);
}

function optimizeReplaces(&$replaceID2CLASS, &$replaceID, &$replaceCSS){
    // IDからCLASSに変える配列。IDを変える配列。
    // それらから重複する値を変更する関数。
    // 
    // 関数に渡される変数は、一次配列！
    // 変更後のIDに、CLASSに変換されてしまうものがないかチェックし、
    // あれば、IDの変換後の値を変更。
    foreach($replaceID as $beforeID => $afterID){
        while($tergetKey = current($replaceID2CLASS)){
            if(key($replaceID2CLASS) == $afterID){
                $pre = genPass(3);// プレフィクス文字列
                $replaceID[$beforeID] = $pre.$afterID;
                $replaceCSS['#'.$afterID] = '#'.$pre.$afterID;
            }
            next($replaceID2CLASS);
        }
    }
    unset($beforeID,$afterID);
}

function execChangeContents(&$contents, &$replaceCSS, $replaceID, $replaceCLASS, $replaceID2CLASS){
    // IDからCLASSに変える配列。IDを変える配列。CLASSを変える配列。
    // それぞれの配列から、ページコンテンツを書き換え、スタイルシートを変える配列を作成。
    // 
    // 配列は、それぞれ一次配列に変換されていること！
    // 
    // IDをCLASSに変更
    foreach($replaceID2CLASS as $id => $class){
        $contents = str_replace('id="'.$id.'"', 'class="'.$class.'"', $contents);
        $contents = str_replace("id='".$id."'", "class='".$class."'", $contents);
        $contents = str_replace('id=\"'.$id.'\"', 'class=\"'.$class.'\"', $contents);
        $contents = str_replace("id=\'".$id."\'", "class=\'".$class."\'", $contents);
        $replaceCSS['#'.$id] = '.'.$class;
    }
    unset($id,$class);
    
    // IDを変更
    foreach($replaceID as $id => $new){
        $contents = str_replace('id="'.$id.'"', 'id="'.$new.'"', $contents);
        $contents = str_replace("id='".$id."'", "id='".$new."'", $contents);
        $contents = str_replace('id=\"'.$id.'\"', 'id=\"'.$new.'\"', $contents);
        $contents = str_replace("id=\'".$id."\'", "id=\'".$new."\'", $contents);
        $replaceCSS['#'.$id] = '#'.$new;
    }
    unset($id,$newid);
    
    // CLASSを変更
    foreach($replaceCLASS as $class => $new){
        $contents = str_replace('class="'.$class.'"', 'class="'.$new.'"', $contents);
        $contents = str_replace("class='".$class."'", "class='".$new."'", $contents);
        $contents = str_replace('class="'.$class.' ', 'class="'.$new.' ', $contents);
        $contents = str_replace("class='".$class." ", "class='".$new." ", $contents);
        $contents = str_replace(' '.$class.'"', ' '.$new.'"', $contents);
        $contents = str_replace(" ".$class."'", " ".$new."'", $contents);
        $contents = str_replace('class=\"'.$class.'\"', 'class=\"'.$new.'\"', $contents);
        $contents = str_replace("class=\'".$class."\'", "class=\'".$new."\'", $contents);
        $contents = str_replace('class=\"'.$class.' ', 'class=\"'.$new.' ', $contents);
        $contents = str_replace("class=\'".$class." ", "class=\'".$new." ", $contents);
        $contents = str_replace(' '.$class.'\"', ' '.$new.'\"', $contents);
        $contents = str_replace(" ".$class."\'", " ".$new."\'", $contents);
        if(array_search('.'.$class, $replaceCSS) !== false){
            // 何もしない
        }else{
            $replaceCSS['.'.$class] = '.'.$new;
        }
    }
    unset($class,$new);
    
    return array("contents"=>$contents, "replaceCSS"=>$replaceCSS);
}

function execChangeCSS(&$csscontents, $replaceCSS){
    // スタイルシートを配列に従って書き換える関数。
    // 
    foreach($replaceCSS as $key => $val){
        $csscontents = str_replace($key.' ', $val.' ', $csscontents);
        $csscontents = str_replace($key.'{', $val.'{', $csscontents);
        $csscontents = str_replace($key.':', $val.':', $csscontents);
    }
    unset($key,$val);
    return $csscontents;
}

function execChangeMeta($contents, $metavalue){
    // メタタグをページコンテンツに挿入＆削除
    // 挿入するメタタグを引数で渡すこと。
    // 
    if(empty($contents)) return false;
    if(empty($metavalue)) return false;
    // METAタグの挿入位置
    $metaposition = array();
    $metaposition[] = array("format"=>"<head>\n%s", "replace"=>"<head>");
    $metaposition[] = array("format"=>"%s\n<title>", "replace"=>"<title>");
    $metaposition[] = array("format"=>"</title>\n%s", "replace"=>"</title>");
    $metaposition[] = array("format"=>"%s\n</head>", "replace"=>"</head>");
    $metaposes = count($metaposition) -1;
    $r = rand(0, $metaposes);
    $metaplaces = $metaposition[$r];
    
    // METAタグ更新
    $contents = str_replace($metaplaces["replace"], sprintf($metaplaces["format"], $metavalue), $contents);
    
    // NOYDIR削除
    $contents = str_replace('<meta name="Slurp" content="NOYDIR">', '', $contents);
    $contents = str_replace('<meta name="Slurp" content="NOYDIR" />', '', $contents);
    
    return $contents;
}

function getDomainName($testdomain){
    $split = array_reverse(explode(".", $testdomain));
    $domain = $split[1].".".$split[0];
    
    if(function_exists('gethostbyname')){
        if(gethostbyname($domain) != gethostbyname($testdomain) && isset($split[2])){   
            $domain = $split[2].".".$split[1].".".$split[0];
        }
    }
    
    return $domain;
}

function genPass($len = 8){
    return substr(str_shuffle('abcefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'), 0, $len);
}

function shortstr($str = '', $num = 4, $format = '<span style="font-size:80%;">[+str+]</span>'){
    if(mb_strlen($str,'UTF-8') > $num){
        return str_replace('[+str+]', $str, $format);
    }else{
        return $str;
    }
}

function kakunin(){
    $return = array();
    $remote_ip = (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != "") ? $_SERVER['REMOTE_ADDR'] : gethostbyaddr($remote_ip);
    $remote_host = (isset($_SERVER['REMOTE_HOST']) && $_SERVER['REMOTE_HOST'] != "") ? $_SERVER['REMOTE_HOST'] : gethostbyaddr($remote_ip);
    $return['remote_ip'] = $remote_ip;
    $return['remote_host'] = $remote_host;
    if($remote_ip == $remote_host){
        $return['remote_name'] = $remote_ip;
    }else{
        $return['remote_name'] = $remote_host.' ['.$remote_ip.']';
    }
    return $return;
}

/**
 * フォームから来たデータをエンコードする
 * @param array $post フォームから来たデータ
 */
function FormEncode(&$post){
    if(!isset($post['enc'])){
        return;
    }
    //どのエンコーディングか判定
    $enc = mb_detect_encoding($post['enc']);
    $default_enc = "UTF-8";
    foreach($post as &$value) {
        EncodeCore($value,$default_enc,$enc);
    }
    unset($value);
}
/**
* エンコードのコア部分
* @param unknown_type $value
* @param string $default_enc
* @param string $enc
*/
function EncodeCore(&$value, $default_enc, $enc){
    if(is_array($value)){
        //配列の場合は再帰処理
        foreach ($value as &$value2) {
            EncodeCore($value2, $default_enc, $enc);    
        }
    }elseif($enc != $default_enc){
        //文字コード変換
        $value = mb_convert_encoding($value, $default_enc, $enc) ;
    }
}

/**
 * ファイルポインタから行を取得し、CSVフィールドを処理する
 * @param resource handle
 * @param int length
 * @param string delimiter
 * @param string enclosure
 * @return ファイルの終端に達した場合を含み、エラー時にFALSEを返します。
 */
function fgetcsv_reg (&$handle, $length = null, $d = ',', $e = '"') {
    $d = preg_quote($d);
    $e = preg_quote($e);
    $_line = "";
    $eof = false;
    while (($eof != true)and(!feof($handle))) {
        $_line .= (empty($length) ? fgets($handle) : fgets($handle, $length));
        $itemcnt = preg_match_all('/'.$e.'/', $_line, $dummy);
        if ($itemcnt % 2 == 0) $eof = true;
    }
    $_csv_line = preg_replace('/(?:\\r\\n|[\\r\\n])?$/', $d, trim($_line));
    $_csv_pattern = '/('.$e.'[^'.$e.']*(?:'.$e.$e.'[^'.$e.']*)*'.$e.'|[^'.$d.']*)'.$d.'/';
    preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
    $_csv_data = $_csv_matches[1];
    for($_csv_i=0;$_csv_i<count($_csv_data);$_csv_i++){
        $_csv_data[$_csv_i]=preg_replace('/^'.$e.'(.*)'.$e.'$/s','$1',$_csv_data[$_csv_i]);
        $_csv_data[$_csv_i]=str_replace($e.$e, $e, $_csv_data[$_csv_i]);
    }
    return empty($_line) ? false : $_csv_data;
}

// フォルダ内を再帰的に削除
function delete_folder($tmp_path){
    if(!is_writeable($tmp_path) && is_dir($tmp_path)){chmod($tmp_path,0777);}
    $handle = opendir($tmp_path);
    while($tmp=readdir($handle)){
        if($tmp!='..' && $tmp!='.' && $tmp!=''){ 
            if(strrpos($tmp, ".csv", -4) !== false){
                $tname = $tmp_path.$tmp;
                if(is_writeable($tname) && is_file($tname)){ 
                    unlink($tname);
                }elseif(!is_writeable($tname) && is_file($tname)){
                    chmod($tname,0666);
                    unlink($tname); 
                }
                if(is_writeable($tname) && is_dir($tname)){
                    delete_folder($tname);
                }elseif(!is_writeable($tname) && is_dir($tname)){
                    chmod($tname,0777);
                    delete_folder($tname);
                } 
            }
        } 
    } 
    closedir($handle); 
    return true;
} 

/**
 * Recursively delete a directory
 *
 * @param string $dir Directory name
 * @param boolean $deleteRootToo Delete specified top-level directory as well
 *　$dirは消去したい一番上のディレクトリ
 *  $deleteRootTooは一番上のディレクトリを消去したい場合にtrueを設定
 */
// http://onlineconsultant.jp/pukiwiki/?PHP%20%E3%83%87%E3%82%A3%E3%83%AC%E3%82%AF%E3%83%88%E3%83%AA%E5%86%85%E3%81%AE%E3%83%95%E3%82%A1%E3%82%A4%E3%83%AB%E3%80%81%E3%82%B5%E3%83%96%E3%83%87%E3%82%A3%E3%83%AC%E3%82%AF%E3%83%88%E3%83%AA%E3%82%92%E4%B8%80%E6%8B%AC%E6%B6%88%E5%8E%BB
function unlinkRecursive($dir, $deleteRootToo = false){
    $results = array();
    // ディレクトリが開けなければ終了
    if(!$dh = @opendir($dir)) return;
    
    // ディレクトリ内を再帰処理
    while(false !== ($obj = readdir($dh))){
        if($obj == '.' || $obj == '..') continue;
        if(is_file($dir.'/'.$obj)){
            if(!is_writeable($dir.'/'.$obj)) chmod($dir.'/'.$obj, 0666);
            @unlink($dir.'/'.$obj);
            $results[] = $dir.'/'.$obj;
        }elseif(is_dir($dir.'/'.$obj)){
            unlinkRecursive($dir.'/'.$obj, true);
        }
        //if(!@unlink($dir . '/' . $obj)) unlinkRecursive($dir.'/'.$obj, true);
    }
    closedir($dh);
    
    // 指定のディレクトリを削除する？
    if ($deleteRootToo){
        if(!is_writeable($dir)) chmod($dir, 0777);
        @rmdir($dir);
        $results[] = $dir;
    }
    return $results;
}

function mailFormatter($mailtxt='', $maildir='./includes/email/'){
    $return = array();
    $mailStr = file_get_contents($maildir.$mailtxt);
    $mailSp = strpos($mailStr, "body:", 0);
    $return["mailbody"] = substr($mailStr, $mailSp + 5);
    $return["subject"] = str_replace("subject:", "", substr($mailStr, 0, $mailSp));
    return $return;
}

function resize_dimensions($goal_width,$goal_height,$width,$height) {
    $return = array('width' => $width, 'height' => $height);
    
    // If the ratio > goal ratio and the width > goal width resize down to goal width 
    if ($width/$height > $goal_width/$goal_height && $width > $goal_width) {
        $return['width'] = $goal_width;
        $return['height'] = $goal_width/$width * $height;
    }
    // Otherwise, if the height > goal, resize down to goal height 
    else if ($height > $goal_height) {
        $return['width'] = $goal_height/$height * $width;
        $return['height'] = $goal_height;
    } 
    
    return $return;
}

function mce($str){
    if(get_magic_quotes_gpc()){
        $str = mb_convert_encoding(stripslashes($str),"UTF-8","auto");
    }else{
        $str = mb_convert_encoding($str,"UTF-8","auto");
    }
    return $str;
}

function hs($str){
    $str = mce($str);
    $str = htmlspecialchars($str);
    return $str;
}

function ue($str){
    $str = mce($str);
    $str  = urlencode($str);
    return $str;
}

function he($str){
    $str = mce($str);
    $str  = htmlentities($str,ENT_QUOTES,"UTF-8");
    return $str;
}

function data_convert($data){ //GET、POSTデータコンバート
    if(!is_array($data)){
        $data = mce($data);
        $data = str_replace("\r\n","\n",$data);
        $data = str_replace("\r","\n",$data);
        $data = str_replace("&lt;br&gt;","\n",$data);
        $data = str_replace("\n","<br />",$data);
        $data = str_replace("'","’",$data);
        $data = str_replace("\"","”",$data);
        $data = str_replace(",","、",$data);
        $data  = strip_tags($data);
        $data  = htmlspecialchars($data);
    }elseif(is_array($data)){
        foreach($data as $val){
            $newval = mce($val);
            $newval = str_replace("\r\n","\n",$newval);
            $newval = str_replace("\r","\n",$newval);
            $newval = str_replace("&lt;br&gt;","\n",$newval);
            $newval = str_replace("\n","<br />",$newval);
            $newval = str_replace("'","’",$newval);
            $newval = str_replace("\"","”",$newval);
            $newval = str_replace(",","、",$newval);
            $newval  = strip_tags($newval);
            $newval  = htmlspecialchars($newval);
            $newdata[] = $newval;
        }
        unset($val);
        $data = $newdata;
    }
    return $data;
}

function hashconv($d, $raw_output = false, $salt = SECURE_SALT){
    $d .= $salt;
    if(version_compare(PHP_VERSION, '5.0.0', '<')){
        $d = md5($d);
        $d = sha1($d);
    }else{
        $d = md5($d, $raw_output);
        $d = sha1($d, $raw_output);
    }
    return $d;
}

function errors($d = '', $format = '%s<br />'){
    $res = '';
    if(is_array($d)){
        foreach($d as $v){
            $res .= sprintf($format, $v) . "\n";
        }
    }
    return $res;
}

function HBsendMail($to, $subject, $body, $from_email, $from_name, $from_enc="UTF-8", $cc, $bcc){
    mb_language("ja");
    mb_internal_encoding($from_enc);
    $result = false;
    
    /* Mail, headers */
    $headers  = "MIME-Version: 1.0 \r\n" ;
    if(!empty($cc)){
        if(is_array($cc)){
            $headers .= "Cc: ".implode(',', $cc)." \r\n";
        }else{
            $headers .= "Cc: ".$cc." \r\n";
        }
    }
    if(!empty($bcc)){
        if(is_array($bcc)){
            $headers .= "Bcc: ".implode(',', $bcc)." \r\n";
        }else{
            $headers .= "Bcc: ".$bcc." \r\n";
        }
    }
    $headers .= "From: " .
        mb_encode_mimeheader (mb_convert_encoding($from_name,"ISO-2022-JP",$from_enc)) .
        "<".$from_email."> \r\n";
    $headers .= "Reply-To: " .
        mb_encode_mimeheader (mb_convert_encoding($from_name,"ISO-2022-JP",$from_enc)) .
        "<".$from_email."> \r\n";
    $headers .= "Content-Type: text/plain;charset=ISO-2022-JP \r\n";
    
    /* Mail, body */
    $body = mb_convert_encoding($body, "ISO-2022-JP", $from_enc);
    
    /* Mail, optional paramiters. */
    $sendmail_params  = "-f$from_email";
    
    /* Mail, subject */
    $subject = mb_convert_encoding($subject, "ISO-2022-JP", $from_enc);
    $subject = "=?iso-2022-jp?B?" . base64_encode($subject) . "?=";
    
    /* Mail, sending */
    $result = mail($to, $subject, $body, $headers, $sendmail_params);
    
    return $result;
}

function mailsending($mailto = '', $mailbody = '', $subject = '') {
    // メール送信
    // カレントの言語を日本語に設定する
    mb_language("ja");
    // 内部文字エンコードを設定する
    mb_internal_encoding("UTF-8");
    
    if($subject == '') $subject = SITE_NAME;
    $headers = "from: " . mb_encode_mimeheader(mb_convert_encoding(SITE_NAME,"ISO-2022-JP","AUTO")) . "<" . FROM_ADDRESS . ">\n";
    if ($mailto != '') {
        $mailsending = mb_send_mail($mailto, $subject, $mailbody, $headers);
    }else{
        return false;
    }
    return $mailsending;
}

function paginate($pageall = 0, $page_current = 0){
    $results = array();
    /* 
    $pageall 全ページ数
    $page_current 現在のページ
    $pagingformat リンクフォーマット　'index.php?mainpage=mypage&amp;pages=%s'
     */
    if(intval($page_current) == 0) $page_current = 1;
    if ( $page_current > 0 ) {
        if($page_current != 1){
            $results[] = array("pagenum"=>(int)$page_current - 1, "type"=>"prev");
        }
    }
    for ( $i=0,$j=1; $i < $pageall; $i++,$j++ ) {
        if ( $j == $page_current ) {
            $results[] = array("pagenum"=>$j, "type"=>"current");
        } else {
            $results[] = array("pagenum"=>$j, "type"=>"page");
        }
    }
    if ( $page_current < $pageall ) { 
        $results[] = array("pagenum"=>(int)$page_current + 1, "type"=>"next");
    }
    return $results;
}

function paging($pageall = 0, $page_current = 0, $linkformat = 'index.php?mainpage=mypage&amp;pages=%s', $outerClass = 'paging tcenter'){
    $contents = '';
    /* 
    $pageall 全ページ数
    $page_current 現在のページ
    $pagingformat リンクフォーマット　'index.php?mainpage=mypage&amp;pages=%s'
     */
    if(intval($page_current) == 0) $page_current = 1;
    $contents .= "\n<ul class=\"".$outerClass."\">\n";
    if ( $page_current > 0 ) {
        if($page_current != 1){
            $contents .= "<li class=\"prev\"><a href=\"" . sprintf($linkformat, (int)$page_current - 1 ) . "\" class=\"prev\">前へ</a></li>&nbsp;\n";
        }
    }
    for ( $i=0,$j=1; $i < $pageall; $i++,$j++ ) {
        if ( $j == $page_current ) {
            $contents .= "<li class=\"current\"><span>$j</span></li>&nbsp;\n";
        } else {
            if($i == 0){
                $contents .= "<li><a href=\"" . sprintf($linkformat, $j ) . "\">$j</a></li>&nbsp;\n";
            }else{
                $contents .= "<li><a href=\"" . sprintf($linkformat, $j ) . "\">$j</a></li>&nbsp;\n";
            }
        }
    }
    if ( $page_current < $pageall ) { 
        $contents .= "<li class=\"next\"><a href=\"" . sprintf($linkformat, (int)$page_current + 1 ) . "\" class=\"next\">次へ</a></li>&nbsp;\n"; 
    }
    $contents .= "</ul>\n";
    return $contents;
}

function debug($data = NULL){
    $result = "";
    if(DEBUG){
        $result .= '<div class="debug"><div class="debugheader"><p>デバッグデータ</p></div><pre class="debugbody">';
        if(is_array($data)){
            $result .= "DEBUGS : \n";
            print_r($data);
            $result .= "\n";
        }
        $result .= "SESSION : \n";
        $result .= print_r($_SESSION, true);
        $result .= "GET : \n";
        $result .= print_r($_GET, true);
        $result .= "POST : \n";
        $result .= print_r($_POST, true);
        $result .= "SERVER : \n";
        $result .= print_r($_SERVER, true);
        $result .= "\nデバッグを解除するには、コンフィグレーションファイルのデバッグモードを解除して下さい。\n";
        $result .= '</pre><div class="debugfooter">&nbsp;</div></div>';
    }
    return $result;
}

?>