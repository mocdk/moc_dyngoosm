<?php
function user_robotsonpage(){
	$extConf = array();
	$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moc_dyngoosm']);
	if($extConf['ROBOTS']){
		return true;
	}
	return false;
}
?>