<?php
/**
* Custom Forms plug-in
* @copyright Copyright 2003-2018 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @Author: Will Davies Vasconcelos <willvasconcelos@outlook.com>
* @Version: 1.1
* @Dev Start Date: Wednesday, May 30 2018
* @Dev End Date: Friday, June 15 2018
* @Last Update: Friday, July 6 2018
* @Tested on Zen Cart v1.5.5f $
*/
/* PLUGIN */
define('HEADING_TITLE','Custom Form Dashboard');

/* MAIN TABLE */
define('TBL_HEAD_COUNT', '#');
define('TBL_HEAD_REQUEST_ID', 'ID');
define('TBL_HEAD_NAME', 'Name');
define('TBL_HEAD_COMPANY', 'Company');
define('TBL_HEAD_PHONE', 'Phone');
define('TBL_HEAD_EMAIL', 'Email');
define('TBL_HEAD_ACCOUNT', 'Account');
define('TBL_HEAD_IP', 'IP');
define('TBL_HEAD_PLATFORM', 'Platform');
define('TBL_HEAD_IS_MOBILE', 'Mobile?');
define('TBL_HEAD_BROWSER', 'Browser');
define('TBL_HEAD_BROWSER_VERSION', 'Version');
define('TBL_HEAD_STATUS', 'Status');
define('TBL_HEAD_TIMESTAMP', 'Date/Time');
define('TBL_HEAD_ACTION', 'Action');
define('TBL_HEAD_USER_AGENT', 'User Agent');
define('TBL_HEAD_MESSAGE', 'Message');

/* BUTTONS */
define('BTN_ADD', 'Add');
define('BTN_EDIT', 'Edit');
define('BTN_DELETE', 'Delete');

/* LABELS */
define('LBL_YES', 'Yes');
define('LBL_NO', '-');

/* FEEDBACK MESSAGES */
define('INFO_NO_REQUEST', 'No requests available to display');
define('MSG_INSTALL_ERROR', 'There is a problem with this plugin. Please, contact technical support.');
define('MSG_REQUEST_UPDATED', 'Status Updated');
define('MSG_REQUEST_NOT_UPDATED', 'Unable to update status. Please, contact technical support.');
define('MSG_MISSING_REQUEST_ID_ERROR', 'Request ID missing.');
define('MSG_REQUEST_DELETED', 'Request deleted.');
define('MSG_REQUEST_NOT_DELETED', 'Request not deleted. Please, try again or contact technical support.');
/* RIGHT BOX */
define('INFO_HEAD_DISPLAY_REQUEST', 'Display Request');
define('INFO_HEAD_DELETE_REQUEST', 'Delete Request');
define('INFO_DELETE_REQUEST_CONFIRM', 'Are you sure you want to delete this request?');
define('INFO_EDIT_REQUEST', 'Edit Request');
define('INFO_FORM_STATUS_LABEL', 'Status');
define('INFO_HEAD_FIELDS_SECTION_TITLE', 'Title');

define('PAGINATION_LABEL','Displaying %s to %s (of %s items)');

define('JSON_LINE_BREAK_PLACEHOLDER', '|||');