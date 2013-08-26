<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_zafanunturi_anunuri'] = array(
	'ctrl' => $TCA['tx_zafanunturi_anunuri']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'hidden,titlu,tip_anunt,judet,localitate,pret,moneda,valabilitate,text_anunt, nume, telefon, email'
	),
	'feInterface' => $TCA['tx_zafanunturi_anunuri']['feInterface'],
	'columns' => array(
		'hidden' => array(		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array(
				'type'    => 'check',
				'default' => '0'
			)
		),
		'titlu' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.titlu',		
			'config' => array(
				'type' => 'input',	
				'size' => '30',
			)
		),
		'tip_anunt' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.tip_anunt',		
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.tip_anunt.I.0', '0'),
					array('LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.tip_anunt.I.1', '1'),
					array('LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.tip_anunt.I.2', '2'),
					array('LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.tip_anunt.I.3', '3'),
					array('LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.tip_anunt.I.4', '4'),
					array('LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.tip_anunt.I.4', '5'),
				),
				'size' => 1,	
				'maxitems' => 1,
			)
		),
		"categorie" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.categ",
			"config" => Array (
				"type" => "select",
				"items" => Array(),
				"size" => 1,
				"itemsProcFunc" => "tx_zafanunturi_customselect->main_categ",
				"minitems" => 0,
				"maxitems" => 1,
			)
		),		
		"judet" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.judet",
			"config" => Array (
				"type" => "select",
				"items" => Array(),
				"size" => 1,
				"itemsProcFunc" => "tx_zafanunturi_customselect->main_judet",
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"localitate" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.localitate",
			"config" => Array (
				"type" => "select",
				"size" => 1,
				"items" => Array(
					Array("LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.select", "0"),
				),
				"itemsProcFunc" => "tx_zafanunturi_customselect->main_localitate",
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		'pret' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.pret',		
			'config' => array(
				'type' => 'input',	
				'size' => '30',
			)
		),
		'moneda' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.moneda',		
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.moneda.I.0', '0'),
					array('LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.moneda.I.1', '1'),					
				),
				'size' => 1,	
				'maxitems' => 1,
			)
		),
		'valabilitate' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.valabilitate',		
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.valabilitate.I.0', '0'),
					array('LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.valabilitate.I.1', '1'),
				),
				'size' => 1,	
				'maxitems' => 1,
			)
		),
		'text_anunt' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.text_anunt',		
			'config' => array(
				'type' => 'text',
				'cols' => '30',	
				'rows' => '5',
			)
		),
		
		'poza' => array(        
            'exclude' => 0,        
            'label' => 'LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.poza',        
            'config' => array(
                'type' => 'group',
                'internal_type' => 'file',
                'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],    
                'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],    
                'uploadfolder' => 'uploads/tx_zafanunturi',
				'show_thumbs' => 1,
                'size' => 6,    
                'minitems' => 0,
                'maxitems' => 6,
            )
        ),
        
        'nume' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.nume',		
			'config' => array(
				'type' => 'input',	
				'size' => '30',
			)
		),
		
		'telefon' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.telefon',		
			'config' => array(
				'type' => 'input',	
				'size' => '30',
			)
		),
		
		'email' => array(		
			'exclude' => 0,		
			'label' => 'LLL:EXT:zaf_anunturi/locallang_db.xml:tx_zafanunturi_anunuri.email',		
			'config' => array(
				'type' => 'input',	
				'size' => '30',
			)
		),
	),
	'types' => array(
		'0' => array('showitem' => 'hidden;;1;;1-1-1, titlu, tip_anunt, categorie, judet, localitate, pret, moneda, valabilitate, text_anunt, poza, nume, telefon, email')
	),
	'palettes' => array(
		'1' => array('showitem' => '')
	)
);
?>