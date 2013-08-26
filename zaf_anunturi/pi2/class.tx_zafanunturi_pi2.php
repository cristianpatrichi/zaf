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
class tx_zafanunturi_pi2 extends tslib_pibase {
	public $prefixId      = 'tx_zafanunturi_pi2';		// Same as class name
	public $scriptRelPath = 'pi2/class.tx_zafanunturi_pi2.php';	// Path to this script relative to the extension dir.
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
		
		//ajax autocomplete search
		if($this->isAjax()) {
			$this->ajaxSearch();
		}	
		
//debug($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'metoda_afisare'),'flex method');		
		//$content = $this->orderTemplate;
		$content = $this->getData();
				
	
		return $this->pi_wrapInBaseClass($content);
	}
	
	function isAjax() {
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			return true;	
		}
		return false;
	}
	
	function init() {
		$this->conf['templateFile'] =  $this->conf['templateFile'] ? $this->conf['templateFile'] : 'EXT:' . $this->extKey . '/res/template_pi2.html';
		$this->templateFile = $this->cObj->fileResource($this->conf['templateFile']);
		$this->subTemplate = $this->cObj->getSubpart($this->templateFile,"###ROW_ANUNT###");
		$this->orderTemplate = $this->cObj->getSubpart($this->templateFile,"###ORDER_BOX###");
		$this->formCautare = $this->cObj->getSubpart($this->templateFile,"###FORMULAR_CAUTARE###");
		$this->theTitle = $this->cObj->getSubpart($this->templateFile,"###THE_TITLE###");
//debug($this->subTemplate,'template');		

		$startingPoint = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'startingPoint');
		$this->conf['startingPoint'] = $startingPoint ? $startingPoint : $GLOBALS['TSFE']->id;				
		$this->conf['singlePID'] = $this->conf['singlePID'] ? $this->conf['singlePID'] : $GLOBALS['TSFE']->id;
	}
	
	function ajaxSearch() {
		$selectedRows = 'titlu';
		$where = '1 AND deleted=0 AND hidden=0 ';
		$limit = '10';
		$sorting = 'titlu ASC';
		
		if(isset($this->piVars['id_judet']) && intval($this->piVars['id_judet']) > 0) {
			$where = "judet='".intval($this->piVars['id_judet'])."'";
		}
		
		if(isset($this->piVars['categorie']) && $this->piVars['categorie'] != '' && $this->piVars['categorie'] != 0) {
			$where .= " AND categorie='".intval($this->piVars['categorie'])."'";
		}
		
		if (isset($_GET['term']) && trim($_GET['term']) != '') {
				$this->stopwords = array(" si ", " și ", " de ", " pe ", " un ", " o ", " la ", " de ","pentru ");//you need to extend this big time.
				$this->symbols = array('/','\\','\'','"',',','.','<','>','?',';',':','[',']','{','}','|','=','+','-','_',')','(','*','&','^','%','$','#','@','!','~','`'	);//this will remove punctuation
				
				$cleanString = $this->parseString($_GET['term']);				
				$explString = explode(' ', trim($cleanString));

				/*
				if(is_array($explString) && count($explString) > 1) {
					$where .= "AND(";
					
					foreach ($explString as $k=>$ex) {
						if($k < count($explString)-1) {
							$or = 'OR ';
						}
						else {
							$or = '';
						}
						
						if($ex != ' ') 
							$where .= "titlu LIKE '%".trim($ex)."%' ".$or;
					}	
					$where .= " )";
				}else {
				*/
					$srcWord = $GLOBALS['TYPO3_DB']->quoteStr($cleanString, 'tx_zafanunturi_anunuri');			
					$where .= " AND titlu LIKE '%".$srcWord."%'";
					//$where .= "AND MATCH (titlu) AGAINST ('".$srcWord."')";
				//}
				
				$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($selectedRows, 'tx_zafanunturi_anunuri', $where, '', $sorting, $limit);
	//debug($where,'where');						
				//print_r($rows); exit();
				header('Content-type: text/json');
				header('Content-type: application/json');
				
				//echo ($where);
				//print_r($rows);
				
				foreach ($rows as $row) {
					$res[] = $row['titlu'];
				}
								
				$ress = json_encode($res);
				echo $ress; 
				exit();
			}
	}
	
	function getData() {
		$selectedRows = 'uid, crdate, titlu, tip_anunt, judet, localitate, pret, moneda, valabilitate, text_anunt, categorie, poza';
		$where = '1 ';
		//$sorting = 'crdate DESC';
		$limit = '';
//debug($this->piVars['id_judet'],'pi var');	

		if(isset($this->piVars['id_judet']) && intval($this->piVars['id_judet']) > 0) {
			$where = "judet='".intval($this->piVars['id_judet'])."'";
		}
		
//debug($this->piVars['src_word'],'src word');		
		if (isset($this->piVars['src_word']) && trim($this->piVars['src_word']) != '') {
			$this->stopwords = array(" si ", " și ", " de ", " pe ", " un ", " o ", " la ", " de ", "pentru ");//you need to extend this big time.
			$this->symbols = array('/','\\','\'','"',',','.','<','>','?',';',':','[',']','{','}','|','=','+','-','_',')','(','*','&','^','%','$','#','@','!','~','`'	);//this will remove punctuation
			
			$cleanString = $this->parseString($this->piVars['src_word']);
//debug($cleanString,'clean string');			

			$explString = explode(' ', trim($cleanString));
//debug($explString,'implode');
/*
			if(is_array($explString) && count($explString) > 1) {
				$where .= " AND(";
				
				foreach ($explString as $k=>$ex) {
					if($k < count($explString)-1) {
						$or = 'OR ';
					}
					else {
						$or = '';
					}
					
					if($ex != ' ') 
						$where .= "titlu LIKE '%".trim($ex)."%' ".$or;
				}	
				$where .= " )";
			}
			
			else {
	*/	
				$srcWord = $GLOBALS['TYPO3_DB']->quoteStr($cleanString, 'tx_zafanunturi_anunuri');			
				$where .= " AND titlu LIKE '%".$srcWord."%'";
				//$where .= "AND MATCH (titlu) AGAINST ('".$srcWord."')";
		//	}
			
//debug($where,'where');						
		}
		
		if(isset($this->piVars['categorie']) && $this->piVars['categorie'] != '' && $this->piVars['categorie'] != 0) {
			$where .= " AND categorie='".intval($this->piVars['categorie'])."'";
		}
		
		//oferte
		if($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'metoda_afisare') == 2) {
			$where .= " AND tip_anunt='1'";	
		}
		
		//cereri
		if($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'metoda_afisare') == 3) {
			$where .= " AND tip_anunt='2'";
		}
		
		$where .= " AND deleted='0' AND hidden='0'";
		
		if(isset($this->piVars['ordonare']) && $this->piVars['ordonare'] == 'cele-mai-noi') {
			$sorting = 'crdate DESC';
		}
		elseif(isset($this->piVars['ordonare']) && $this->piVars['ordonare'] == 'pret-asc') {
			$sorting = "CAST(`pret` AS SIGNED) ASC";
		}
		elseif(isset($this->piVars['ordonare']) && $this->piVars['ordonare'] == 'pret-desc') {
			$sorting = "CAST(`pret` AS SIGNED) DESC";
		}
		else {
			$sorting = 'crdate DESC';
		}
		
		
		//pagination stuff
		$pageSize = $this->conf['limit'];
		$page = max(1, intval($this->piVars['page']));
		$number = ($page - 1) * $pageSize;
		$limit = $number . ', ' . $pageSize;
		
		
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($selectedRows, 'tx_zafanunturi_anunuri', $where, '', $sorting, $limit);
		$rows2 = $GLOBALS['TYPO3_DB']->SELECTquery($selectedRows, 'tx_zafanunturi_anunuri', $where, '', $sorting, $limit);
		//debug($rows2,'query');
		//$counter = 1;
		
		if(count($rows) < 1)
		{
			$optionsOrdonare = array('cele-mai-noi' => 'Cele mai noi',
								 'pret-asc'		=> 'Preț crescător',
								 'pret-desc'	=> 'Preț descrescător',
							    );

							    
			foreach($optionsOrdonare as $k=>$v) {			
				if($this->piVars['ordonare'] == $k)
				{
					$sel = 'selected="selected"';
				}
				else {
					$sel = '';
				}
				
				$optVal .= '<option value="'.$k.'" '.$sel.'>';
				$optVal .= $v;
				$optVal .= '</option>';
			}							    
			
			$this->orderTemplate= $this->cObj->substituteMarkerArray($this->orderTemplate, 
														array('###ACTION###'     		=> $this->pi_getPageLink($GLOBALS['TSFE']->id,'',array()),	
															  '###SRC_WORD###'	 		=> $this->piVars['src_word'],													  
															  '###ID_JUDET###'	 		=> $this->piVars['id_judet'],
															  '###PAGINA###' 		 	=> $this->piVars['page'],
															  '###OPTIONS_ORDONARE###' 	=> $optVal,	
														      )
	 	   											    );		
	
			$judeteList = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,judet', 'tx_judet','','', 'judet ASC');
			//$judeteList2 = $GLOBALS['TYPO3_DB']->SELECTquery('uid,judet', 'tx_judet','','', 'judet ASC');
			//debug($judeteList2,'query');
			$htmlJudete = '<option value="0">Toată România</option>' . "\n";
			while ($j = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($judeteList)) {
				if($this->piVars['id_judet'] == $j['uid'])
					$sel = 'selected="selected"';
				else 
					$sel = '';
				
				$htmlJudete .= '<option value="'.$j['uid'].'" '.$sel.'>';
				$htmlJudete .= $j['judet'];
				$htmlJudete .= '</option>' . "\n";
			}
			
			//get list of categories
			$categorii = '<option value="0">--- Alege ---</option>';
			$categsList = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,categ_label', 'tx_zafanunturi_anunuri_categorii_label','1','', 'uid ASC');
			while ($j = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($categsList)) {
				$categorii .= '<optgroup label="--- '.$j['categ_label'].' ---">';
				$categ2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,categorie', 'tx_zafanunturi_anunuri_categorii',"pid='".intval($j[uid])."'",'', 'uid ASC');
				
				while ($k = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($categ2)) {
					if($this->piVars['categorie'] == $k['uid'])
						$sel = 'selected="selected"';
					else 
						$sel = '';
					
					$categorii .= '<option value="'.$k['uid'].'" '.$sel.'>'.$k['categorie'].'</option>';
				}
				$categorii .= '</optgroup>';
			}
	 	   											    
	 	   											    
			$this->formCautare = $this->cObj->substituteMarkerArray($this->formCautare, 
														array('###ACTION###'     		=> $this->pi_getPageLink($GLOBALS['TSFE']->id,'',array()),	
															  '###SRC_WORD###'			=> $this->piVars['src_word'],													  
															  '###ID_JUDET###'	 		=> $this->piVars['id_judet'],
															  '###PAGINA###' 	 		=> $this->piVars['page'],
															  '###ORDONARE###' 	 		=> $this->piVars['ordonare'],
															  '###OPTIONS_ORDONARE###' 	=> $optVal,
															  '###JUDETE###'			=> $htmlJudete,
															  '###CATEGORII###'			=> $categorii,
																	
														      )
	 	   											    ); 	
			
			
			$this->orderTemplate  = $this->cObj->substituteSubpart($this->orderTemplate, '###ORDER_BOX###', $this->orderTemplate);
			$this->formCautare  = $this->cObj->substituteSubpart($this->formCautare, '###FORMULAR_CAUTARE###', $this->formCautare);	 	   											    
			
			$accum  = $this->formCautare . $this->orderTemplate;			
			$accum .= $this->pi_getLL('no_results_found');	
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
			
			if($poza!='') {
				$imageConf['file'] = 'uploads/tx_zafanunturi/'.$poza;				
				$imageConf['file.']['width'] = $this->conf['thumbWidth'] . 'c';
				$imageConf['file.']['height'] = $this->conf['thumbHeight'] . 'c';
				$imageConf['altText'] = 'Anunturi gratuite';
				$imageConf['titleText'] = 'Anunturi gratuite';
				//$thumbnailImage = $this->cObj->IMG_RESOURCE($imageConf);
				$thumbnailImage = $this->cObj->IMAGE($imageConf);
			}
			else {
				$imageConf['file'] = 'typo3conf/ext/zaf_anunturi/res/img/no_photo.jpg';	
				$imageConf['file.']['width'] = $this->conf['thumbWidth'] . 'c';
				$imageConf['file.']['height'] = $this->conf['thumbHeight']. 'c';
				$imageConf['altText'] = 'Anunturi gratuite';
				$imageConf['titleText'] = 'Anunturi gratuite';
				$thumbnailImage = $this->cObj->IMAGE($imageConf);
			}	
			
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
																	  '###ORA###'	 		=> date('G:i:s',$row['crdate']),
																	  '###POZA###'	 		=> $thumbnailImage,
																	  '###TITLU###'	 		=> $row['titlu'],
																	  '###PRET###'	 		=> number_format($row['pret'],0,'','.') .' '. $moneda,
																	  '###CATEGORIE###'		=> $categorie,
																	  '###LOCALITATE###' 	=> $localitate,
																	  '###GRAY###'			=> $gray,
																	  '###LINK_DETALII###'	=> $detailURL,
  																	  '###TIP_ANUNT###'		=> $tipAnunt[$row['tip_anunt']],
																      '###ID###'			=> $row['uid'],			
														 	  		 )
		 	   												   );
		 	$counter++;	   												  
		}

		$pageSize = $this->conf['limit'];
		$page = max(1, intval($this->piVars['page']));
		$number = ($page - 1) * $pageSize;
		$limit = $number . ', ' . $pageSize;
		
		$optionsOrdonare = array('cele-mai-noi' => 'Cele mai noi',
								 'pret-asc'		=> 'Preț crescător',
								 'pret-desc'	=> 'Preț descrescător',
							    );
							    
		foreach($optionsOrdonare as $k=>$v) {			
			if($this->piVars['ordonare'] == $k)
			{
				$sel = 'selected="selected"';
			}
			else {
				$sel = '';
			}
			
			$optVal .= '<option value="'.$k.'" '.$sel.'>';
			$optVal .= $v;
			$optVal .= '</option>';
		}							    
		
		$this->orderTemplate= $this->cObj->substituteMarkerArray($this->orderTemplate, 
													array('###ACTION###'     		=> $this->pi_getPageLink($GLOBALS['TSFE']->id,'',array()),	
														  '###SRC_WORD###'	 		=> $this->piVars['src_word'],													  
														  '###ID_JUDET###'	 		=> $this->piVars['id_judet'],
														  '###PAGINA###' 		 	=> $this->piVars['page'],
														  '###OPTIONS_ORDONARE###' 	=> $optVal,	
														  '###CATEGORIE###'			=> $this->piVars['categorie'],
													      )
 	   											    );		

		$judeteList = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,judet', 'tx_judet','','', 'judet ASC');
		//$judeteList2 = $GLOBALS['TYPO3_DB']->SELECTquery('uid,judet', 'tx_judet','','', 'judet ASC');
		//debug($judeteList2,'query');
		$htmlJudete = '<option value="0">Toată România</option>' . "\n";
		while ($j = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($judeteList)) {
			if($this->piVars['id_judet'] == $j['uid'])
				$sel = 'selected="selected"';
			else 
				$sel = '';
			
			$htmlJudete .= '<option value="'.$j['uid'].'" '.$sel.'>';
			$htmlJudete .= $j['judet'];
			$htmlJudete .= '</option>' . "\n";
		} 	   											    

		
		//get list of categories
		$categorii = '<option value="0">--- Alege ---</option>';
		$categsList = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,categ_label', 'tx_zafanunturi_anunuri_categorii_label','1','', 'uid ASC');
		while ($j = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($categsList)) {
			$categorii .= '<optgroup label="--- '.$j['categ_label'].' ---">';
			$categ2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,categorie', 'tx_zafanunturi_anunuri_categorii',"pid='".intval($j[uid])."'",'', 'uid ASC');
			
			while ($k = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($categ2)) {
				if($this->piVars['categorie'] == $k['uid'])
					$sel = 'selected="selected"';
				else 
					$sel = '';
				
				$categorii .= '<option value="'.$k['uid'].'" '.$sel.'>'.$k['categorie'].'</option>';
			}
			$categorii .= '</optgroup>';
		}
		
 	   											    
		$this->formCautare = $this->cObj->substituteMarkerArray($this->formCautare, 
													array('###ACTION###'     		=> $this->pi_getPageLink($GLOBALS['TSFE']->id,'',array()),	
														  '###SRC_WORD###'			=> $this->piVars['src_word'],													  
														  '###ID_JUDET###'	 		=> $this->piVars['id_judet'],
														  '###PAGINA###' 	 		=> $this->piVars['page'],
														  '###ORDONARE###' 	 		=> $this->piVars['ordonare'],
														  '###OPTIONS_ORDONARE###' 	=> $optVal,
														  '###JUDETE###'			=> $htmlJudete,
														  '###CATEGORII###'			=> $categorii,
																
													      )
 	   											    );

		if($this->piVars['id_judet'] == 0) {
			$judet = 'toata tara';
		}
		else {
			$jud = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('judet', 'tx_judet', "uid='".intval($row['judet'])."'", '', '', '');
			$judet = $jud[0]['judet'];
		}	
			 	   											    
		$this->theTitle = $this->cObj->substituteMarkerArray($this->theTitle, 
													array('###ANUNT_DIN###' => $judet,
													      )
 	   											    );	 	   											    
		
		
		$temp  = $this->cObj->substituteSubpart($this->templateFile, '###ROW_ANUNT###', $accum);
		$temp  = $this->cObj->substituteSubpart($temp, '###ORDER_BOX###', $this->orderTemplate);
		$temp  = $this->cObj->substituteSubpart($temp, '###FORMULAR_CAUTARE###', $this->formCautare);
		$temp  = $this->cObj->substituteSubpart($temp, '###THE_TITLE###', $this->theTitle);
						
		$temp2 = $this->getPager($this->cObj->getSubpart($this->templateFile, '###PAGINATION_HTML###'), $page, $pageSize, $where);
		$temp  = $this->cObj->substituteSubpart($temp, '###PAGINATION_HTML###', $temp2);
		
		return $temp;
	}
	
	function parseString($string) {
		$string = ' '.$string.' ';
		$string = $this->removeStopwords($string);
		$string = $this->removeSymbols($string);
		return $string;
	}
	
	function removeStopwords($string) {
		for ($i = 0; $i < sizeof($this->stopwords); $i++) {
			$string = str_replace($this->stopwords[$i],' ',$string);
		}
		
		//$string = str_replace('  ',' ',$string);
		return trim($string);
	}
	
	function removeSymbols($string) {
		for ($i = 0; $i < sizeof($this->symbols); $i++) {
			$string = str_replace($this->symbols[$i],' ',$string);
		}
			
		return trim($string);
	}	
	
	function getPager($template, $page, $pageSize, $where){
		$nrLinks = $this->conf['nrLinks'];
			
		list($row) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('COUNT(DISTINCT uid) AS t', 'tx_zafanunturi_anunuri', $where);		
		//debug($GLOBALS['TYPO3_DB']->SELECTquery('COUNT(DISTINCT uid) AS t', 'tx_reeatheatre_actori', $where),'query ');		
		
		$this->totalResults = $row['t'];

		if($row['t'] < $pageSize){
			return '';
		}

		$halfPages = floor($nrLinks / 2);

		if($page == 1){
			$markers['###PREV###'] = '';
			$markers['###FIRST###'] = '';
		}else{
			$markers['###PREV###'] = $this->pi_linkTP_keepPIvars($this->pi_getLL('link_prev'), array('page' => $page - 1,
																									 'src_word' => $this->piVars['src_word'],
																									 'id_judet' => $this->piVars['id_judet'],
																									 'ordonare' => $this->piVars['ordonare'],
																									 'categorie' => $this->piVars['categorie']	
																							   ), true);
			$markers['###FIRST###'] = $this->pi_linkTP_keepPIvars($this->pi_getLL('link_first'), array('page' => 1,
																									   'src_word' => $this->piVars['src_word'],
																									   'id_judet' => $this->piVars['id_judet'],
																									   'ordonare' => $this->piVars['ordonare'],
																									   'categorie' => $this->piVars['categorie']
			 																				   ), true);
		}
		$lastPage = ceil($row['t'] / $pageSize);
		if($row['t'] <= $page * $pageSize){
			$markers['###NEXT###'] = '';
			$markers['###LAST###'] = '';
		}else{
			$markers['###NEXT###'] = $this->pi_linkTP_keepPIvars($this->pi_getLL('link_next'), array('page' => $page + 1,
																					      			 'src_word' => $this->piVars['src_word'],
																									 'id_judet' => $this->piVars['id_judet'],
																									 'ordonare' => $this->piVars['ordonare'],
																									 'categorie' => $this->piVars['categorie']
																								), true);
			$markers['###LAST###'] = $this->pi_linkTP_keepPIvars($this->pi_getLL('link_last'), array('page' => $lastPage,
																					    			 'src_word' => $this->piVars['src_word'],
																									 'id_judet' => $this->piVars['id_judet'],
																									 'ordonare' => $this->piVars['ordonare'],
																									 'categorie' => $this->piVars['categorie']
																								), true);
		}

		$subTemplate = $this->cObj->getSubpart($template, '###PAGE###');
		$j = 0;
		if(($page - $halfPages > 0) && ($page + $halfPages <= $lastPage)){
			$startPage = $page - $halfPages;
			$stopPage = $page + $halfPages;
		}else{
			$startPage = 1;
			$stopPage = 3;
			if($page - $halfPages <= 0){
				$startPage = 1;
				$stopPage = $nrLinks - $startPage + 1;
				if($stopPage > $lastPage){
					$stopPage = $lastPage;
				}
			}
			if($page + $halfPages > $lastPage){
				$stopPage = $lastPage;
				$startPage = $stopPage - $nrLinks + 1;
				if($startPage < 1){
					$startPage = 1;
				}
			}
		}
		$content = '';
		for($i = $startPage; $i <= $stopPage; $i++){

			if($page == $i){
				$subMarkers['###PAGE_LINK###'] = $page;
			}else{
				$subMarkers['###PAGE_LINK###'] = $this->pi_linkTP_keepPIvars(($i), array('page' => $i,
																						 'src_word' => $this->piVars['src_word'],
																						 'id_judet' => $this->piVars['id_judet'],
																						 'ordonare' => $this->piVars['ordonare'],
																						 'categorie' => $this->piVars['categorie']
																						), true);
			}
			if($i == $stopPage){
				$subMarkers['###SEPARATOR###'] = '';
			}else{
				$subMarkers['###SEPARATOR###'] = $this->cObj->getSubpart($this->templateCode, '###SEPARATOR_HTML###');				
			}
			
			$content .= $this->cObj->substituteMarkerArray($subTemplate, $subMarkers);
		}	
		
		if($startPage != $stopPage)
			$subParts['###PAGE###'] = $content;
		else 
			$subParts['###PAGE###'] = '';
		return $this->cObj->substituteMarkerArrayCached($template, $markers, $subParts);
	}
}



if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/zaf_anunturi/pi2/class.tx_zafanunturi_pi2.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/zaf_anunturi/pi2/class.tx_zafanunturi_pi2.php']);
}

?>