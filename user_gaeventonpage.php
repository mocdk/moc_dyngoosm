<?php
function user_gaeventonpage(){
	$extConf = array();
	$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moc_dyngoosm']);
	if($extConf['GA_EVENT']){
		return true;
	}
	return false;
}
?>