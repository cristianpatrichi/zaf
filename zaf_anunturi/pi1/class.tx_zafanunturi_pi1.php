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
 * Plugin 'Adaugare anunt' for the 'zaf_anunturi' extension.
 *
 * @author	Cristi <bau_baus2002@yahoo.com>
 * @package	TYPO3
 * @subpackage	tx_zafanunturi
 */
class tx_zafanunturi_pi1 extends tslib_pibase {
	public $prefixId      = 'tx_zafanunturi_pi1';		// Same as class name
	public $scriptRelPath = 'pi1/class.tx_zafanunturi_pi1.php';	// Path to this script relative to the extension dir.
	public $extKey        = 'zaf_anunturi';	// The extension key.
	public $anunt = array();
	public $edit = false;
	public $userID = 0;
	
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
		
		$user = $GLOBALS['TSFE']->fe_user->user;
		$this->userID = $user['uid'];
		$this->userName = $user['first_name'];
		$this->userEmail = $user['email'];
		
		//check if is in edit page mode
		if($this->piVars['edit_id'] && $this->piVars['edit_id'] !='') {
			$this->anunt = $this->getAnunt();
		}
		
		//repopulate images list
		if($this->piVars['edit_id'] && $this->piVars['repopulate'] == '1' && $this->is_ajax()){
			echo $this->repopulateImages();			
			exit();
		}
		
		//delete image
		if($this->piVars['edit_id'] && $this->piVars['delete'] && $this->is_ajax()){
			$this->deleteImageAjax();
			echo $this->repopulateImages();			
			exit();
		}
		
		//check upload ajax request
		elseif($this->piVars['edit_id'] && $this->is_ajax()){
			$this->ajaxUpload(); 
			echo 1;
			exit();
		}
		
		//check ajax request for dropdowns
		elseif($this->is_ajax()) {
			echo $this->getLocalitati();exit();
		}		
		
