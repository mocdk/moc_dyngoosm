<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/template.php']['preStartPageHook'][] = 'EXT:moc_dyngoosm/hooks/class.tx_mocdyngoosm_hooks.php:tx_mocdyngoosm_hooks->preStartPageHook';

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_mocdyngoosm_pi1.php', '_pi1', 'list_type', 0);
include_once(t3lib_extMgm::extPath('moc_dyngoosm') . 'user_gaeventonpage.php');
include_once(t3lib_extMgm::extPath('moc_dyngoosm') . 'user_robotsonpage.php');
include_once(t3lib_extMgm::extPath('moc_dyngoosm') . 'user_googleanalytics.php');
?>