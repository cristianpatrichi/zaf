<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Cristi <bau_baus2002@yahoo.com>
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
 * Plugin 'ZAF Weird Login' for the 'zaf_weirdlogin' extension.
 *
 * @author	Cristi <bau_baus2002@yahoo.com>
 * @package	TYPO3
 * @subpackage	tx_zafweirdlogin
 */
class tx_zafweirdlogin_pi1 extends tslib_pibase {
	public $prefixId      = 'tx_zafweirdlogin_pi1';		// Same as class name
	public $scriptRelPath = 'pi1/class.tx_zafweirdlogin_pi1.php';	// Path to this script relative to the extension dir.
	public $extKey        = 'zaf_weirdlogin';	// The extension key.
	
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
	
		if($this->piVars['email'] && $this->is_ajax()) {
			
		$user = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users',"username='".trim($this->piVars['email'])."'", '', '');
		while ($usr = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($user)) {
			$res[] = $usr;
		}
		if(count($res) < 1) {
			$res['err'] = 'err';
			$res['msg'] = '<div class="alert alert-error"><b>Eroare:</b> <br /> Adresa de email <b>'.htmlspecialchars(trim($this->piVars['email'])).'</b> nu exista in sistem!</div>';
			echo json_encode($res); exit(); 
		}
		else {
			$newPass = md5(rand() * time());
			$updateArray = array('temp_password' => $newPass);			
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users', "username='".trim($this->piVars['email'])."'", $updateArray);
		}
			$res['err'] = 'ok';
			$res['msg'] = '<div class="alert alert-success"><b>Email trimis:</b> <br /> S-a trimis un emai cu link pentru login automat pe adresa '.htmlspecialchars(trim($this->piVars['email'])).'.</div>';
			echo json_encode($res); exit();
		}
		
		
		
		//$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] .= $js;
		
		$content  = '<script type="text/javascript" src="/typo3conf/ext/zaf_weirdlogin/res/zaf_weirdlogin.js"></script>'; 
		$content .= '<div class="theForm">';
		$content .= '<label>Adresa de Email cu care ati postat anuntul:</label>';
		$content .= '<input type="text" name="email" id="email" value="" />';
		$content .= '<a id="emailLink" class="btn btn-primary" href="#">Trimite</a>';
		$content .= '</div>';
		$content .= '<div class="response"></div>';
			
		
		
		return $this->pi_wrapInBaseClass($content);
	}
	
	function is_ajax()
    {
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }
}



if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/zaf_weirdlogin/pi1/class.tx_zafweirdlogin_pi1.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/zaf_weirdlogin/pi1/class.tx_zafweirdlogin_pi1.php']);
}

?>