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
class tx_zafanunturi_pi7 extends tslib_pibase {
	public $prefixId      = 'tx_zafanunturi_pi7';		// Same as class name
	public $scriptRelPath = 'pi7/class.tx_zafanunturi_pi7.php';	// Path to this script relative to the extension dir.
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
		
		$this->init();

		$content = $this->getData();
				
	
		return $this->pi_wrapInBaseClass($content);
	}
	
	function init() {
		$this->conf['templateFile'] =  $this->conf['templateFile'] ? $this->conf['templateFile'] : 'EXT:' . $this->extKey . '/res/template_pi7.html';
		$this->templateFile = $this->cObj->fileResource($this->conf['templateFile']);
		$this->subTemplate = $this->cObj->getSubpart($this->templateFile,"###ROW_ANUNT###");		

		$startingPoint = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'startingPoint');
		$this->conf['startingPoint'] = $startingPoint ? $startingPoint : $GLOBALS['TSFE']->id;				
		$this->conf['singlePID'] = $this->conf['singlePID'] ? $this->conf['singlePID'] : $GLOBALS['TSFE']->id;
	}
	
	function getData() {
		$selectedRows = 'uid, crdate, titlu, tip_anunt, judet, localitate, pret, moneda, valabilitate, text_anunt, categorie, poza';
		$where = '1 ';
		//$sorting = 'crdate DESC';
		$limit = '6';

		if(isset($this->piVars['id_judet']) && intval($this->piVars['id_judet']) > 0) {
			$where = "judet='".intval($this->piVars['id_judet'])."'";
		}
		
		$where .= " AND deleted='0' AND hidden='0' AND poza!=''";
		$sorting = 'crdate DESC';

			
		
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($selectedRows, 'tx_zafanunturi_anunuri', $where, '', $sorting, $limit);
		//$rows2 = $GLOBALS['TYPO3_DB']->SELECTquery($selectedRows, 'tx_zafanunturi_anunuri', $where, '', $sorting, $limit);
		//debug($rows2,'query');
		$counter = 1;
		
		if(count($rows) < 1)
		{	
		
			return $accum;
		}
		
		foreach ($rows as $row) {
//debug($row,'row');			
			
			$expl = explode(',', $row['poza']);
			if(is_array($expl) && count($expl) > 0) {
				$poza = $expl[0];
			}
			else 	
				$poza = $row['poza'];
			
			
				$imageConf['file'] = 'uploads/tx_zafanunturi/'.$poza;				
				$imageConf['file.']['width'] = $this->conf['thumbWidth'].'c';
				$imageConf['file.']['height'] = $this->conf['thumbHeight'].'c';
				$imageConf['altText'] = 'Anunturi gratuite';
				$imageConf['titleText'] = 'Anunturi gratuite';
				//$thumbnailImage = $this->cObj->IMG_RESOURCE($imageConf);
				$thumbnailImage = $this->cObj->IMAGE($imageConf);
						
			
			if($counter%2==0) {
				$gray = 'gray';
			}
			else{
				$gray = '';
			}
						
			$categ = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('categorie', 'tx_zafanunturi_anunuri_categorii', "uid='".intval($row['categorie'])."'", '', '', '');
			$categorie = $categ[0]['categorie'];	
			
			$localit = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('nume_localitate', 'tx_localitati', "id_localitate='".intval($row['localitate'])."'", '', '', '');
			$localitate = $localit[0]['nume_localitate'];
			
			if($row['moneda'] == 0) {
				$moneda = 'RON';
			}  
			elseif($row['moneda'] == 1) {
				$moneda = 'EUR';
			}
			elseif($row['moneda'] == 2) {
				$moneda = 'USD';
			}
			
			$tipAnunt =  array('0' => '-- Alege --',
					    '1' => 'Ofertă / Vânzare',
						'2'	=> 'Cerere / Cumpărare',
						'3' => 'Închirieri',
						'4' => 'Schimburi',
						'5' => 'Nespecificat',		
						);
			
			
			$detailURL = $this->pi_getPageLink($this->conf['singlePID'], '', array('tx_zafanunturi_pi3[id_anunt]' => $row['uid'] ) );
			
			$ziuaPostarii = date('j',$row['crdate']);
			$lunaPOstarii = date('n',$row['crdate']);
			$anulPOstarii = date('Y',$row['crdate']);			
			$ziuaDeAzi = date('j',time());
			$lunaDeAzi = date('n',time());
			$anulDeAzi = date('Y',time());
			
			if($ziuaDeAzi == $ziuaPostarii && $lunaDeAzi == $lunaPOstarii && $anulDeAzi == $anulPOstarii) {
				$data = 'Azi';
			}
			else
			{
				$data = date('j-n-Y',$row['crdate']);
			}
			
			$accum .= $this->cObj->substituteMarkerArray($this->subTemplate, 
																array('###DATA###'	 		=> $data,
																	  '###ORA###'	 		=> date('G:i',$row['crdate']),
																	  '###POZA###'	 		=> $thumbnailImage,
																	  '###TITLU###'	 		=> $row['titlu'],
																	  '###PRET###'	 		=> $row['pret'] .' '. $moneda,
																	  '###CATEGORIE###'		=> $categorie,
																	  '###LOCALITATE###' 	=> $localitate,
																	  '###GRAY###'			=> $gray,
																	  '###LINK_DETALII###'	=> $detailURL,
  																	  '###TIP_ANUNT###'		=> $tipAnunt[$row['tip_anunt']],
														 	  		 )
		 	   												   );
		 	$counter++;	   												  
		}

		
		$temp  = $this->cObj->substituteSubpart($this->templateFile, '###ROW_ANUNT###', $accum);
		$temp  = $this->cObj->substituteSubpart($temp, '###ORDER_BOX###', $this->orderTemplate);
		$temp  = $this->cObj->substituteSubpart($temp, '###FORMULAR_CAUTARE###', $this->formCautare);
						
		
		
		return $temp;
	}
	
}



if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/zaf_anunturi/pi7/class.tx_zafanunturi_pi7.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/zaf_anunturi/pi7/class.tx_zafanunturi_pi7.php']);
}

?>