<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012  <>
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

// require_once(PATH_tslib . 'class.tslib_pibase.php');

/**
 * Plugin 'ZAF Sitemap' for the 'zaf_sitemap' extension.
 *
 * @author	 <>
 * @package	TYPO3
 * @subpackage	tx_zafsitemap
 */
class tx_zafsitemap_pi1 extends tslib_pibase {
	public $prefixId      = 'tx_zafsitemap_pi1';		// Same as class name
	public $scriptRelPath = 'pi1/class.tx_zafsitemap_pi1.php';	// Path to this script relative to the extension dir.
	public $extKey        = 'zaf_sitemap';	// The extension key.
	
	/**
	 * The main method of the Plugin.
	 *
	 * @param string $content The Plugin content
	 * @param array $conf The Plugin configuration
	 * @return string The content that is displayed on the website
	 */
	public function main($content, array $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		$this->pi_initPIflexForm();
	
		$this->startingPoint = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'startingPoint');
		
		header ("content-type: text/xml");
		$content  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
		
		$judeteList = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,judet', 'tx_judet','','', 'judet ASC');
	//debug($judeteList,'judete');
		
		foreach ($judeteList as $judet) {
			$content .= "\t" . '<url>' ."\n";
			$content .= "\t\t" .'<loc>' . $GLOBALS['TSFE']->baseUrl . $this->pi_getPageLink($this->startingPoint, '', array('tx_zafanunturi_pi2[id_judet]' => $judet['uid'] ) ) . '</loc>' . "\n";
		//	$content .= "\t\t" . '<lastmod>' .date('Y-m-d', $judet['tstamp']). '</lastmod>' . "\n";
			$content .= "\t" . '</url>' ."\n";
		}	
		
		$content .= '</urlset>';
		
		echo $content; exit();
	
		return $this->pi_wrapInBaseClass($content);
	}
}



if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/zaf_sitemap/pi1/class.tx_zafsitemap_pi1.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/zaf_sitemap/pi1/class.tx_zafsitemap_pi1.php']);
}

?>