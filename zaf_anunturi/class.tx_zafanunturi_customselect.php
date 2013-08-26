<?php
/***************************************************************
*  Copyright notice
*
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
 * Class/Function which manipulates the item-array for table/field tx_reeaimobiliare_customselect.
 *
 * @author	a
 */

/*
function get_judete()
			{
				$j = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,judet', 'tx_reea_judet','1');
				$return_array = Array();
				foreach($j as $judet)
				{
					$return_array[$judet['uid']] = Array($judet['judet'],$judet['uid']);
				}
				
				return $return_array;
			}
*/

	class tx_zafanunturi_customselect {
		
//		tx_reeaimobiliare_customselect->main_judet
		function main_judet(&$params,&$pObj)	{
			
			//debug('aaa');
			$params['items'] = array();
			$params['items'][] = Array($pObj->sL("LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.select"), "0");
			$jres = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,judet', 'tx_judet','1', 'judet ASC');
			while ($j = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($jres)) {
				$params['items'][] = Array($j['judet'], $j['uid']);	
			}
				// Adding an item!
//			$params["items"][]=Array($pObj->sL("Added label by PHP function|Tilføjet Dansk tekst med PHP funktion"), 999);

			// No return - the $params and $pObj variables are passed by reference, so just change content in then and it is passed back automatically...
		}
		
		
		function main_categ(&$params,&$pObj)	{
//debug('aa bb');			
			$params['items'] = array();
			$params['items'][] = Array($pObj->sL("LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.select"), "0");
			$jres = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,categorie', 'tx_zafanunturi_anunuri_categorii','1', 'categorie ASC');
			//$jres2 = $GLOBALS['TYPO3_DB']->SELECTquery('uid,categorie', 'tx_zafanunturi_anunuri_categorii','1', 'categorie ASC');
			//debug($jres2,'query');
			while ($j = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($jres)) {
				$params['items'][] = Array($j['categorie'], $j['uid']);	
			}
		}
				
		
		function main_localitate(&$params,&$pObj)
		{
			$params['items'] = array();
			$params['items'][] = Array($pObj->sL("LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.select"), "0");
			if ($params['row']['judet'])
			{
				$lres = $GLOBALS['TYPO3_DB']->exec_SELECTquery('id_localitate,nume_localitate', 'tx_localitati',"id_judet=".$params['row']['judet'],'','nume_localitate ASC');
				while ($l = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($lres)) {
					$params['items'][] = Array($l['nume_localitate'], $l['id_localitate']);	
				}
			}
			
		}
	}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/reea_imobiliare/class.tx_reeaimobiliare_customselect.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/reea_imobiliare/class.tx_reeaimobiliare_customselect.php']);
}

?>