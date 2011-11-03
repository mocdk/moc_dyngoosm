<?php
function user_googleanalytics(){
	$extConf = array();
	$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moc_dyngoosm']);
	$analytics = '
<script type="text/javascript">var teat;
  var _gaq = _gaq || [];
  _gaq.push([\'_setAccount\', \'###GA_ACCOUNT###\']);
  _gaq.push([\'_trackPageview\']);
  (function() {
    var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
    ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';
    var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>';
	if($extConf['GA_ACCOUNT_PROFILETLD']){
	    $host = t3lib_div::getIndpEnv('HTTP_HOST');
	    $parts = explode('.',$host); 
	    $tld = $parts[count($parts)-1];
	    $accounts = t3lib_div::trimExplode(';',$extConf['GA_ACCOUNT_PROFILETLD']);
	    foreach($accounts as $account){
		$ga = t3lib_div::trimExplode('=', $account);
		if($tld == $ga[0]){
		    $GLOBALS['TSFE']->additionalHeaderData['MOC_dyngoosm_ga'] = str_replace('###GA_ACCOUNT###', $ga[1], $analytics);
		    return;
		}
	    }
	}
	if($extConf['GA_ACCOUNT']){
	    $GLOBALS['TSFE']->additionalHeaderData['MOC_dyngoosm_ga'] = str_replace('###GA_ACCOUNT###', $extConf['GA_ACCOUNT'], $analytics);
	}
	return;
}
?>