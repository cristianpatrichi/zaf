<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Cristi <bau_baus2002@yahoo.com>
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
 * Plugin 'ZAF Harta' for the 'zaf_harta' extension.
 *
 * @author	Cristi <bau_baus2002@yahoo.com>
 * @package	TYPO3
 * @subpackage	tx_zafharta
 */
class tx_zafharta_pi1 extends tslib_pibase {
	public $prefixId      = 'tx_zafharta_pi1';		// Same as class name
	public $scriptRelPath = 'pi1/class.tx_zafharta_pi1.php';	// Path to this script relative to the extension dir.
	public $extKey        = 'zaf_harta';	// The extension key.
	public $pi_checkCHash = TRUE;
	
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
		$this->pi_initPIflexForm();
		$this->init();
		
	
		$content = '';
		//$content = $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '3' ) );
		//$content .= $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '4' ) );
		$content = $this->renderHarta();
	
		return $this->pi_wrapInBaseClass($content);
	}
	
	function init() {
		$whereToPoint = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'whereToPoint');
		$this->conf['whereToPoint'] = $whereToPoint ? $whereToPoint : $GLOBALS['TSFE']->id;
		
		$this->conf['templateFile'] =  $this->conf['templateFile'] ? $this->conf['templateFile'] : 'EXT:' . $this->extKey . '/res/template_pi1.html';
		$this->templateFile = $this->cObj->fileResource($this->conf['templateFile']);
		$this->subTemplate = $this->cObj->getSubpart($this->templateFile,"###HARTA###");
	}
	
	function renderHarta() {
		$this->subTemplate = $this->cObj->substituteMarkerArray($this->subTemplate, 
			array('###ALBA###'	 => $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '1' ) ),
				  '###ARAD###'	 => $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '2' ) ),
				  '###ARGES###'	 => $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '3' ) ),
				  '###BACAU###'	 => $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '4' ) ),
				  '###BISTRITA###'	=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '6' ) ),
				  '###BIHOR###'	 	=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '5' ) ),
				  '###BOTOSANI###'	=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '7' ) ),
				  '###BRASOV###'	=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '8' ) ),
				  '###BRAILA###'	=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '9' ) ),
			  	  '###BUZAU###'		=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '10' ) ),
				  '###CALARASI###'	=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '12' ) ),
				  '###CLUJ###'		=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '13' ) ),
				  '###CARASSEVERIN###'	=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '11' ) ),
				  '###CONSTANTA###'		=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '14' ) ),
				  '###COVASNA###'		=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '15' ) ),
				  '###DAMBOVITA###'		=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '16' ) ),
				  '###DOLJ###'			=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '17' ) ),
				  '###GALATI###'		=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '18' ) ),
				  '###GIURGIU###'		=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '19' ) ),
				  '###GORJ###'			=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '20' ) ),
				  '###HARGHITA###'		=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '21' ) ),
				  '###HUNEDOARA###'		=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '22' ) ),
				  '###IALOMITA###'		=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '23' ) ),
				  '###IASI###'			=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '24' ) ),															  	
				  '###BUCURESTI###'		=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '25' ) ),
			      '###MARAMURES###'		=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '26' ) ),																	  
				  '###MEHEDINTI###'		=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '27' ) ),
				  '###MURES###'			=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '28' ) ),
				  '###NEAMT###'			=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '29' ) ),
				  '###OLT###'			=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '30' ) ),
				  '###PRAHOVA###'		=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '31' ) ),
				  '###SATUMARE###'		=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '32' ) ),
				  '###SALAJ###'			=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '33' ) ),
				  '###SIBIU###'			=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '34' ) ),
				  '###SUCEAVA###'		=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '35' ) ),
				  '###TELEORMAN###'		=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '36' ) ),
				  '###TIMIS###'			=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '37' ) ),
				  '###TULCEA###'		=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '38' ) ),
				  '###VASLUI###'		=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '39' ) ),
				  '###VALCEA###'		=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '40' ) ),
				  '###VRANCEA###'		=> $this->pi_getPageLink($this->conf['whereToPoint'], '', array('tx_zafanunturi_pi2[id_judet]' => '41' ) ),
				 )
		 );
		
		return $this->subTemplate;
	}
}



if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/zaf_harta/pi1/class.tx_zafharta_pi1.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/zaf_harta/pi1/class.tx_zafharta_pi1.php']);
}

?>