#
# Table structure for table 'tx_zafanunturi_anunuri_judet_mm'
# 
#
CREATE TABLE tx_zafanunturi_anunuri_judet_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);




#
# Table structure for table 'tx_zafanunturi_anunuri_localitate_mm'
# 
#
CREATE TABLE tx_zafanunturi_anunuri_localitate_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);


CREATE TABLE tx_zafanunturi_anunuri_categorii (
  uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
  categorie varchar(200) DEFAULT '' NOT NULL,
  
  PRIMARY KEY (uid),
  KEY parent (pid)
);

CREATE TABLE tx_zafanunturi_anunuri_categorii_label (
  uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
  categ_label varchar(200) DEFAULT '' NOT NULL,

  PRIMARY KEY (uid),
  KEY parent (pid)  
);

#
# Table structure for table 'tx_zafanunturi_anunuri'
#
CREATE TABLE tx_zafanunturi_anunuri (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	titlu tinytext,
	tip_anunt int(11) DEFAULT '0' NOT NULL,
	categorie int(11) DEFAULT '0' NOT NULL,
	judet int(11) DEFAULT '0' NOT NULL,
	localitate int(11) DEFAULT '0' NOT NULL,
	pret tinytext,
	moneda int(11) DEFAULT '0' NOT NULL,
	valabilitate int(11) DEFAULT '0' NOT NULL,
	text_anunt text,
	poza text,
	user_id int(11) DEFAULT '0' NOT NULL,
	nume tinytext,
	email tinytext,
	telefon tinytext,
	md5hash text,
	confirmed_once int(11) DEFAULT '0' NOT NULL,
	contact_pe_mail tinyint(4) DEFAULT '1' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
) ENGINE=InnoDB;