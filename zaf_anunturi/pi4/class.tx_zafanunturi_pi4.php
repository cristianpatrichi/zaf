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
 * Plugin 'Lista anunturi' for the 'zaf_anunturi' extension.
 *
 * @author	Cristi <bau_baus2002@yahoo.com>
 * @package	TYPO3
 * @subpackage	tx_zafanunturi
 */
class tx_zafanunturi_pi4 extends tslib_pibase {
	public $prefixId      = 'tx_zafanunturi_pi4';		// Same as class name
	public $scriptRelPath = 'pi4/class.tx_zafanunturi_pi4.php';	// Path to this script relative to the extension dir.
	public $extKey        = 'zaf_anunturi';	// The extension key.
	
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
		
		$this->init();
		$content = $this->renderForm();
				
	
		return $this->pi_wrapInBaseClass($content);
	}
	
	function init() {
		$this->conf['templateFile'] =  $this->conf['templateFile'] ? $this->conf['templateFile'] : 'EXT:' . $this->extKey . '/res/template_pi4.html';
		$this->templateFile = $this->cObj->fileResource($this->conf['templateFile']);
		$this->subTemplate = $this->cObj->getSubpart($this->templateFile,"###FORMULAR_CAUTARE###");
//debug($this->subTemplate,'template');		

		$startingPoint = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'whereToPoint');
		$this->conf['whereToPoint'] = $startingPoint ? $startingPoint : $GLOBALS['TSFE']->id;
	}
	
	function renderForm() {
		$thisPageLink = $this->pi_getPageLink($this->conf['whereToPoint'],'',array());
		
		$judeteList = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,judet', 'tx_judet','','', 'judet ASC');
		//$judeteList2 = $GLOBALS['TYPO3_DB']->SELECTquery('uid,judet', 'tx_judet','','', 'judet ASC');
		//debug($judeteList2,'query');
		$htmlJudete = '<option value="0">Toată România</option>' . "\n";
		while ($j = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($judeteList)) {
			if($this->piVars['judet'] == $j['uid'])
				$sel = 'selected="selected"';
			else 
				$sel = '';
			
			$htmlJudete .= '<option value="'.$j['uid'].'" '.$sel.'>';
			$htmlJudete .= $j['judet'];
			$htmlJudete .= '</option>' . "\n";
		}	
		$this->subTemplate = $this->cObj->substituteMarkerArray($this->subTemplate, 
																array('###ACTION###'	=> $thisPageLink,
																	  '###JUDETE###'	=> $htmlJudete,
																	  '###ID###'		=> $this->conf['whereToPoint'],
																																		  
														 	  		 )
		 	   												   );
		
		
		return $this->subTemplate;
	}
	
}



if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/zaf_anunturi/pi4/class.tx_zafanunturi_pi4.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/zaf_anunturi/pi4/class.tx_zafanunturi_pi4.php']);
}

?>