		$watermarkImg = PATH_site . 'fileadmin/templates/images/watermark.png';
		
//debug($this->piVars['contactat_pe_mail'],'contact pe mail');		
		$this->init();
		$content = $this->renderForm();
		//insert in database
		if(isset($this->piVars['submited']) && $this->piVars['submited'] == 'yes') {
			$this->insertAnunt();
			$this->subTemplate = $this->cObj->substituteMarkerArray($this->subTemplate, array('###ERROR_MESSAGE###'	=> $this->error,));
			$content = $this->subTemplate;
		}
		else {
			$this->subTemplate = $this->cObj->substituteMarkerArray($this->subTemplate, array('###ERROR_MESSAGE###'	=> '',));
			$content = $this->subTemplate;
		}
//debug( $GLOBALS['TSFE']->fe_user->user['uid'],'user name');	
		return $this->pi_wrapInBaseClass($content);
	}
	
	function deleteImageAjax() {
		$img = $this->anunt['poza'];
		$img = str_replace(','.$this->piVars['delete'], '', $img);
		$img = str_replace($this->piVars['delete'].',', '', $img);
		$img = str_replace($this->piVars['delete'], '', $img);
		//$img = str_replace(',,', '', $img);

		$img = preg_replace( '/\,\,/', "", $img );
		
		$updateArray = array('poza' => $img);
//echo 'poza= '.$img;				
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_zafanunturi_anunuri', "uid='".intval($this->piVars['edit_id'])."' AND user_id='".intval($this->userID)."'", $updateArray);
		$this->anunt = $this->getAnunt();
		
		//echo $this->anunt['poza'];
	}
	
	function repopulateImages() { 
		$poze = $this->anunt;
		$poze = $poze['poza'];
		$poze = $this->getPhotos($poze);
		$photos = '';
		$counter = 1;
		
		foreach($poze as $poza){
			
			$photos .= '<li data-original-name="'.$poza['orig'].'" class="ui-state-default element_'.$counter.'">';
			$photos .= '<div class="img"><img src="'.$poza['src'].'" /></div>';
			$photos .= '<div class="lnk"><a data-image="'.$poza['orig'].'" href="#">Sterge</a></div>';
						
			$photos .= '</li>';
			$counter++;
		}
		
		return $photos;
	}
	
	function ajaxUpload() {
		$this->init();
		
		//check number of already uploaded images
		$query = $GLOBALS['TYPO3_DB']->exec_SELECTquery('poza', 'tx_zafanunturi_anunuri',"user_id='".intval($this->userID)."' AND uid='".intval($this->piVars['edit_id'])."'",'', '');
		//echo($GLOBALS['TYPO3_DB']->SELECTquery('poza', 'tx_zafanunturi_anunuri',"user_id='".intval($this->userID)."' AND uid='".intval($this->piVars['edit_id'])."'",'', ''));
		$res = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($query);
		
		if(!is_array($res) || count($res) < 1) {
			return 'A aparut o eroare la uploadarea acestei poze';
		}
		
		$expl = explode(',', $res['poza']);
		
		if(count($expl) > 6) {
			echo 'Ati atins limita de 6 poze uploadate';exit();
		}
		
		$actualPhotos = $res['poza']; 
		//echo $res['poza']; return '';
		
		//debug($_FILES,'$_FILES');
		$prefix_directory=realpath(".");
		$prefix_directory=str_replace("\\","/",$prefix_directory );
		$prefix_directory .= '/';

		require_once(PATH_site.'t3lib/class.t3lib_basicfilefunc.php');
		$fileFunctions = t3lib_div::makeInstance('t3lib_basicFileFunctions');
		
		require_once(PATH_site . 'typo3conf/ext/zaf_anunturi/pi1/image_resizer.php');
		$imgRes = t3lib_div::makeInstance('image_resizer');

		//$uploadFolder = $this->uploadFolder;
		$uploadFolder = $prefix_directory . 'uploads/tx_zafanunturi';

		$retFiles = array('images'=>array());

		//upload images
		if($_FILES[$this->prefixId]['name']['images'][0])
		{
//debug($_FILES[$this->prefixId]['name']['images'],'images');
//print_r($_FILES[$this->prefixId]['name']['images']);			
			foreach($_FILES[$this->prefixId]['name']['images'] as $key=>$image)
			{								
				$tmp = $image;					
				$imgSize = $_FILES[$this->prefixId]['size']['images'][$key] / 1000;

				$dotPos = strrpos($image, ".");
				$ext  = strtolower(substr($image,$dotPos+1));
				
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$imgType = finfo_file($finfo, $_FILES[$this->prefixId]['tmp_name']['images'][$key]);
				finfo_close($finfo);
	
				//image is good, upload it
				if($imgSize <= $this->conf['maxImageSizeKB'] && in_array($ext, $this->allowedImageTypes) && in_array($imgType, $this->allowedImageMimeTypes))
				{				
					$image = $tmp;					
					$unique_name = $fileFunctions->getUniqueName($fileFunctions->cleanFileName($image), $uploadFolder);
					//$unique_name = md5(time()) . '.' . $ext;

					//resize before upload to server
					$imgRes->image_resizer($_FILES[$this->prefixId]['tmp_name']['images'][$key]);
					$this->error = $imgRes->error;
					
					$tmp3 = explode('/', $unique_name);
					$unique_name2 = $tmp3[sizeof($tmp3)-1]; 
					
					$imgRes->resize($uploadFolder,'800',$unique_name2);

//debug($uploadFolder,'upload folder');
//debug($unique_name2,'unique name');		
//echo $uploadFolder;
//echo $unique_name2;			
					
					//put watermark
					$watermarkImg = PATH_site . 'fileadmin/templates/images/watermark.png';	
					//$this->doWatermark($_FILES[$this->prefixId]['tmp_name']['images'][$key],$watermarkImg, '');
					$this->doWatermark($unique_name,$watermarkImg, '');
					
				/*	if(FALSE===move_uploaded_file($_FILES[$this->prefixId]['tmp_name']['images'][$key],$unique_name))
					{						
						return false;
					}
				*/	
						//debug('upload succesfully');
//echo $unique_name;
//echo 'upload successfull';						
						$tmp1 =  substr($unique_name, strlen($uploadFolder));
						$tmp1 = str_replace('/', '', $tmp1);
						$this->retFiles['images'][] = $tmp1;
						//debug($retFiles,'ret files');
//echo 'tmp1='.$tmp1.' ';						
				}

				//image is of unallowed type
				else if( (!in_array($ext, $this->allowedImageTypes) || !in_array($imgType, $this->allowedImageMimeTypes)) && $image != '')
				{
					if(!$this->error['image_type'] && $image!='')
					{
						$this->error['image_type'] = $this->pi_getLL('error_image_type').implode(',', $this->allowedImageTypes);
					}
					$this->error['images_wrong_type'] .= $this->pi_getLL('wr_img_type') . '- '.$image.'<br />';
				}

				//image is too large
				else if(($imgSize > $this->conf['maxImageSizeKB']) && $image!='')
				{
					if(!$this->error['image_size'])
					{
						$this->error['image_size'] = $this->pi_getLL('error_image_size');
					}
					$this->error['images_too_large'] .= '- '.$image.'<br>';
				}
			}
		}		
		//debug($retFiles,'$retFiles');
		//print_r($this->retFiles);
		//print_r($this->error);
		
		$newPhoto = $this->retFiles['images'][0];
		
		/*
		$actualPhotos = preg_replace( '/\,\,/', "", $actualPhotos );
		
		$rest  = substr($actualPhotos, -1);
		$rest2 = $actualPhotos[0]; 
		
		if($rest == ',') {
			$actualPhotos= substr($actualPhotos, 0, -1);
		}
		
		if($rest2 == ',') {
			$actualPhotos = substr($actualPhotos, 1);
		}		
		*/
		
		$expl = explode(',', $actualPhotos);
		array_push($expl, $newPhoto);
		
		foreach ($expl as $ex) {
			if($ex !='') {
				$aa[]=$ex;
			}
		}
		
		$newPhoto = implode(',', $aa);
		
		$updateArray = array('poza' => $newPhoto);
		
		
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_zafanunturi_anunuri', "uid='".intval($this->piVars['edit_id'])."' AND user_id='".intval($this->userID)."'", $updateArray);
		
		return $this->retFiles;
	}
	
	function getAnunt() {
		$id = $this->piVars['edit_id'];
		$res = array();
		
		$anuntul = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_zafanunturi_anunuri',"uid='".intval($id)."'","user_id='".intval($this->userID)."'", '');
		while ($anunt = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($anuntul)) {
			$res[] = $anunt;
		}
		if(count($res) < 1) {
			return false;
		}
		
		$this->edit = true;
		return $res[0];
	}
	
	function is_ajax()
    {
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }
	
	function init() {
		$this->conf['templateFile'] =  $this->conf['templateFile'] ? $this->conf['templateFile'] : 'EXT:' . $this->extKey . '/res/template_pi1.html';
		$this->templateFile = $this->cObj->fileResource($this->conf['templateFile']);
		$this->subTemplate = $this->cObj->getSubpart($this->templateFile,"###FORM_ADAUGARE###");
//debug($this->subTemplate,'template');		

		$startingPoint = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'startingPoint');
		$this->conf['startingPoint'] = $startingPoint ? $startingPoint : $GLOBALS['TSFE']->id;
		$this->conf['usersPID'] = $this->conf['usersPID'] ? $this->conf['usersPID'] : $GLOBALS['TSFE']->id;
		
		
		
		$this->conf['from_email'] = $this->conf['from_email'] ? $this->conf['from_email'] : 'admin@zaf.ro';
		
		//debug($this->conf['startingPoint'],'starting point');
		
		$this->conf['maxImageSizeKB'] = 6024;
		$this->allowedImageTypes = 'jpg,jpeg,gif,png';
		$this->allowedImageTypes = t3lib_div::trimExplode(',', $this->allowedImageTypes);
		
		$this->allowedImageMimeTypes = 'image/jpeg,image/pjpeg,image/gif,image/png';
		$this->allowedImageMimeTypes = t3lib_div::trimExplode(',', $this->allowedImageMimeTypes);
	}
	
	function getLocalitati() {
		$judeteList = $GLOBALS['TYPO3_DB']->exec_SELECTquery('id_localitate,nume_localitate,resedinta', 'tx_localitati',"id_judet='".intval($_POST['theVal'])."'",'', 'nume_localitate ASC');
		//return $judeteList2 = $GLOBALS['TYPO3_DB']->SELECTquery('uid,nume_localitate', 'tx_localitati',"id_judet='".intval($_POST['theVal'])."'",'', 'nume_localitate ASC');
		$contentData = '<option value="0">-- Alege --</option>';
		while ($j = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($judeteList)) {
			
			if($j['resedinta'] == 1) {
				$sel = 'selected="selected"';			
			}
			else 
				$sel ='';
				
			$contentData .= '<option value="'.$j['id_localitate'].'" '.$sel.'>'.$j['nume_localitate'].'</option>';
		}
		
		return $contentData;
	}
	
	function strip_tags_content($text, $tags = '', $invert = FALSE) { 
	  preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags); 
	  $tags = array_unique($tags[1]); 
	    
	  if(is_array($tags) AND count($tags) > 0) { 
	    if($invert == FALSE) { 
	      return preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text); 
	    } 
	    else { 
	      return preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text); 
	    } 
	  } 
	  elseif($invert == FALSE) { 
	    return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text); 
	  } 
	  return $text; 
	}
	
	function renderForm() {
		$judeteList = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,judet', 'tx_judet','1', 'judet ASC');
		$htmlJudete = '<option value="">--Alege--</option>' . "\n";
		while ($j = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($judeteList)) {
			$jud = $this->piVars['judet'] ? $this->piVars['judet'] : $this->anunt['judet']; 
			if($jud == $j['uid'])
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
			
			$categ = $this->piVars['categorie'] ? $this->piVars['categorie'] : $this->anunt['categorie'];
			while ($k = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($categ2)) {				
				if($categ == $k['uid'])
					$sel = 'selected="selected"';
				else 
					$sel = '';
				
				$categorii .= '<option value="'.$k['uid'].'" '.$sel.'>'.$k['categorie'].'</option>';
			}
			$categorii .= '</optgroup>';
		}
		
		$thisPageLink = $this->pi_getPageLink($GLOBALS['TSFE']->id,'',array());
		$this->titlu  	  = isset($this->piVars['titlu']) ? htmlspecialchars($this->piVars['titlu']) : htmlspecialchars($this->anunt['titlu']);
		$this->pret  	  = isset($this->piVars['pret']) ? $this->piVars['pret'] : $this->anunt['pret'];
		$this->text_anunt = isset($this->piVars['text_anunt']) ? $this->piVars['text_anunt'] : $this->anunt['text_anunt'];
		$this->text_anunt = $this->strip_tags_content($this->text_anunt, '<p><b><i><br />');
		
		$this->nume  	  = isset($this->piVars['nume']) ? $this->piVars['nume'] : $this->anunt['nume'];
		$this->email  	  = isset($this->piVars['email']) ? $this->piVars['email'] : $this->anunt['email'];
		$this->telefon    = isset($this->piVars['telefon']) ? $this->piVars['telefon'] : $this->anunt['telefon'];

//debug($this->piVars,'pi vars');		
//debug($_FILES,'files');
		//buid tip anunt list
		$lista =  array('0' => '-- Alege --',
					    '1' => 'Ofertă / Vânzare',
						'2'	=> 'Cerere / Cumpărare',
						'3' => 'Închirieri',
						'4' => 'Schimburi',
						'5' => 'Nespecificat',		
						);
		
		$tp_ant = $this->piVars['tip_anunt'] ? $this->piVars['tip_anunt'] : $this->anunt['tip_anunt'];						
		foreach($lista as $k=>$v) {
			if($tp_ant == $k)
				$sel = 'selected="selected"';
			else 
				$sel = '';	
			$tipAnunt .= '<option value="'.$k.'" '.$sel.'>'.$v.'</option>' . "\n";
		}
		
		//build monede list
		$lista =  array('0' => 'RON',
						'1'	=> 'EUR',
						);
		
		$mnda = $this->piVars['moneda'] ? $this->piVars['moneda'] : $this->anunt['moneda'];						
		foreach($lista as $k=>$v) {
			if($mnda == $k)
				$sel = 'selected="selected"';
			else 
				$sel = '';	
			$monede .= '<option value="'.$k.'" '.$sel.'>'.$v.'</option>' . "\n";
		}
		
		//build valabilitate list
		$lista =  array('0' => '1 săptămână',
						'1'	=> '2 săptămâni',		
						);
		
		$valab = $this->piVars['valabilitate'] ? $this->piVars['valabilitate'] : $this->anunt['valabilitate'];
		foreach($lista as $k=>$v) {
			if($this->piVars['valabilitate'] == $k)
				$sel = 'selected="selected"';
			else 
				$sel = '';	
			$valabilitate .= '<option value="'.$k.'" '.$sel.'>'.$v.'</option>' . "\n";
		}
		
		//check if is submitted localitate, then render back localitati list
		$localit = $this->piVars['localitate'] ? $this->piVars['localitate'] : $this->anunt['localitate'];
		$jud = $this->piVars['judet'] ? $this->piVars['judet'] : $this->anunt['judet'];
		
		if(isset($localit) && intval($localit) > 0){
			$localitatiList = $GLOBALS['TYPO3_DB']->exec_SELECTquery('id_localitate,nume_localitate', 'tx_localitati',"id_judet='".$jud."'", 'nume_localitate ASC');
			$localitatiList2 = $GLOBALS['TYPO3_DB']->SELECTquery('id_localitate,nume_localitate', 'tx_localitati',"id_judet='".$jud."'", 'nume_localitate ASC');
			//debug($localitatiList2,'query');
			
			$htmlLocalitati = '';
			while ($j = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($localitatiList)) {
				if($localit == $j['id_localitate'])
					$sel = 'selected="selected"';
				else 
					$sel = '';
				
				$htmlLocalitati .= '<option value="'.$j['id_localitate'].'" '.$sel.'>';
				$htmlLocalitati .= $j['nume_localitate'];
				$htmlLocalitati .= '</option>' . "\n";
			}
		}
		else {
			$htmlLocalitati = '';
		}
		
		$captchaImg = t3lib_extMgm::isLoaded('captcha') ? '<img src="'.t3lib_extMgm::siteRelPath('captcha').'captcha/captcha.php?'.time().'" alt="" />' : '';
		
		
		//draw photos 
		$poze = $this->anunt;
		$poze = $poze['poza'];
		$poze = $this->getPhotos($poze);
		$photos = '<ul id="sortable" class="clearfix">';
		$counter = 1;
		
		foreach($poze as $poza){
			
			$photos .= '<li data-original-name="'.$poza['orig'].'" class="ui-state-default element_'.$counter.'">';
			$photos .= '<div class="img"><img src="'.$poza['src'].'" /></div>';
			$photos .= '<div class="lnk"><a data-image="'.$poza['orig'].'" href="#">Sterge</a></div>';
						
			$photos .= '</li>';
			$counter++;
		}
		$photos .= '</ul>';	

		$photos .= '<input class="file2" type="file" id="file2" name="images[]" />';
		
		$ajaxURL = $this->pi_getPageLink($GLOBALS['TSFE']->id, '', array('tx_zafanunturi_pi1[edit_id]' => $this->piVars['edit_id'] ) );
		
		$this->subTemplate = $this->cObj->substituteMarkerArray($this->subTemplate, 
																array('###JURDETE###'	 	=> $htmlJudete,
																	  '###CATEGORII###'	 	=> $categorii,
																	  '###ACTION###'	 	=> $thisPageLink,
																	  '###TITLU###' 	 	=> $this->titlu,
																	  '###PRET###' 		 	=> $this->pret,
																	  '###TEXT_ANUNT###' 	=> $this->text_anunt,
																	  '###TIP_ANUNT###'	 	=> $tipAnunt,
																	  '###MONEDE###'	 	=> $monede,
																	  '###VALABILITATE###'	=> $valabilitate,
																	  '###LOCALITATI###' 	=> $htmlLocalitati,
																	  '###CAPTCHA###'	    => $captchaImg,	
																	  '###NUME###'	    	=> ($this->userID > 0) ? $this->userName  : htmlspecialchars($this->nume),
																	  '###EMAIL###'	    	=> ($this->userID > 0) ? $this->userEmail : htmlspecialchars($this->email),
																	  '###TELEFON###'	    => htmlspecialchars($this->telefon),
																	  '###EDIT_ID###'		=> count($this->anunt) > 0 ? $this->piVars['edit_id'] : '',	
																	  '###POZE_FORM###'		=> $photos,													
																	  '###AJAX_URL###'		=> $ajaxURL,
																	  '###NEW_IMAGES###'	=> $this->anunt['poza'],	
																	  '###READONLY###'		=> ($this->userID > 0) ? 'readonly="readonly"' : '',			  
														 	  		 )
		 	   												   );
		return $this->subTemplate;
	}
	
	function getPhotos($img) {
			$counter = 0;
			//debug($row,'row');						
			$expl = explode(',', $img);
			if(is_array($expl) && count($expl) > 0) {				
				//poze thumbs
				foreach ($expl as $e) {
//debug($this->conf['thumbWidth'],'width');					
						$imageConf['file'] = 'uploads/tx_zafanunturi/'.$e;				
						$imageConf['file.']['width'] = $this->conf['thumbWidth'].'c';
						$imageConf['file.']['height'] = $this->conf['thumbHeight'].'c';
						$imageConf['altText'] = 'Anunturi gratuite';
						$imageConf['titleText'] = 'Anunturi gratuite';
						$res[$counter]['src'] = $this->cObj->IMG_RESOURCE($imageConf);
						$res[$counter]['orig'] = $e; 
						$counter++;												
				}
			}
			else { 	
				//$poza = $row['poza'];
				$imageConf['file'] = 'uploads/tx_zafanunturi/'.$img;				
				$imageConf['file.']['width'] = $this->conf['thumbWidth'].'c';
				$imageConf['file.']['height'] = $this->conf['thumbHeight'].'c';
				$imageConf['altText'] = 'Anunturi gratuite';
				$imageConf['titleText'] = 'Anunturi gratuite';
				$res[$counter]['src'] = $this->cObj->IMG_RESOURCE($imageConf);								
				$res[$counter]['orig'] = $img;					
			}	
			return $res;			
 	}
	
	function insertAnunt() {
		$feUserID = $GLOBALS['TSFE']->fe_user->user['uid'];
		
		$this->md5Hash = md5(time());	
		
		if($this->validateData() == false) {
			//debug($this->error,'errors');
			return false;
		}
		
				
		
		//upload the files
		if($this->uploadFiles() === false || isset($this->error['image_type']) || isset($this->error['image_size'])) {
			//debug($this->error,'errors');
			
			return false;
		}
		
		if(is_array($this->retFiles['images']) && count($this->retFiles['images']) >0) {
			
			
			foreach ($this->retFiles['images'] as $im) {
				$imageConf['file'] = 'uploads/tx_zafanunturi/'.$im;
				$imageConf['file.']['maxH'] = $this->conf['mailPhotoW'];
				$imageConf['file.']['maxW'] = $this->conf['mailPhotoH'];
				$pozaMica = $this->cObj->IMG_RESOURCE($imageConf);
				
				$this->resizedImgForMail[] = $GLOBALS['TSFE']->baseUrl . $pozaMica;
			} 

			$images = implode(',', $this->retFiles['images']);
		}
		else 	
			$images = '';
			
//debug($this->resizedImgForMail,'poze pentru mail');			
		//check if existr FE user with provided email address
		$users = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid', 'fe_users', "email='".trim($this->piVars['email'])."'", '', '', '1');

		$eml = $this->piVars['email'] ? $this->piVars['email'] : $this->anunt['email'];
		$nme = $this->piVars['nume'] ? $this->piVars['nume'] : $this->anunt['nume'];
		
		if(!is_array($users) || count($users) < 1) {
			$insert_array2 = array( 'tstamp'	 => time(),
									'crdate '	 => time(),
									'username'	 => trim($eml),
									'email'		 => trim($eml),
									'first_name' => trim($nme),
									'pid'		 => $this->conf['usersPID'],
							      );
							      
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('fe_users', $insert_array2);
			$user_id = $GLOBALS['TYPO3_DB']->sql_insert_id();
			
		}
		else {
			$user_id = $users[0]['uid'];
		}	
		
		if(isset($this->piVars['contactat_pe_mail']) && $this->piVars['contactat_pe_mail'] == 'on') {
			$noEmailContact = 0;
		}
		else {
			$noEmailContact = 1;
		}
		
		$insertArray = array('titlu' 		=> $this->piVars['titlu'],
							 'tip_anunt' 	=> intval($this->piVars['tip_anunt']),
							 'categorie'	=> intval($this->piVars['categorie']),
							 'judet'		=> intval($this->piVars['judet']),
							 'localitate'	=> intval($this->piVars['localitate']),
							 'pret'			=> intval($this->piVars['pret']),
							 'moneda'		=> intval($this->piVars['moneda']),
							 'valabilitate' => intval($this->piVars['valabilitate']),
							 'text_anunt' 	=> strip_tags($this->piVars['text_anunt'], '<p><b><i><br /><br><s><em>'),
							 'user_id'		=> intval($user_id),
							 'pid'			=> intval($this->conf['startingPoint']),
							 'tstamp'		=> time(),
							 'crdate'		=> time(),
							 'poza'			=> $images,
							 'nume'			=> $this->piVars['nume'],
							 'email'		=> $this->piVars['email'],
						     'telefon'		=> $this->piVars['telefon'],
							 'md5hash'		=> $this->md5Hash,
							 'deleted'		=> $this->piVars['edit_id'] > 0 ? '0' : '1',
							 'contact_pe_mail' => $noEmailContact,	
							);
							
		$updateArray = array('titlu' 		=> $this->piVars['titlu'],
							 'tip_anunt' 	=> intval($this->piVars['tip_anunt']),
							 'categorie'	=> intval($this->piVars['categorie']),
							 'judet'		=> intval($this->piVars['judet']),
							 'localitate'	=> intval($this->piVars['localitate']),
							 'pret'			=> intval($this->piVars['pret']),
							 'moneda'		=> intval($this->piVars['moneda']),
							 'valabilitate' => intval($this->piVars['valabilitate']),
							 'text_anunt' 	=> strip_tags($this->piVars['text_anunt'], '<p><b><i><br /><br><s><em>'),
							 'user_id'		=> intval($user_id),
							 'pid'			=> intval($this->conf['startingPoint']),
							 'tstamp'		=> time(),
							 'crdate'		=> time(),							 
							 'nume'			=> $this->piVars['nume'],
							 'email'		=> $this->piVars['email'],
						     'telefon'		=> $this->piVars['telefon'],
							 'md5hash'		=> $this->md5Hash,
							 'deleted'		=> $this->piVars['edit_id'] > 0 ? '0' : '1',
							 'contact_pe_mail' => $noEmailContact,	
							 'poza'			=>  $this->piVars['new_images'],
							);							
		
		//check if is insert or update
		if($this->piVars['edit_id'] && $this->piVars['edit_id'] > 0) {
			//update database
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_zafanunturi_anunuri', "uid='".intval($this->piVars['edit_id'])."' AND user_id='".intval($this->userID)."'", $updateArray);
			setcookie("ZafSucessUpdate", 'set', time()+80);
		}					
		else {
			//insert in database
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_zafanunturi_anunuri', $insertArray);
			
			////////////// SEND THE MAIL !!!!!!!!!!!!!!!!!!!
			$this->sendApprovalEmail();
			///////////////!!!!!!!!!!!!!!!!!!!
		}
			
		//clear captcha after submit
		$_SESSION['tx_captcha_string'] = '';
		
		
		$redirectLink = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'thankYouPage');
		if($redirectLink >0) {
			header('Location:'.$this->pi_getPageLink($redirectLink,'',array())); exit();
		}
		
	}

	function validateData() {
		$valid = true;
		
		if(!isset($this->piVars['titlu']) || trim($this->piVars['titlu'])=='') {
			$valid = false;
			$this->error .= $this->pi_getLL('no_title').'<br />';
		}
		
		/*
		if(!isset($this->piVars['tip_anunt']) || trim($this->piVars['tip_anunt']) < 1 ){
			$valid = false;
			$this->error .= $this->pi_getLL('no_tip_anunt').'<br />';
		}
		*/
		
		if(!isset($this->piVars['categorie']) || trim($this->piVars['categorie']) < 1 ){
			$valid = false;
			$this->error .= $this->pi_getLL('no_categorie').'<br />';
		}
		
		if(!isset($this->piVars['tip_anunt']) || trim($this->piVars['tip_anunt']) < 1 ){
			$valid = false;
			$this->error .= $this->pi_getLL('no_tip_anunt').'<br />';
		}
		
		if(!isset($this->piVars['judet']) || trim($this->piVars['judet']) < 1 ){
			$valid = false;
			$this->error .= $this->pi_getLL('no_judet').'<br />';
		}
		else {
			$jud = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid', 'tx_judet', '', '', '', '');
			foreach($jud as $j){
				$judete[] = $j['uid'];
			}			

			if(!in_array(trim($this->piVars['judet']), $judete)) {
				$valid = false;
				$this->error .= $this->pi_getLL('id_judet_invalid'). $this->piVars['judet'] . '<br />';
			}
		}		
		
		if(!isset($this->piVars['localitate']) || trim($this->piVars['localitate']) < 1 ){
			$valid = false;
			$this->error .= $this->pi_getLL('no_localitate').'<br />';
		}
		else {
			$localit = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('id_localitate', 'tx_localitati', '', '', '', '');
			foreach($localit as $l){
				$localitati[] = $l['id_localitate'];
			}			

			if(!in_array(trim($this->piVars['localitate']), $localitati)) {
				$valid = false;
				$this->error .= $this->pi_getLL('id_localitate_invalid'). $this->piVars['localitate'] . '<br />';
			}
		}
		
		if(!isset($this->piVars['pret']) || intval($this->piVars['pret']) < 1 ){
			$valid = false;
			$this->error .= $this->pi_getLL('no_pret').'<br />';
		}
		
//debug($this->piVars['text_anunt'],'text anunt');	
		if(!isset($this->piVars['text_anunt']) || trim($this->piVars['text_anunt'])=='') {
			$valid = false;
			$this->error .= $this->pi_getLL('no_anunt').'<br />';
		}
		
		if(!isset($this->piVars['nume']) || trim($this->piVars['nume'])=='') {
			$valid = false;
			$this->error .= $this->pi_getLL('no_nume').'<br />';
		}
		
		if(!isset($this->piVars['email']) || trim($this->piVars['email'])=='') {
			$valid = false;
			$this->error .= $this->pi_getLL('no_email').'<br />';
		}
		elseif (!preg_match("/^[\w.-]+@[\w.-]+\.[A-Za-z]{2,6}$/", $this->piVars['email'])) {     
    		$valid = false;
			$this->error .= $this->pi_getLL('wrong_email').'<br />';
		}
		
		if(!isset($this->piVars['telefon']) || trim($this->piVars['telefon'])=='') {
			$valid = false;
			$this->error .= $this->pi_getLL('no_telefon').'<br />';
		}
		elseif(!is_numeric(trim($this->piVars['telefon']))) {
			$valid = false;
			$this->error .= $this->pi_getLL('wrong_telefon').'<br />';
		}
		
		if (t3lib_extMgm::isLoaded('captcha'))	
		{
			session_start();
			$captchaStr = $_SESSION['tx_captcha_string'];
			$_SESSION['tx_captcha_string'] = '';
		} 
		else 
		{
			$captchaStr = -1;
		}		
		
		if(!$this->userID || $this->userID < 1) {
			if ($captchaStr != -1 && ($captchaStr && $this->piVars['captchaResponse']!=$captchaStr))
			{
				$valid = false;
				$this->error .= $this->pi_getLL('no_good_captcha').'<br />';
			}
		}
		
		return $valid;
	}
	
	function uploadFiles()
	{
		//debug($_FILES,'$_FILES');
		$prefix_directory=realpath(".");
		$prefix_directory=str_replace("\\","/",$prefix_directory );
		$prefix_directory .= '/';

		require_once(PATH_site.'t3lib/class.t3lib_basicfilefunc.php');
		$fileFunctions = t3lib_div::makeInstance('t3lib_basicFileFunctions');
		
		require_once(PATH_site . 'typo3conf/ext/zaf_anunturi/pi1/image_resizer.php');
		$imgRes = t3lib_div::makeInstance('image_resizer');

		//$uploadFolder = $this->uploadFolder;
		$uploadFolder = $prefix_directory . 'uploads/tx_zafanunturi';

		$retFiles = array('images'=>array());

		//upload images
		if($_FILES[$this->prefixId]['name']['images'][0])
		{
//debug($_FILES[$this->prefixId]['name']['images'],'images');			
			foreach($_FILES[$this->prefixId]['name']['images'] as $key=>$image)
			{
				$tmp = $image;					
				$imgSize = $_FILES[$this->prefixId]['size']['images'][$key] / 1000;

				$dotPos = strrpos($image, ".");
				$ext  = strtolower(substr($image,$dotPos+1));
				
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$imgType = finfo_file($finfo, $_FILES[$this->prefixId]['tmp_name']['images'][$key]);
				finfo_close($finfo);
	
				//image is good, upload it
				if($imgSize <= $this->conf['maxImageSizeKB'] && in_array($ext, $this->allowedImageTypes) && in_array($imgType, $this->allowedImageMimeTypes))
				{				
					$image = $tmp;					
					$unique_name = $fileFunctions->getUniqueName($fileFunctions->cleanFileName($image), $uploadFolder);
					//$unique_name = md5(time()) . '.' . $ext;

					//resize before upload to server
					$imgRes->image_resizer($_FILES[$this->prefixId]['tmp_name']['images'][$key]);
					$this->error = $imgRes->error;
					
					$tmp3 = explode('/', $unique_name);
					$unique_name2 = $tmp3[sizeof($tmp3)-1]; 
					
					$imgRes->resize($uploadFolder,'800',$unique_name2);

//debug($uploadFolder,'upload folder');
//debug($unique_name2,'unique name');					
					
					//put watermark
					$watermarkImg = PATH_site . 'fileadmin/templates/images/watermark.png';	
					//$this->doWatermark($_FILES[$this->prefixId]['tmp_name']['images'][$key],$watermarkImg, '');
					$this->doWatermark($unique_name,$watermarkImg, '');
					
				/*	if(FALSE===move_uploaded_file($_FILES[$this->prefixId]['tmp_name']['images'][$key],$unique_name))
					{						
						return false;
					}
				*/	
						//debug('upload succesfully');
						$tmp1 =  substr($unique_name, strlen($uploadFolder));
						$tmp1 = str_replace('/', '', $tmp1);
						$this->retFiles['images'][] = $tmp1;
						//debug($retFiles,'ret files');
				}

				//image is of unallowed type
				else if( (!in_array($ext, $this->allowedImageTypes) || !in_array($imgType, $this->allowedImageMimeTypes)) && $image != '')
				{
					if(!$this->error['image_type'] && $image!='')
					{
						$this->error['image_type'] = $this->pi_getLL('error_image_type').implode(',', $this->allowedImageTypes);
					}
					$this->error['images_wrong_type'] .= $this->pi_getLL('wr_img_type') . '- '.$image.'<br />';
				}

				//image is too large
				else if(($imgSize > $this->conf['maxImageSizeKB']) && $image!='')
				{
					if(!$this->error['image_size'])
					{
						$this->error['image_size'] = $this->pi_getLL('error_image_size');
					}
					$this->error['images_too_large'] .= '- '.$image.'<br>';
				}
			}
		}		
		//debug($retFiles,'$retFiles');
		return $retFiles;
	}
	
	function doWatermark($calePoza, $caleLogo, $pozitionare = 'centru') {
		$watermark = imagecreatefrompng($caleLogo);
		$photo = imagecreatefromjpeg($calePoza);

		imagealphablending($photo, true);
		
		$widthWatermark = imagesx($watermark);
		$heightWatermark = imagesy($watermark);
		$widthPhoto = imagesx($photo);
		$heightPhoto = imagesy($photo);
		if ($pozitionare == 'centru') {
		    $xLogoPosition = ceil(($widthPhoto - $widthWatermark) / 2);
		    $yLogoPosition = ceil(($heightPhoto - $heightWatermark) / 2);
		} else {		
		    $xLogoPosition = $widthPhoto - $widthWatermark - 10;
		    $yLogoPosition = $heightPhoto - $heightWatermark - 10;
		}
		
		$result = imagecopy($photo, $watermark, $xLogoPosition, $yLogoPosition, 0, 0, $widthWatermark, $heightWatermark);
		
		imagejpeg($photo, $calePoza, 100);
		imagedestroy($photo);

		return $result;
	}
	
	function sendApprovalEmail()
	{
		$localit = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('nume_localitate', 'tx_localitati', "id_localitate='".intval($this->piVars['localitate'])."'", '', '', '');
		$localitate = $localit[0]['nume_localitate'];

		$categ = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('categorie', 'tx_zafanunturi_anunuri_categorii', "uid='".intval($this->piVars['categorie'])."'", '', '', '');
		$categorie = $categ[0]['categorie'];
		
		if( $this->piVars['moneda'] == 0) {
			$moneda = 'RON';
		}  
		elseif($this->piVars['moneda'] == 1) {
			$moneda = 'EUR';
		}
		elseif($this->piVars['moneda'] == 2) {
			$moneda = 'USD';
		}
		
		//link buid  $this->md5Hash
		
		$theLink = $GLOBALS['TSFE']->baseUrl . 'index.php?id='.$this->conf['approvePage'].'&h='.$this->md5Hash;
		
		$tpl = $this->cObj->fileResource($this->conf['mailToUserTemplate'] ? $this->conf['mailToUserTemplate'] : 'EXT:' . $this->extKey . '/res/mail_to_user.html');		
		$emailSubpart = $this->cObj->getSubpart($tpl,'###CONFIRM_EMAIL_TEMPLATE###');
		
		if(isset($this->resizedImgForMail) && is_array($this->resizedImgForMail)){
			foreach($this->resizedImgForMail as $poza) {
				$imgS .= '<img src="'.$poza.'" /> <br />';
			}
		}
		else{
			$imgS = 'Nu sunt poze atasate.';
		}
		
		$emailContent = $this->cObj->substituteMarkerArray($emailSubpart,
														array(  '###TITLU###' 		 => $this->piVars['titlu'],
																'###PRET###' 		 => $this->piVars['pret'].' '.$moneda ,
																'###CATEGORIE###'  	 => $categorie,
																'###LOCALITATE###' 	 => $localitate,
																'###TEXT_ANUNT###'	 => $this->piVars['text_anunt'],
																'###LINK_ANUNT###'	 => '<a href="'.$theLink.'">'.$theLink.'</a>',
																'###NUME###'		 => $this->piVars['nume'],
																'###TELEFON###'		 => $this->piVars['telefon'],
																'###EMAIL###'		 => $this->piVars['email'],
																'###POZE_ATASATE###' => $imgS,
															 )
													  	   );													  	
													  	   
													  	   
		$fromEmail = $this->conf['approval_email_to'];

		$sendMailObj = t3lib_div::makeInstanceClassName('t3lib_htmlmail');
		$sendMailObj = t3lib_div::makeInstance($sendMailObj);
		$sendMailObj->start();
		$sendMailObj->defaultCharset = 'utf-8';						
		$sendMailObj->useBase64();
		$sendMailObj->subject = $this->conf['subject'];
		$sendMailObj->from_email = $this->conf['from_email'];
		$sendMailObj->from_name =  $this->conf['fromName'];
		$sendMailObj->replyto_email = $this->conf['from_email'];
		$sendMailObj->replyto_name = $this->conf['fromName'];
		$sendMailObj->dontEncodeHeader = true;
		$sendMailObj->organisation = '';
		$sendMailObj->priority = 3;
		$sendMailObj->setHeaders();		
		$sendMailObj->setHtml($sendMailObj->encodeMsg($emailContent));
				
		$sendMailObj->setRecipient($this->piVars['email']);
		$sendMailObj->setContent();
		$sendMailObj->sendtheMail();		
	}
}



if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/zaf_anunturi/pi1/class.tx_zafanunturi_pi1.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/zaf_anunturi/pi1/class.tx_zafanunturi_pi1.php']);
}

?>