<?php
/**
 * @package Individuelle Kontaktformulare
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart-pro.at/license/2_0.txt GNU Public License V2.0
 * @version $Id: 1_2_0.php 2019-02-22 11:13:51Z webchills $
 */
 
$db->Execute("CREATE TABLE IF NOT EXISTS " . TABLE_CUSTOM_FORMS . " (
 `form_id` int(11) NOT NULL AUTO_INCREMENT,
 `form_title` varchar(128) DEFAULT NULL,
 `page_title` varchar(128) DEFAULT NULL,
 `page_heading` varchar(128) DEFAULT NULL,
 `navbar_title` varchar(64) DEFAULT NULL,
 `form_description` text,
 `created_by` int(11) DEFAULT NULL,
 `timestamp` datetime DEFAULT NULL,
 PRIMARY KEY (`form_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");

$db->Execute("CREATE TABLE IF NOT EXISTS " . TABLE_CUSTOM_FORMS_FIELDS . " (
 `form_field_id` int(11) NOT NULL AUTO_INCREMENT,
 `form_id` int(11) NOT NULL DEFAULT '0',
 `field_type` enum('Dropdown','Text','Text Area','Radio','Checkbox','File','Read Only') NOT NULL DEFAULT 'Text',
 `field_name` varchar(64) NOT NULL,
 `label` varchar(64) NOT NULL,
 `description` varchar(1024) DEFAULT NULL,
 `required` tinyint(1) NOT NULL DEFAULT '0',
 `sort_order` int(3) NOT NULL DEFAULT '0',
 `modified_by` int(11) DEFAULT NULL,
 `timestamp` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`form_field_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");

$db->Execute("CREATE TABLE IF NOT EXISTS " . TABLE_CUSTOM_FORMS_FIELDS_OPTIONS . " (
 `form_field_option_id` int(11) NOT NULL AUTO_INCREMENT,
 `form_field_id` int(11) NOT NULL,
 `field_text` varchar(256) NOT NULL,
 `field_value` varchar(1024) DEFAULT NULL,
 `selected` tinyint(1) NOT NULL DEFAULT '0',
 `read_only` tinyint(1) NOT NULL DEFAULT '0',
 `sort_order` int(3) NOT NULL DEFAULT '0',
 PRIMARY KEY (`form_field_option_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;");

$db->Execute("CREATE TABLE IF NOT EXISTS " . TABLE_CUSTOM_FORMS_HITS . " (
 `form_hit_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `form_id` int(11) NOT NULL,
 `account_id` int(11) DEFAULT NULL,
 `referer` varchar(256) DEFAULT NULL,
 `timestamp` datetime DEFAULT NULL,
 PRIMARY KEY (`form_hit_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");

$db->Execute("CREATE TABLE IF NOT EXISTS " . TABLE_CUSTOM_FORMS_REQUESTS . " (
 `request_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `form_id` int(11) DEFAULT NULL,
 `customer_name` varchar(64) DEFAULT NULL,
 `customer_company` varchar(64) DEFAULT NULL,
 `customer_phone` varchar(16) DEFAULT NULL,
 `customer_email` varchar(128) DEFAULT NULL,
 `account_id` int(11) DEFAULT NULL,
 `remote_ip` varchar(32) DEFAULT NULL,
 `platform` varchar(64) DEFAULT NULL,
 `mobile` tinyint(1) NOT NULL DEFAULT '0',
 `browser_name` varchar(32) DEFAULT NULL,
 `browser_version` varchar(32) DEFAULT NULL,
 `user_agent` varchar(256) DEFAULT NULL,
 `message` text NOT NULL,
 `status` varchar(32) DEFAULT NULL,
 `message_timestamp` datetime DEFAULT NULL,
 PRIMARY KEY (`request_id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;");
 
$db->Execute(" SELECT @gid:=configuration_group_id
FROM ".TABLE_CONFIGURATION_GROUP."
WHERE configuration_group_title= 'Individuelle Kontaktformulare'
LIMIT 1;");


$db->Execute("INSERT IGNORE INTO ".TABLE_CONFIGURATION." (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, last_modified, use_function, set_function) VALUES
('Email addresses', 'CUSTOM_FORMS_RECIPIENT_EMAILS', '', 'E-mail addresses of customer service or administrative staff assigned to respond to customer inquiries sent over using custom forms.', @gid, 2, now(), now(), '', ''),
('Ask for Customer Name', 'CUSTOM_FORMS_INCLUDE_NAME', 'Yes', 'Ask for the customers name?', @gid, 3, now(), now(), '', 'zen_cfg_select_option(array(\'Yes\', \'No\'),'),
('Require Customer Name', 'CUSTOM_FORMS_REQUIRE_NAME', 'Yes', 'Is customers name required?', @gid, 4, now(), now(), '', 'zen_cfg_select_option(array(\'Yes\', \'No\'),'),
('Ask for Company Name', 'CUSTOM_FORMS_INCLUDE_COMPANY', 'Yes', 'Ask for the customers company name?', @gid, 5, now(), now(), '', 'zen_cfg_select_option(array(\'Yes\', \'No\'),'),
('Require Company Name', 'CUSTOM_FORMS_REQUIRE_COMPANY', 'Yes', 'Is customers company name required?', @gid, 6, now(), now(), '', 'zen_cfg_select_option(array(\'Yes\', \'No\'),'),
('Ask for Customer Email', 'CUSTOM_FORMS_INCLUDE_EMAIL', 'Yes', 'Ask for the customers e-mail address?', @gid, 7, now(), now(), '', 'zen_cfg_select_option(array(\'Yes\', \'No\'),'),
('Require Customer Email', 'CUSTOM_FORMS_REQUIRE_EMAIL', 'Yes', 'Is customers email required?', @gid, 8, now(), now(), '', 'zen_cfg_select_option(array(\'Yes\', \'No\'),'),
('Ask for Customer Phone Number', 'CUSTOM_FORMS_INCLUDE_PHONE', 'Yes', 'Ask for the customers phone number?', @gid, 9, now(), now(), '', 'zen_cfg_select_option(array(\'Yes\', \'No\'),'),
('Require Customer Phone Number', 'CUSTOM_FORMS_REQUIRE_PHONE', 'No', 'Is customers phone number required?', @gid, 10, now(), now(), '', 'zen_cfg_select_option(array(\'Yes\', \'No\'),'),
('Number of Rows', 'CUSTOM_FORMS_NUMBER_ROWS', '25', 'Number of rows to display on the responses dashboard and built forms in Admin.', @gid, 11, now(), now(), '', ''),
('Widget Button Text', 'CUSTOM_FORMS_WIDGET_BUTTON_TEXT', 'Anpassen', 'Text that will display on the button tag created by the widget option in the form builder interface.', @gid, 12, now(), now(), '', ''),
('Text Char Limit', 'CUSTOM_FORMS_TEXT_MAX_CHAR', '32', 'Maximum number of characters allowed on text fields.', @gid, 13, now(), now(), '', '')");

$db->Execute("REPLACE INTO ".TABLE_CONFIGURATION_LANGUAGE." (configuration_title, configuration_key, configuration_description, configuration_language_id) VALUES
('Email Adressen', 'CUSTOM_FORMS_RECIPIENT_EMAILS', 'E-Mail-Adressen von Kundendienst- oder Verwaltungspersonal, das zur Beantwortung von Kundenanfragen, die mit Hilfe von benutzerdefinierten Formularen übermittelt wurden, zugewiesen wurde.', 43),
('Namen des Kunden erfragen?', 'CUSTOM_FORMS_INCLUDE_NAME', 'Soll nach dem Namen des Kunden gefragt werden?', 43),
('Name des Kunden Pflichtfeld?', 'CUSTOM_FORMS_REQUIRE_NAME','Soll der Name des Kunden ein Pflichtfeld sein?', 43),
('Firmenname erfragen?', 'CUSTOM_FORMS_INCLUDE_COMPANY', 'Soll nach dem Firmennamen des Kunden gefragt werden?', 43),
('Firmenname Pflichtfeld?', 'CUSTOM_FORMS_REQUIRE_COMPANY', 'Soll der Firmenname ein Pflichtfeld sein?', 43),
('Email des Kunden erfragen?', 'CUSTOM_FORMS_INCLUDE_EMAIL', 'Soll nach der Email des Kunden gefragt werden?', 43),
('Email Pflichtfeld?', 'CUSTOM_FORMS_REQUIRE_EMAIL', 'Soll die Email ein Pflichtfeld sein?', 43),
('Telefonnummer des Kunden erfragen?', 'CUSTOM_FORMS_INCLUDE_PHONE', 'Soll nach der Telefonnummer des Kunden gefragt werden?', 43),
('Telefonnummer Pflichtfeld?', 'CUSTOM_FORMS_REQUIRE_PHONE', 'Soll die Telefonnummer ein Pflichtfeld sein?', 43),
('Zeilenanzahl', 'CUSTOM_FORMS_NUMBER_ROWS', 'Anzahl der Zeilen, die im Antwort-Dashboard und in den erstellten Formularen im Admin angezeigt werden sollen.', 43),
('Button Text im Widget', 'CUSTOM_FORMS_WIDGET_BUTTON_TEXT','Text, der auf dem Button-Tag angezeigt wird, der von der Widget-Option in der Form Builder-Oberfläche erstellt wurde.', 43),
('Zeichenlimit für Textfelder', 'CUSTOM_FORMS_TEXT_MAX_CHAR','Maximal zulässige Anzahl von Zeichen in Textfeldern', 43)");


// delete old configuration/tools menu
$admin_page = 'configCustomForms';
$db->Execute("DELETE FROM " . TABLE_ADMIN_PAGES . " WHERE page_key = '" . $admin_page . "' LIMIT 1;");
$admin_page_tools = 'addonCustomFormDashboard';
$db->Execute("DELETE FROM " . TABLE_ADMIN_PAGES . " WHERE page_key = '" . $admin_page_tools . "' LIMIT 1;");
$admin_page_builder = 'addonCustomFormBuilder';
$db->Execute("DELETE FROM " . TABLE_ADMIN_PAGES . " WHERE page_key = '" . $admin_page_builder . "' LIMIT 1;");
// add configuration/tools menu
if (!zen_page_key_exists($admin_page)) {
$db->Execute(" SELECT @gid:=configuration_group_id
FROM ".TABLE_CONFIGURATION_GROUP."
WHERE configuration_group_title= 'Individuelle Kontaktformulare'
LIMIT 1;");
$db->Execute("INSERT IGNORE INTO " . TABLE_ADMIN_PAGES . " (page_key,language_key,main_page,page_params,menu_key,display_on_menu,sort_order) VALUES 
('configCustomForms','BOX_ADDON_CUSTOM_FORMS','FILENAME_CONFIGURATION',CONCAT('gID=',@gid),'configuration','Y',@gid)");
$db->Execute("INSERT IGNORE INTO " . TABLE_ADMIN_PAGES . " (page_key,language_key,main_page,page_params,menu_key,display_on_menu,sort_order) VALUES 
('addonCustomFormDashboard','BOX_ADDON_CUSTOM_FORM_DASHBOARD','FILENAME_ADDON_CUSTOM_FORM_DASHBOARD','','customers','Y',101)");
$db->Execute("INSERT IGNORE INTO " . TABLE_ADMIN_PAGES . " (page_key,language_key,main_page,page_params,menu_key,display_on_menu,sort_order) VALUES 
('addonCustomFormBuilder','BOX_ADDON_CUSTOM_FORM_BUILDER','FILENAME_ADDON_CUSTOM_FORM_BUILDER','','catalog','Y',101)");
$messageStack->add('Individuelle Kontaktformulare erfolgreich installiert.', 'success');  
}