<?php
/**
* Custom Forms plug-in - customer side.
* Loaded automatically by index.php?main_page=custom_form
* Displays custom product request page.
* @copyright Copyright 2003-2018 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @Author: Will Davies Vasconcelos <willvasconcelos@outlook.com>
* @Version: 1.0
* @Dev Start Date: Wednesday, May 30 2018
* @Dev End Date:   Friday,    June 15 2018
* @Tested on Zen Cart v1.5.5f $
*/
require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

// include template specific file name defines
$define_page = zen_get_file_directory(DIR_WS_LANGUAGES . $_SESSION['language'] . '/html_includes/', FILENAME_DEFINE_CUSTOM_FORM, 'false');

require(DIR_WS_CLASSES . 'custom_form.php');
$cf = new custom_form();

$breadcrumb->add( $cf->GetNavbarTitle() );

define('META_TAG_TITLE', $cf->GetPageTitle() ); 