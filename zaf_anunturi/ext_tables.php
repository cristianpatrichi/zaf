<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1'] = 'layout,select_key,pages';


t3lib_extMgm::addPlugin(array(
	'LLL:EXT:zaf_anunturi/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2'] = 'layout,select_key,pages';

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi3'] = 'layout,select_key,pages';

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi4'] = 'layout,select_key,pages';

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi5'] = 'layout,select_key,pages';

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi6'] = 'layout,select_key,pages';

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi7'] = 'layout,select_key,pages';

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi8'] = 'layout,select_key,pages';


t3lib_extMgm::addPlugin(array(
	'LLL:EXT:zaf_anunturi/locallang_db.xml:tt_content.list_type_pi2',
	$_EXTKEY . '_pi2',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

t3lib_extMgm::addPlugin(array(
	'LLL:EXT:zaf_anunturi/locallang_db.xml:tt_content.list_type_pi3',
	$_EXTKEY . '_pi3',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

t3lib_extMgm::addPlugin(array(
	'LLL:EXT:zaf_anunturi/locallang_db.xml:tt_content.list_type_pi4',
	$_EXTKEY . '_pi4',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

t3lib_extMgm::addPlugin(array(
	'LLL:EXT:zaf_anunturi/locallang_db.xml:tt_content.list_type_pi5',
	$_EXTKEY . '_pi5',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

t3lib_extMgm::addPlugin(array(
	'LLL:EXT:zaf_anunturi/locallang_db.xml:tt_content.list_type_pi6',
	$_EXTKEY . '_pi6',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

t3lib_extMgm::addPlugin(array(
	'LLL:EXT:zaf_anunturi/locallang_db.xml:tt_content.list_type_pi7',
	$_EXTKEY . '_pi7',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

t3lib_extMgm::addPlugin(array(
	'LLL:EXT:zaf_anunturi/locallang_db.xml:tt_content.list_type_pi8',
	$_EXTKEY . '_pi8',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

$TCA['tx_zafanunturi_anunuri'] = array(
	'ctrl' => array(
		'requestUpdate' => 'judet',
		'title'     => 'LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri',		
		'label'     => 'titlu',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate',	
		'delete' => 'deleted',	
		'enablecolumns' => array(		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY) . 'icon_tx_zafanunturi_anunuri.gif',
	),
);

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1'] = 'pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi1', 'FILE:EXT:' . $_EXTKEY . '/pi1/flexform_ds.xml');

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi2'] = 'pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi2', 'FILE:EXT:' . $_EXTKEY . '/pi2/flexform_ds.xml');

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi4'] = 'pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi4', 'FILE:EXT:' . $_EXTKEY . '/pi4/flexform_ds.xml');


if (TYPO3_MODE=="BE")
{
	include_once(t3lib_extMgm::extPath("zaf_anunturi")."class.tx_zafanunturi_customselect.php");
}
?>