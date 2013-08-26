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
class tx_zafanunturi_pi3 extends tslib_pibase {
	public $prefixId      = 'tx_zafanunturi_pi3';		// Same as class name
	public $scriptRelPath = 'pi3/class.tx_zafanunturi_pi3.php';	// Path to this script relative to the extension dir.
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
		
		if(isset($_POST['submited']) && $_POST['submited'] == 'yes') {
			$this->submitTheMailForm();
		}
		
		$content  = $this->getData();		
	
		return $this->pi_wrapInBaseClass($content);
	}
	
	function init() {
		$this->conf['templateFile'] =  $this->conf['templateFile'] ? $this->conf['templateFile'] : 'EXT:' . $this->extKey . '/res/template_pi3.html';
		$this->templateFile = $this->cObj->fileResource($this->conf['templateFile']);
		$this->subTemplate = $this->cObj->getSubpart($this->templateFile,"###DETALII_ANUNT###");
		$this->formMail = $this->cObj->getSubpart($this->templateFile,"###FORMULAR_DE_MAIL###");
//debug($this->subTemplate,'template');		

		$startingPoint = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'startingPoint');
		$this->conf['startingPoint'] = $startingPoint ? $startingPoint : $GLOBALS['TSFE']->id;
	}
	
	function getData() {
		$selectedRows = '*';
		$where  = "uid='".intval($this->piVars['id_anunt'])."'";
		$where .= " AND deleted=0 AND hidden=0";
		$sorting = 'crdate DESC';
		$limit = '';	

		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($selectedRows, 'tx_zafanunturi_anunuri', $where, '', $sorting, '1');
	//	$rows2 = $GLOBALS['TYPO3_DB']->SELECTquery($selectedRows, 'tx_zafanunturi_anunuri', $where, '', $sorting, '1');
	//	debug($rows2,'query');
		$counter = 1;
		
		$imagesFinal = '';
		
		foreach ($rows as $row) {
			//debug($row,'row');						
			$expl = explode(',', $row['poza']);
			if(is_array($expl) && count($expl) > 0) {
				//poza mare
				$imageConf['file'] = 'uploads/tx_zafanunturi/'.$expl[0];
				$imageConf['file.']['maxH'] = $this->conf['imgWidth'];
				$imageConf['file.']['maxW'] = $this->conf['imgHeight'];
				$imageConf['altText'] = 'Anunturi gratuite';
				$imageConf['titleText'] = 'Anunturi gratuite';
				$pozaMare = $this->cObj->IMG_RESOURCE($imageConf);
				//poze thumbs
				foreach ($expl as $e) {
					$imageConf['file'] = 'uploads/tx_zafanunturi/'.$e;				
					$imageConf['file.']['maxH'] = $this->conf['thumbWidth'];
					$imageConf['file.']['maxW'] = $this->conf['thumbHeight'];
					$imageConf['altText'] = 'Anunturi gratuite';
					$imageConf['titleText'] = 'Anunturi gratuite';
					$thumbnailImage = $this->cObj->IMG_RESOURCE($imageConf);
					
					$imageConf['file'] = 'uploads/tx_zafanunturi/'.$e;				
					$imageConf['file.']['maxH'] = $this->conf['imgWidth'];
					$imageConf['file.']['maxW'] = $this->conf['imgHeight'];
					$imageConf['altText'] = 'Anunturi gratuite';
					$imageConf['titleText'] = 'Anunturi gratuite';
					$bigIMage = $this->cObj->IMG_RESOURCE($imageConf);
				
					if($bigIMage!='' && $thumbnailImage!='') {
						$imagesFinal .= '<img onclick="document.getElementById(\'poza_mare\').src=\''.$bigIMage.'\'" src="'.$thumbnailImage.'" alt="Anunturi gratuite" />';
					}
					else {
						$imagesFinal = '&nbsp;';
					}	
				}
			}
			else { 	
				//$poza = $row['poza'];
				$imageConf['file'] = 'uploads/tx_zafanunturi/'.$row['poza'];				
				$imageConf['file.']['maxH'] = $this->conf['thumbWidth'];
				$imageConf['file.']['maxW'] = $this->conf['thumbHeight'];
				$imageConf['altText'] = 'Anunturi gratuite';
				$imageConf['titleText'] = 'Anunturi gratuite';
				$thumbnailImage = $this->cObj->IMG_RESOURCE($imageConf);
				
				$imageConf['file'] = 'uploads/tx_zafanunturi/'.$row['poza'];				
				$imageConf['file.']['maxH'] = $this->conf['imgWidth'];
				$imageConf['file.']['maxW'] = $this->conf['imgHeight'];
				$imageConf['altText'] = 'Anunturi gratuite';
				$imageConf['titleText'] = 'Anunturi gratuite';
				$bigIMage = $this->cObj->IMG_RESOURCE($imageConf);
				$pozaMare = $this->cObj->IMG_RESOURCE($imageConf);
				
				if($bigIMage!='' && $thumbnailImage!='') {
					$imagesFinal .= '<img onclick="document.getElementById(\'poza_mare\').src=\''.$bigIMage.'\'" src="'.$thumbnailImage.'" alt="Anunturi gratuite" />';
				}
				else {
					$imagesFinal = '&nbsp;';
				}	
			}	
							

			$imageConf['file'] = 'uploads/tx_zafanunturi/'.$poza;
			$imageConf['file.']['maxH'] = $this->conf['thumbWidth'];
			$imageConf['file.']['maxW'] = $this->conf['thumbHeight'];
			$imageConf['altText'] = 'Anunturi gratuite';
			$imageConf['titleText'] = 'Anunturi gratuite';
			$thumbnailImage = $this->cObj->IMG_RESOURCE($imageConf);
			//$thumbnailImage = $this->cObj->IMAGE($imageConf);
			
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
			
			if(trim($pozaMare) == '') {
				$pozaMare = '&nbsp;';
			}
			
			$phoneImg = $GLOBALS['TSFE']->baseUrl . 'typo3conf/text2img.php?txt=' . $row['md5hash'];
			//debug($phoneImg,'iimg');
			
			$accum .= $this->cObj->substituteMarkerArray($this->subTemplate, 
																array('###DATA###'	 		=> date('j-n-Y',$row['crdate']),
																	  '###ORA###'	 		=> date('G:i:s',$row['crdate']),
																	  '###POZE_MICI###'	 	=> $imagesFinal,
																	  '###POZA_MARE###'		=> '<img id="poza_mare" src="'.$pozaMare.'" alt="Anunturi gratuite" title="Anunturi gratuite" />',	
																	  '###TITLU###'	 		=> $row['titlu'],
																	  '###PRET###'	 		=> number_format($row['pret'],0,'','.') .' '. $moneda,
																	  '###CATEGORIE###'		=> $categorie,
																	  '###LOCALITATE###' 	=> $localitate,
																	  '###GRAY###'			=> $gray,
																	  '###CONTENT###'		=> $row['text_anunt'],
																	  '###NUME###'			=> $row['nume'],
																	  '###TELEFON###'		=> $phoneImg,	
														 	  		 )
		 	   												   );
		 	   												   
			$this->formMail = $this->cObj->substituteMarkerArray($this->formMail, 
																array(
																	  '###TELEFON###'		=> $phoneImg,	
														 	  		 )
		 	   												   );
		 	   												   
		 	$counter++;	   												  
		}
	//	debug($rows[0]['contact_pe_mail'],'contact pe mail');
//debug($rows,'rows');

		
		if($rows[0]['contact_pe_mail'] == 1) {
			$captchaImg = t3lib_extMgm::isLoaded('captcha') ? '<img src="'.t3lib_extMgm::siteRelPath('captcha').'captcha/captcha.php" alt="Anunturi gratuite" />' : '';
			
			if($_POST['submited'] == 'yes') {			
				$jsScroll = '$(document).ready(function() {	
			  					
				  					$("html, body").animate({
			            				scrollTop: $(".trimiteMail").offset().top
			        				}, 1000);
			  				    
							});
				';
			}
			else {
				$jsScroll = '';
			}
			
			
			$this->formMail = $this->cObj->substituteMarkerArray($this->formMail, 
																array(
																	  '###MAIL_FORM_ACTION###'	=> $this->cObj->currentPageUrl(),	
																	  '###NUME_MAIL###'			=> $_POST['nume'] ? htmlentities($_POST['nume']) : '',
																	  '###MAIL_MAIL###'			=> $_POST['mail'] ? htmlentities($_POST['mail']) : '',
																	  '###TELEFON_MAIL###'		=> $_POST['telefon'] ? htmlentities($_POST['telefon']) : '',
																	  '###MESAJ###'				=> $_POST['mesaj'] ? $_POST['mesaj'] : '',
																	  '###COD_CAPTCHA###'		=> $_POST['captcha'] ? htmlentities($_POST['captcha']) : '',
																	  '###DISPLAYBLOCK###'   	=> $_POST['submited'] == 'yes' ? 'style="display:block;"' : '',
																	  '###CAPTCHA###'			=> $captchaImg,
																	  '###ERRORS###'			=> $this->error,
																	  '###JS_SCROLL###'			=> $jsScroll,
														 	  		 )
		 	   												   );
	//debug( $this->piVars['submited'],'submited val');					
			$accum .= $this->formMail;
		}
		else {		
			$accum .= '
			<div class="trimiteMailSau">
			<div class="sauC">Contactează la</div>
			<div class="laTelefonul"><span></span><img src="'.$phoneImg.'" /></div>
			<div class="clear"></div>
			</div>
			';
		}
		return $accum;
	}
	
	function submitTheMailForm(){		
		if($this->validateMailForm() == false) {
			return false;
		}
		
		//check if user have checked he can accept emails
		$where  = "uid='".intval($this->piVars['id_anunt'])."'";
		$anunt = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('contact_pe_mail', 'tx_zafanunturi_anunuri', $where, '', '', '');
		$contactPeMail = $anunt[0]['contact_pe_mail'];
		
		if($contactPeMail == 1){ 		
			$this->sendApprovalEmail();
			$this->error = '<b>'. $this->pi_getLL('mail_sent') . '</b>';
			
			$_POST['nume'] = '';
			$_POST['telefon'] = '';
			$_POST['mail'] = '';
			$_POST['mesaj'] = '';
			$_POST['captcha'] = '';
		}	
		
	}
	
	function validateMailForm() {
		$valid = true;
		
		if(!isset($_POST['nume']) || trim($_POST['nume']) == '') {
			$valid = false;
			$this->error .= $this->pi_getLL('no_nume').'<br />';
		}
		
		if(!isset($_POST['mail']) || trim($_POST['mail']) == '') {
			$valid = false;
			$this->error .= $this->pi_getLL('no_mail').'<br />';
		}
		elseif (!preg_match("/^[\w.-]+@[\w.-]+\.[A-Za-z]{2,6}$/", $_POST['mail'])) {     
    		$valid = false;
			$this->error .= $this->pi_getLL('wrong_email').'<br />';
		}
		
		if(!isset($_POST['mesaj']) || trim($_POST['mesaj']) == '') {
			$valid = false;
			$this->error .= $this->pi_getLL('no_mesaj').'<br />';
		}
		
		if(!isset($_POST['captcha']) || trim($_POST['captcha']) == '') {
			$valid = false;
			$this->error .= $this->pi_getLL('no_captcha').'<br />';
		}
		elseif (t3lib_extMgm::isLoaded('captcha'))	
		{
			session_start();
			$captchaStr = $_SESSION['tx_captcha_string'];
			$_SESSION['tx_captcha_string'] = '';
		} 
		else 
		{
			$captchaStr = -1;
		}		
		
		if ($captchaStr != -1 && ($captchaStr && $_POST['captcha']!=$captchaStr))
		{
			$valid = false;
			$this->error .= $this->pi_getLL('wrong_captcha').'<br />';
		}
		
		return $valid;
	}
	
	function sendApprovalEmail() {		
		$where  = "uid='".intval($this->piVars['id_anunt'])."'";
		$anunt = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('titlu,email', 'tx_zafanunturi_anunuri', $where, '', '', '');
		$titluAnunt = $anunt[0]['titlu'];
		$email 	    = $anunt[0]['email'];
	
		
		
		$tpl = $this->cObj->fileResource($this->conf['emailTemplateFile'] ? $this->conf['emailTemplateFile'] : 'EXT:' . $this->extKey . '/res/mail_din_formular.html');
		$emailSubpart = $this->cObj->getSubpart($tpl,'###FROM_EMAIL_TEMPLATE###');

	

		
		$emailContent = $this->cObj->substituteMarkerArray($emailSubpart,
														array(	'###TITLU_ANUNT###'		=> $titluAnunt,
																'###NUME###'			=> $_POST['nume'],
																'###TELEFON###'			=> $_POST['telefon'],
																'###EMAIL###'			=> $_POST['mail'],
																'###MESAJ###'			=> $_POST['mesaj'],
															 )
													  	   );													  	

//debug($emailContent,'continut mail'); 
//return false;													  	   

													  	   
		$fromEmail = $this->conf['approval_email_to'];

		$sendMailObj = t3lib_div::makeInstanceClassName('t3lib_htmlmail');
		$sendMailObj = t3lib_div::makeInstance($sendMailObj);
		$sendMailObj->start();
		$sendMailObj->defaultCharset = 'utf-8';						
		$sendMailObj->useBase64();
		$sendMailObj->subject = $this->conf['subject'];
		$sendMailObj->from_email = $this->conf['from_email'];
		$sendMailObj->from_name = 'zaf.ro';
		$sendMailObj->replyto_email = $_POST['mail'];
		$sendMailObj->replyto_name = $_POST['nume'];
		$sendMailObj->dontEncodeHeader = true;
		$sendMailObj->organisation = '';
		$sendMailObj->priority = 3;
		$sendMailObj->setHeaders();		
		$sendMailObj->setHtml($sendMailObj->encodeMsg($emailContent));
				
		$sendMailObj->setRecipient($email);
		$sendMailObj->setContent();
		$sendMailObj->sendtheMail();		
	}
}



if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/zaf_anunturi/pi3/class.tx_zafanunturi_pi3.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/zaf_anunturi/pi3/class.tx_zafanunturi_pi3.php']);
}

?>