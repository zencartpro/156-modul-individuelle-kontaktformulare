<?php
/**
 * Custom Forms plug-in - customer side.
 * Loaded automatically by index.php?main_page=custom_form
 * Displays custom product request page.
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @Author: Will Davies Vasconcelos <willvasconcelos@outlook.com>
 * @Version: 1.0
 * @Dev Start Date: Wednesday, May 30 2018
 * @Dev End Date:   Friday,    June 15 2018
 * @Tested on Zen Cart v1.5.5f $
 */
?>
	<link rel="stylesheet" type="text/css" media="all" href="<?php echo $template->get_template_dir('custom_form.css', DIR_WS_TEMPLATE, 'css', 'css') . '/custom_form.css'; ?>">
	<div class="centerColumn topMargin20" id="customProductDefault">
		<h1 id="pageTwoHeading"><?php echo $cf->GetPageHeading(); ?></h1>
		<div id="customProductContent" class="content">
			<?php require( $define_page ); ?>
			<?php echo $cf->GetDescription(); ?>
		</div>
		<?php 
			#LOAD FORM FROM FORM BUILDER PLUGIN
			echo $cf->GetForm();
		?>
	</div>
	<?php require( DIR_WS_MODULES . 'pages/' . $current_page_base . '/jscript_main.php' ); ?>