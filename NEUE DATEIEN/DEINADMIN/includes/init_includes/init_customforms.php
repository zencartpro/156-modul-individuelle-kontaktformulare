<?php
/**
 * @package Individuelle Kontaktformulare
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart-pro.at/license/2_0.txt GNU Public License V2.0
 * @version $Id: init_customforms.php 2019-07-05 16:34:51Z webchills $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// -----
// Erst loslegen, falls der Admin eingeloggt ist ...
//
if (empty($_SESSION['admin_id'])) {
    return;
}

$module_constant = 'CUSTOM_FORMS_VERSION'; 
$module_installer_directory = DIR_FS_ADMIN . 'includes/installers/customforms'; 
$module_name = "Individuelle Kontaktformulare"; 

$configuration_group_id = '';
if (defined($module_constant)) {
    $current_version = constant($module_constant);
} else {
    $current_version = "0.0.0";
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION_GROUP . " (configuration_group_title, configuration_group_description, sort_order, visible, language_id) VALUES ('" . $module_name . "', 'Einstellungen zum " . $module_name . " Modul', '60', '1', '43');");
    $configuration_group_id = $db->Insert_ID();

    $db->Execute("UPDATE " . TABLE_CONFIGURATION_GROUP . " SET sort_order = " . $configuration_group_id . " WHERE configuration_group_id = " . $configuration_group_id . ";");

    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES
                    ('Version', '" . $module_constant . "', '0.0.0', 'Version installed:', " . $configuration_group_id . ", 0, NOW(), NOW(), NULL, 'zen_cfg_read_only(');");
}
if ($configuration_group_id == '') {
    $config = $db->Execute("SELECT configuration_group_id FROM " . TABLE_CONFIGURATION . " WHERE configuration_key= '" . $module_constant . "'");
    $configuration_group_id = $config->fields['configuration_group_id'];
}

$installers = scandir($module_installer_directory, 1);

$newest_version = $installers[0];
$newest_version = substr($newest_version, 0, -4);

sort($installers);
if (version_compare($newest_version, $current_version) > 0) {
    foreach ($installers as $installer) {
        if (version_compare($newest_version, substr($installer, 0, -4)) >= 0 && version_compare($current_version, substr($installer, 0, -4)) < 0) {
            include($module_installer_directory . '/' . $installer);
            $current_version = str_replace("_", ".", substr($installer, 0, -4));
            $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '" . $current_version . "' WHERE configuration_key = '" . $module_constant . "' LIMIT 1;");
            $messageStack->add("Erfolgreich installiert: " . $module_name . " v" . $current_version, 'success');
        }
    }
}