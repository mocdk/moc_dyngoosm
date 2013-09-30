<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Mikkel H. Henriksen <mikkel@mocsystems.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'MOC Dynamic Google Sitemap' for the 'moc_dyngoosm' extension.
 *
 * @author	Mikkel H. Henriksen <mikkel@mocsystems.com>
 * @package	TYPO3
 * @subpackage	tx_mocdyngoosm
 */
class tx_mocdyngoosm_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_mocdyngoosm_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_mocdyngoosm_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'moc_dyngoosm';	// The extension key.

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!

		$this->pi_initPIflexForm();

		$GLOBALS["TSFE"]->config["config"]["disableAllHeaderCode"] = 1;

		$this->errorWrapper = '<html><head><title>Error in plugin: tx_mocdyngoosm</title></head><body><h3>An exception is thrown by tx_mocdyngoosm with the following message:</h3><p>###ERROR_MSG###</p></body></html>';
		$this->is_index = (intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'display_type','sDEF')) === 1) || (intval($this->conf['is_index'])===1);
		try{
			$this->xml = '<?xml version="1.0" encoding="UTF-8"?>'.chr(10);
			$this->xml .= $this->generateXML();
		}
		catch(Exception $e){
			exit(str_replace('###ERROR_MSG###',$e->getMessage(), $this->errorWrapper));
		}

		ob_start();
		header('Content-type: text/xml');
		print($this->xml);
		ob_flush();
		exit();
	}

	private function generateXML(){
		if($this->is_index){
			$this->mapList = t3lib_div::trimExplode(chr(10),$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'links_to_sitemaps','sIndex'),true);
			if(count($this->mapList) === 0){
				$this->mapList = t3lib_div::trimExplode('||',$this->conf['siteMaps'],true);
				if(count($this->mapList) === 0){
					throw new Execption('No sitemaps defined for the index!');
				}
			}
			return $this->getIndexXML();

		}else{
			try{
				$this->getConf();
				$this->pageArr = $this->getPages();
				return $this->getSitemapXML();
			}
			catch(Exception $e){
				throw new Exception('Could not get configuration: '.$e->getMessage());
			}
		}
	}

	private function getIndexXML(){
		$xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.chr(10);
		foreach($this->mapList as $sitemap){
			$lastmod = $this->getLastModified($sitemap);
			$xml .= '<sitemap><loc>'.t3lib_div::locationHeaderUrl(htmlentities($sitemap)).'</loc><lastmod>'.$lastmod.'</lastmod></sitemap>';
		}
		$xml .= chr(10).'</sitemapindex>';
		return $xml;
	}

	private function getSitemapXML(){
		$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.chr(10);
		foreach($this->pageArr as $page){
			$xml .= '<url>';
			$xml .= '<loc>'.t3lib_div::locationHeaderUrl(htmlentities($page['url'])).'</loc>';
			$xml .= '<lastmod>'.date('c',$page['lastmod']).'</lastmod>';
			$xml .= '<priority>'.$this->priority.'</priority>';
			$xml .= '</url>'.chr(10);
		}
		$xml .= '</urlset>';
		return $xml;
	}

	private function getPages(){
		$pagearr = array();
		$limit = $this->defineLimit();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,'.$this->lastmodField,$this->pageTable,$this->where.' '.$this->additionalWhere.' '.$this->cObj->enableFields($this->pageTable),'',$this->lastmodField.' DESC',$limit);
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			$linkConf = array(
				'parameter' => $this->singlePid,
				'additionalParams' => '&'.$this->piVar_identifier.'='.$row['uid'].$this->additionalParams,
			);
			$url = $this->cObj->typoLink_URL($linkConf);
			$pagearr[] = array('url'=>$url,'lastmod'=>$row['tstamp']);
		}

		return $pagearr;
	}

	private function defineLimit(){
		if($this->parts > 1){
			if($this->partial > 0){
				$pidPart = $this->storagePid ? 'pid='.$this->storagePid : 'pid > -1';
				//$row = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('COUNT(uid) as cnt',$this->pageTable,'pid='.$this->storagePid.' '.$this->additionalWhere.' '.$this->cObj->enableFields($this->pageTable));
				$row = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('COUNT(uid) as cnt',$this->pageTable, $pidPart.$this->additionalWhere.$this->cObj->enableFields($this->pageTable));
				$rowcount = $row[0]['cnt'];
				$chunk = ceil($rowcount/$this->parts);
				$this->partial == 1?$offset = 0:$offset = ($this->partial-1)*$chunk;
				return $offset.','.$chunk;
			}
		}
		return '';
	}

	private function getLastModified_OLD($url){
		$lastmod = 0;
		$xmlreader = new XMLReader();
		if(!@$xmlreader->open($url)){
			throw new Exception('Could not open the sitemap defined: '.$url);
		}
		while($xmlreader->read()){
			if($xmlreader->nodeType == XMLReader::ELEMENT){
				if($xmlreader->name == 'lastmod'){
					$xmlreader->read();
					$tmp = strtotime($xmlreader->value);
					if($tmp > $lastmod){
						$lastmod = $tmp;
					}
				}
			}
		}
		$lastmod = date('c',$lastmod);
		return $lastmod;
	}

	private function getLastModified($url){
		$lastmod = 0;
		$tablename = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'sitemaps_table','sIndex');
		if (isset($tablename) && strlen(trim($tablename)) > 0) {
			$ffLastMod = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'last_modified_field_all','sIndex');
			$lastModField = (strlen(trim($ffLastMod)) > 0) ? $ffLastMod : 'tstamp';
			$urlParts = parse_url($url);

			$queryParts = t3lib_div::explodeUrl2Array($urlParts['query']);

			if (count($queryParts) >= 2 && isset($queryParts['parts']) && isset($queryParts['partial'])) {
				$this->parts = intval($queryParts['parts']);
				$this->partial = intval($queryParts['partial']);
				$this->pageTable = $tablename;
				$this->lastmodField = $lastModField;
				$additionalWhere = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'index_additional_where','sIndex');
				if (strlen($additionalWhere) === 0) {
					$additionalWhere = $this->conf['index_additional_where'];
				}
				$this->additionalWhere = $this->prepareAdditionalWhere($additionalWhere);
				$limit = $this->defineLimit();
				if (strlen($limit) === 0) {
					return date('c', mktime()-(100*24*60*60)); //Can't determine limit - set lastmod to 100 days ago.
				}
				$row = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($this->lastmodField.' as lastmod',$this->pageTable,'1=1'.$this->additionalWhere.$this->cObj->enableFields($this->pageTable), '', $this->lastmodField . ' DESC', $limit);

				return date('c', intval($row[0]['lastmod']));
			}
		}
		$xmlreader = new XMLReader();
		if(!@$xmlreader->open($url)){
			throw new Exception('Could not open the sitemap defined: '.$url);
		}
		while($xmlreader->read()){
			if($xmlreader->nodeType == XMLReader::ELEMENT){
				if($xmlreader->name == 'lastmod'){
					$xmlreader->read();
					$tmp = strtotime($xmlreader->value);
					if($tmp > $lastmod){
						$lastmod = $tmp;
					}
				}
			}
		}
		$lastmod = date('c',$lastmod);
		return $lastmod;
	}

	private function prepareAdditionalWhere($wherePart) {
		$wherePart = trim($wherePart);
		if (strlen($wherePart) === 0) {
			return '';
		}
		$andFirstPos = strpos(strtoupper($wherePart), 'AND');
		$orFirstPos = strpos(strtoupper($wherePart), 'OR');
		if (($andFirstPos > 0 || $andFirstPos === -1) && $orFirstPos !== 0) {
			$wherePart = 'AND ' . $wherePart . ' ';
		}
		$wherePart = ' ' . $wherePart;
		return mysql_real_escape_string($wherePart);
	}

	private function getConf(){
		$ffFields = array
		(
			'pageTable'=>'table_of_records',
			'storagePid'=>'storage_pid',
			'singlePid'=>'pid_singleview',
			'priority'=>'priority',
			'piVar_identifier' => 'pivar_identifier',
			'lastmodField'=>'lastmod_field',
			'additionalWhere' => 'additionalWhere',
			'additionalParams' => 'additionalParams'

		);
		$intvals = array('storagePid','singlePid');
		foreach($ffFields as $property=>$field){
			$this->$property = trim($this->pi_getFFvalue($this->cObj->data['pi_flexform'],$field,'sTable'));
			if(strlen($this->$property)==0){
				$this->$property = trim($this->conf[$property]);
			}
		}
		if(strlen($this->pageTable)==0){
			throw new Exception('No table for record given');
		}
		if(strlen($this->piVar_identifier)==0){
			$this->piVar_identifier = 'tx_'.$this->pageTable.'_pi1[uid]';
		}
		if(strlen(trim($this->storagePid))===0 && intval($this->storagePid) === 0){
			$this->where = 'pid > -1';
		}
		elseif(strpos($this->storagePid,',')){
			$storagePids = t3lib_div::intExplode(',',$this->storagePid, TRUE);
			$this->where = 'pid IN ('.implode(',', $storagePids);
		}
		else{
			$this->where = 'pid = '.$this->storagePid;
		}

		if(strlen(trim($this->additionalWhere)) > 0)
			$this->additionalWhere = trim($this->additionalWhere);

		if(t3lib_div::_GET('parts') > 1){
			if(t3lib_div::_GET('partial') > 0){
				$this->parts = intval(t3lib_div::_GET('parts'));
				$this->partial = intval(t3lib_div::_GET('partial'));
			}
		}
	}

	private function definePartsAndPartial() {
		if(t3lib_div::_GET('parts') > 1){
			if(t3lib_div::_GET('partial') > 0){
				$this->parts = intval(t3lib_div::_GET('parts'));
				$this->partial = intval(t3lib_div::_GET('partial'));
			}
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/moc_dyngoosm/pi1/class.tx_mocdyngoosm_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/moc_dyngoosm/pi1/class.tx_mocdyngoosm_pi1.php']);
}

?>