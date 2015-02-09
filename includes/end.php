<?php
$endNowTime = microtime(true);
if($startNowTime){
	$spanTime = $endNowTime - $startNowTime;
}else{
	$spanTime = $endNowTime;
}
if(DEBUG){
	echo '<!-- '.print_r($_SESSION, true).' -->';
}
?>
<!-- <?php echo $spanTime; ?> -->