<?php
/**
 *
 *
 */
class tx_mocdyngoosm_hooks{
	public function preStartPageHook($params, $pObj){
		return;
		if ($pObj->bodyTagId == 'typo3-alt-doc-php') {
			$pageRenderer = $pObj->getPageRenderer();
//			$pObj->jScode .= '<script type="text/javascript" src="/typo3conf/ext/moc_dyngoosm/res/beSEOterms.js" ></script>';
//			$pObj->JScode .= '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>';
//			$pObj->JScode .= '<script type="text/javascript">jQuery.noConflict()</script>';
//			$pObj->JScode .= '<script type="text/javascript">
//				jQuery(document).ready(function(){jQuery(\'.docheader-row2-left\').append(\''.$this->getSEObox($params, $pObj).'\');});
//			</script>';
			$pObj->JScode .= '<script type="text/javascript">
			   var mdgsm_html = \''.$this->getSEObox($params, $pObj).'\';
			</script>';
			$pObj->JScode .= '<script type="text/javascript" src="/typo3conf/ext/moc_dyngoosm/res/beSEOterms.js" ></script>';
			$pObj->addStyleSheet('mdgsm', '../typo3conf/ext/moc_dyngoosm/res/beSEOterms.css');
		}
	}
	private function getSEObox($params, $pObj){
	    $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moc_dyngoosm']);
	    $sw = explode(',',$extConf['PRIMARY_SW']);
	    $img = '/typo3conf/ext/moc_dyngoosm/res/seoterms_icon.gif';
	    $content .= '<img src="'.$img.'" width="40" height="20" id="mdgsm_icon" />';
	    $content .= '<ol id="mdgsm_list">';
	    foreach($sw as $w){
		$content .= '<li><input type="text" value="'.$w.'" onClick="this.select();" /></li>';
	    }
	    $content .= '</ol>';
	    return $content;
	}
}

?>