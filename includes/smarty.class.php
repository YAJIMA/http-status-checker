<?php
/*---------------------------------
Smarty管理クラス
---------------------------------*/
if(defined('SMARTY_DIR')) {
	require_once(SMARTY_DIR.'Smarty.class.php');
}elseif(defined('ROOT_DIR')){
	require_once(ROOT_DIR."includes/Smarty/libs/Smarty.class.php");
}else{
	require_once("/var/www/html/webadmin/http-status-checker/includes/Smarty/libs/Smarty.class.php");
}

class smartyEngine extends Smarty {
	
	function __construct() {
		parent::__construct();
		$this->template_dir = ROOT_DIR.'templates/';
		$this->compile_dir = ROOT_DIR.'templates_c/';
		$this->config_dir = ROOT_DIR.'configs/';
		$this->cache_dir = ROOT_DIR.'cache/';
		if(DEBUG){
			$this->caching = false;
			$this->debugging = true;
		}else{
			$this->caching = false;
			//$this->caching = Smarty::CACHING_LIFETIME_CURRENT;
		}
		// $this->assign('app_name', SITE_NAME);
		// $this->assign('app_url', SITE_URL);
	}
	
	function cacheEnable($switch = true) {
		if($switch == true){
			$this->caching = true;
		}else{
			$this->caching = false;
		}
	}
}
?>