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
 * Plugin 'Confirmare anunt' for the 'zaf_anunturi' extension.
 *
 * @author	Cristi <bau_baus2002@yahoo.com>
 * @package	TYPO3
 * @subpackage	tx_zafanunturi
 */
class tx_zafanunturi_pi6 extends tslib_pibase {
	public $prefixId      = 'tx_zafanunturi_pi6';		// Same as class name
	public $scriptRelPath = 'pi6/class.tx_zafanunturi_pi6.php';	// Path to this script relative to the extension dir.
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
	
		$content = $this->processData();
		
		return $this->pi_wrapInBaseClass($content);
	}
	
	function processData() {
		//debug($_GET['a'],'a var');
		//aproba anunt
		if(isset($_GET['a']) && trim($_GET['a'] !='')) {
			$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_zafanunturi_anunuri', "md5hash='".$_GET['a']."'", '', '', '1');
			$this->rows = $rows;
		//	debug($rows,'rows');
			
			if(is_array($rows) && count($rows)>0 && $rows[0]['confirmed_once'] == 1 && $rows[0]['hidden'] == 1){
				//debug('dsdasd');
				$updateArray = array(	'deleted' 		 => '0',
										'hidden'		 => '0',
									);

				$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_zafanunturi_anunuri', "md5hash='".$_GET['a']."'", $updateArray);
				$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
				
				$this->sendApprovalEmail('app');
				return $this->pi_getLL('success_confirm');
			}			
		}
		
		//sterge anunt
		if(isset($_GET['d']) && trim($_GET['d'] !='')) {
			$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_zafanunturi_anunuri', "md5hash='".$_GET['d']."'", '', '', '1');
			$this->rows = $rows;
		//	debug($rows,'rows');
			
			if(is_array($rows) && count($rows)>0 && $rows[0]['confirmed_once'] == 1 && $rows[0]['hidden'] == 1 && $rows[0]['deleted'] == 0 ){
				//debug('dsdasd');
				$updateArray = array(	'deleted' 		 => '1',
										'hidden'		 => '1',
									);

				$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_zafanunturi_anunuri', "md5hash='".$_GET['d']."'", $updateArray);
				$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
				
				$this->sendApprovalEmail('not_app');
				return $this->pi_getLL('success_delete');
			}			
		}
	}
	
	function sendApprovalEmail($var) {
		$localit = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('nume_localitate', 'tx_localitati', "id_localitate='".intval($this->rows[0]['localitate'])."'", '', '', '');
		$localitate = $localit[0]['nume_localitate'];

		$categ = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('categorie', 'tx_zafanunturi_anunuri_categorii', "uid='".intval($this->rows[0]['categorie'])."'", '', '', '');
		$categorie = $categ[0]['categorie'];
		
		if($this->rows[0]['moneda'] == 0) {
			$moneda = 'RON';
		}  
		elseif($this->rows[0]['moneda'] == 1) {
			$moneda = 'EUR';
		}
		elseif($this->rows[0]['moneda'] == 2) {
			$moneda = 'USD';
		}		
		
		$tpl = $this->cObj->fileResource($this->conf['mailToUserTemplate'] ? $this->conf['mailToUserTemplate'] : 'EXT:' . $this->extKey . '/res/mail_to_user_again.html');
		
		if($var == 'app') {
			$emailSubpart = $this->cObj->getSubpart($tpl,'###ANUNT_APROBAT###');					
		}
		else {
			$emailSubpart = $this->cObj->getSubpart($tpl,'###ANUNT_NE_APROBAT###');
		}
		
		$emailContent = $this->cObj->substituteMarkerArray($emailSubpart,
														array(	'###TITLU###' 			=> $this->rows[0]['titlu'],
																'###PRET###' 			=> $this->rows[0]['pret'].' '.$moneda ,
																'###CATEGORIE###'  		=> $categorie,
																'###LOCALITATE###' 		=> $localitate,
																'###TEXT_ANUNT###'		=> $this->rows[0]['text_anunt'],
																'###NUME###'			=> $this->rows[0]['nume'],
																'###TELEFON###'			=> $this->rows[0]['telefon'],
																'###EMAIL###'			=> $this->rows[0]['email'],
															 )
													  	   );													  	
		if($var == 'app') {
			$subject = 	$this->conf['subject_approved'];		
		}
		else {
			$subject = $this->conf['subject_not_approved'];
		}
													  	   
		$fromEmail = $this->conf['approval_email_to'];

		$sendMailObj = t3lib_div::makeInstanceClassName('t3lib_htmlmail');
		$sendMailObj = t3lib_div::makeInstance($sendMailObj);
		$sendMailObj->start();
		$sendMailObj->defaultCharset = 'utf-8';						
		$sendMailObj->useBase64();
		$sendMailObj->subject = $subject;
		$sendMailObj->from_email = $this->conf['from_email'];
		$sendMailObj->from_name = 'zaf.ro';
		$sendMailObj->replyto_email = $this->conf['from_email'];
		$sendMailObj->replyto_name = 'zaf.ro';
		$sendMailObj->dontEncodeHeader = true;
		$sendMailObj->organisation = '';
		$sendMailObj->priority = 3;
		$sendMailObj->setHeaders();		
		$sendMailObj->setHtml($sendMailObj->encodeMsg($emailContent));
				
		$sendMailObj->setRecipient($this->rows[0]['email']);
		$sendMailObj->setContent();
		$sendMailObj->sendtheMail();		
	}
	
}



if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/zaf_anunturi/pi6/class.tx_zafanunturi_pi6.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/zaf_anunturi/pi6/class.tx_zafanunturi_pi6.php']);
}

?>