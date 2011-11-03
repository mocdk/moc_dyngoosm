<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


t3lib_extMgm::addPlugin(array(
	'LLL:EXT:moc_dyngoosm/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

t3lib_extMgm::addStaticFile($_EXTKEY,'static/mdgsm/', 'mdgsm');


$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:moc_dyngoosm/flexform_ds_pi1.xml');


t3lib_div::loadTCA('pages');

$extConf = array();
$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moc_dyngoosm']);

if($extConf['ROBOTS']){
	$tempColumns = array (
	    'tx_mocdyngoosm_robots' => array (
	        'exclude' => 0,
	        'label' => 'LLL:EXT:moc_dyngoosm/locallang_db.xml:pages.tx_mocdyngoosm_robots',
	        'config' => array (
	            'type' => 'select',
	            'items' => array (
	                array('LLL:EXT:moc_dyngoosm/locallang_db.xml:pages.tx_mocdyngoosm_robots.I.0', 'INDEX'),
	                array('LLL:EXT:moc_dyngoosm/locallang_db.xml:pages.tx_mocdyngoosm_robots.I.1', 'FOLLOW'),
	                array('LLL:EXT:moc_dyngoosm/locallang_db.xml:pages.tx_mocdyngoosm_robots.I.2', 'NOINDEX'),
	                array('LLL:EXT:moc_dyngoosm/locallang_db.xml:pages.tx_mocdyngoosm_robots.I.3', 'NOFOLLOW'),
	                array('LLL:EXT:moc_dyngoosm/locallang_db.xml:pages.tx_mocdyngoosm_robots.I.4', 'NOARCHIVE'),
	                array('LLL:EXT:moc_dyngoosm/locallang_db.xml:pages.tx_mocdyngoosm_robots.I.5', 'NONE'),
	                array('LLL:EXT:moc_dyngoosm/locallang_db.xml:pages.tx_mocdyngoosm_robots.I.6', 'NOSNIPPET'),
	                array('LLL:EXT:moc_dyngoosm/locallang_db.xml:pages.tx_mocdyngoosm_robots.I.7', 'NOODP'),
	            ),
	            'size' => 5,
	            'maxitems' => 100,
	        )
	    ),
	);
	t3lib_extMgm::addTCAcolumns('pages',$tempColumns,1);
	t3lib_extMgm::addToAllTCAtypes('pages','tx_mocdyngoosm_robots,;;;;1-1-1','','after:description');
}
/*
$tempColumns = array();
$tempColumns['tx_mocdyngoosm_primary_search'] = array(
    'exclude' => 0,
    'label' => 'Primary search words',
    'readOnly' => 1,
    'config' => array(
	'type' => 'passthrough',
	'cols' => 45,
	'rows' => 10,
	'default' => 'LLL:EXT:moc_dyngoosm/pi1/locallang.xml:primary_search'
    )
);
t3lib_extMgm::addTCAcolumns('pages',$tempColumns,0);
t3lib_extMgm::addToAllTCAtypes('pages','tx_mocdyngoosm_primary_search;;;;1-1-1','','after:description');
*/
if($extConf['GA_EVENT']){
	t3lib_extMgm::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:'.$_EXTKEY.'/res/pageTSconfig.txt">');
}
if($extConf['GA_ACCOUNT']){
	t3lib_extMgm::addTypoScriptConstants('ga.account.default='.$extConf['GA_ACCOUNT']);
	t3lib_extMgm::addTypoScriptSetup('gaAccount < ${ga.account.default}');
}
if($extConf['GA_ACCOUNT_PROFILETLD']){
	$tldomains = t3lib_div::trimExplode(';',$extConf['GA_ACCOUNT_PROFILETLD']);
	foreach($tldomains as $tldomain){
	    t3lib_extMgm::addTypoScriptConstants('ga.account.'.$tldomain);
	    $tld = t3lib_div::trimExplode('=',$tldomain);
	    $ts_setup = '[globalString = IENV:HTTP_HOST = *.'.$tld[0].']
		';
	    $ts_setup .= 'gaAccount = {$ga.account.'.$tld[0].'}';
	    $ts_setup .= '[end]';
	    t3lib_extMgm::addTypoScript('setup','moc_dyngoosm',$ts_setup);
	}
}

?>