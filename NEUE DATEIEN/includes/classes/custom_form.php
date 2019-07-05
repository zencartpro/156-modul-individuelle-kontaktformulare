<?php
/**
 * Custom Forms plug-in - customer side.
 * Loaded automatically by index.php?main_page=custom_form
 * Displays custom form page.
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart-pro.at/license/2_0.txt GNU Public License V2.0
 * @Author: Will Davies Vasconcelos <willvasconcelos@outlook.com>
 * @Version: 1.1
 * @Dev Start Date: Wednesday, May 30 2018
 * @Dev End Date: Friday, June 15 2018
 * @Last Update: Friday, July 6 2018
 * @Version: 1.3.0
 * 2019-07-05 webchills
 */
 
class custom_form extends base{
	private $form_id = 0;
	private $form = '';
	private $action = '';
	private $required_fields = array();
	/* URL */
	private $main_page = '';
	private $page_url = '';
	/* LANGUAGE */
	private $form_title = '';
	private $description = '';
	private $page_title = '';
	private $page_heading = '';
	private $navbar_title = '';
	private $images_folder = '';
	private $images_url = '';
	
	/* MISC */
	private $acceptable_file_types = array('image/gif', 'image/jpg', 'image/png', 'image/jpeg', 'application/pdf');
	
	function __construct() {
		/* CALLED FROM THE PAGE'S HEADER FILE
		 */
		global $db;
		#LOAD INSTANCE VARIABLES
		if( isset($_GET['form_id']) and (int)$_GET['form_id'] > 0 ){
			$this->form_id = (int)$_GET['form_id'];
			$sql = "SELECT `form_title`, `page_title`, `page_heading`, `navbar_title`, `form_description`
					FROM `" . TABLE_CUSTOM_FORMS . "`
					WHERE `form_id` = :formID";
			$sql = $db->BindVars($sql, ':formID', zen_db_prepare_input($this->form_id), 'integer');
			$rec = $db->Execute( $sql );
			if( !$rec->EOF ){
				$this->form_title = $rec->fields['form_title'];
				$this->page_title = $rec->fields['page_title'];
				$this->page_heading = $rec->fields['page_heading'];
				$this->navbar_title = $rec->fields['navbar_title'];
				$this->description = $rec->fields['form_description'];
			}
		}
		if( isset($_POST['action']) ){
			$this->action = $_POST['action'];
		}else if( isset($_GET['action']) ){
			$this->action = $_GET['action'];
		}
		
		#CURRENT PAGE'S URL (WITHOUT ACTION)
		if( isset($_GET['main_page']) ){
			$this->main_page = $_GET['main_page'];
			$this->page_url = 'index.php?main_page=' . $_GET['main_page'];
			$parameters = zen_get_all_get_params(array('action'));
			if( strlen($parameters) > 3 ){
				$this->page_url .= '&' . trim($parameters, '&');
			}
		}
		
		#LOCATION OF IMAGES FOLDER
		$this->images_folder = DIR_FS_CATALOG . DIR_WS_IMAGES . 'uploads/';
		if( strtolower(ENABLE_SSL) == 'true' ){
			$this->images_url = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . DIR_WS_IMAGES . 'uploads/';
		}else{
			$this->images_url = HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES . 'uploads/';
		}
		
		
		#LOAD REQUIRED FIELDS ARRAY
		if( CUSTOM_FORMS_REQUIRE_NAME == 'Yes' ){
			array_push( $this->required_fields, 'txtCustomerName' );
		}
		if( CUSTOM_FORMS_REQUIRE_COMPANY == 'Yes' ){
			array_push( $this->required_fields, 'txtCustomerCompany' );
		}
		
		if( CUSTOM_FORMS_REQUIRE_EMAIL == 'Yes' ){
			array_push( $this->required_fields, 'txtCustomerEmail' );
		}
		if( CUSTOM_FORMS_REQUIRE_PHONE == 'Yes' ){
			array_push( $this->required_fields, 'txtCustomerPhone' );
		}
		$sql = "SELECT `field_name`
				FROM `" . TABLE_CUSTOM_FORMS_FIELDS . "`
				WHERE `required` = 1
					AND`form_id` = :formID
				ORDER BY `sort_order` ASC";
		$sql = $db->BindVars($sql, ':formID', zen_db_prepare_input($this->form_id), 'integer');
		$rec = $db->Execute($sql);
		while( !$rec->EOF ){
			array_push( $this->required_fields, $rec->fields['field_name'] );
			$rec->MoveNext();
		}
		if( $this->action == 'send_request' ){
			$this->ProcessSubmitForm();
		}
		$this->LogFormHits();
		$this->form = $this->CustomForm();
	}
	
	private function CustomForm(){
		global $db, $messageStack;
		
		$output = '';
		$customer = array(
			'name'		=> '',
			'company'	=> '',
			'email'		=> '',
			'phone'		=> ''
		);
		
		#PROCESS ACTION
		if( $this->action != '' ){
			switch( $this->action ){
				case 'request_confirmation':
					#SHOW REQUEST CONFIRMATION PAGE: NO FORM
					$output = $this->RequestConfirmationLoader();
					break;
				case 'send_request':
					#SHOW NOTHING, WILL REDIRECT: NO FORM
					break;
				case 'request_success':
					#SHOW SUCCESS PAGE: NO FORM
					$output  = '<h1>' . HEAD_SUCCESS . '</h1>' . "\n";
					$output .= $this->GetMessage( $this->main_page );
					// $output .= '<div id="requestSentSuccess">' . MESSAGE_FORM_SUBMITION_SUCCESS . '</div>' . "\n";
					$output .= '
			<div class="buttonRow back"><a href="' . $this->page_url . '"><span class="cssButton normal_button button  button_back" onmouseover="this.className=\'cssButtonHover normal_button button  button_back button_backHover\'" onmouseout="this.className=\'cssButton normal_button button  button_back\'">' . BTN_NEW_REQUEST . '</span></a></div>
		</form>' . "\n";
					break;
				default:
					#PRELOAD CUSTOMER ARRAY
					if( isset($_POST['txtCustomerCompany']) ){
						$customer['company'] = $_POST['txtCustomerCompany'];
					}
					if( isset($_POST['txtCustomerName']) ){
						$customer['name'] = $_POST['txtCustomerName'];
					}
					if( isset($_POST['txtCustomerEmail']) ){
						$customer['email'] = $_POST['txtCustomerEmail'];
					}
					if( isset($_POST['txtCustomerPhone']) ){
						$customer['phone'] = $_POST['txtCustomerPhone'];
					}
					#LOAD FORM
					$output = $this->FormLoader( $customer );
					break;
			}
		}else{ #ACTION NOT SET YET
			#PRE-LOAD CUSTOMER INFO IF POSSIBLE
			if( isset($_SESSION["customer_id"]) and (int)$_SESSION["customer_id"] > 0 ){
				$sql = "SELECT `customers_firstname`, `customers_lastname`, `customers_email_address`, `customers_telephone`
						FROM `" . TABLE_CUSTOMERS . "`
						WHERE `customers_id` = :customerID";
				$sql = $db->BindVars($sql, ':customerID', (int)$_SESSION["customer_id"], 'integer');
				$rec = $db->Execute($sql);
				if( !$rec->EOF ){
					$customer['name'] = trim($rec->fields['customers_firstname'] . ' ' . $rec->fields['customers_lastname']);
					$customer['email'] = $rec->fields['customers_email_address'];
					$customer['phone'] = $rec->fields['customers_telephone'];
				}
			}
			#LOAD FORM
			$output = $this->FormLoader( $customer );
		}
		
		return $output;
	}
	
	private function ProcessSubmitForm(){
		/* PROCESS FORM SUBMISSION
		 ***/
		global $db, $messageStack;
		
		#LOCAL VARIABLES
		$submission_processed = false;
		$name = '';
		$company = '';
		$email = '';
		$phone = '';
		$accountID = 0;
		$ip = '';
		$message = '';
		$email_text_customer = '';
		$email_html_customer = '';
		$email_text_admin = '';
		$email_html_admin = '';
		
		#LOAD VALUES
		$form_id = 0;
		if( isset($_POST['form_id']) ){
			$form_id = (int)$_POST['form_id'];
		}
		if( isset($_POST['txtCustomerName']) ){
			$name = $_POST['txtCustomerName'];
		}
		if( isset($_POST['txtCustomerCompany']) ){
			$company = $_POST['txtCustomerCompany'];
		}
		if( isset($_POST['txtCustomerPhone']) ){
			$phone = $_POST['txtCustomerPhone'];
		}
		if( isset($_POST['txtCustomerEmail']) ){
			$email = $_POST['txtCustomerEmail'];
		}
		if( isset($_SESSION['customer_id']) ){
			$accountID = (int)$_SESSION['customer_id'];
		}
		if( isset($_SESSION['customers_ip_address']) and $_SESSION['customers_ip_address'] != '' ){
			$ip = $_SESSION['customers_ip_address'];
		}else{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		$message = $this->CompileJsonFromPostedValues();
		
		$this->MoveTempFilesToUploaded();
		
		#ENSURE ALL REQUIRED FIELDS ARE SET
		if( count($this->required_fields) > 0 ){
			
			#RELOAD MESSAGE STACK
			foreach( $this->required_fields as $rf ){
				if( !isset( $_POST[$rf]) or $_POST[$rf] == '' ){
					$field_label = $this->GetFieldLabelFromFieldName( $rf );
					$messageStack->add_session($this->main_page,sprintf(MESSAGE_REQUIRED_FIELD_MISSING, $field_label), 'info');
				}
			}
		}
		
		if( $messageStack->size( $this->main_page ) == 0 ){ #NO REQUIRED FIELD PENDING
			#SAVE DATA TO THE DATABASE, PREP EMAILS
			$env = $this->GetUsersEnvironmentInfo();
			$sql = "INSERT INTO `" . TABLE_CUSTOM_FORMS_REQUESTS . "`
					(
						`form_id`,
						`customer_name`,
						`customer_company`,
						`customer_phone`,
						`customer_email`,
						`account_id`,
						`remote_ip`,
						
						`platform`,
						`mobile`, 
						`browser_name`, 
						`browser_version`, 
						`user_agent`,
						
						`message`,
						`status`,
						`message_timestamp`
					)VALUES(
						:formID,
						:cName,
						:cCompany,
						:cPhone,
						:cEmail,
						:cAccountID,
						:cIP,
						
						:platform,
						:mobile,
						:browserName,
						:browserVersion,
						:userAgent,
						
						:message,
						:status,
						:timestamp
					)";
			
			$email_text_admin = EMAIL_CONTACT_TITLE . "\n";
			$email_html_admin = '<h1>' . EMAIL_CONTACT_TITLE . '</h1>' . "\n";
			$email_html_admin .= '<table>' . "\n";
			/* CONTACT SECTION */
			#NAME
			$sql = $db->BindVars( $sql, ':formID', zen_db_prepare_input($form_id), 'integer' );
			$sql = $db->BindVars( $sql, ':cName', zen_db_prepare_input($name), 'string' );
			if( $name != '' ){
				$email_text_admin .= LABEL_CUSTOMER_NAME . ': ' . $name . "\n";
				$email_html_admin .= '<tr><th style="text-align:right;vertical-align:top;">' . LABEL_CUSTOMER_NAME . ':</th><td style="text-align:left;vertical-align:top;">' . $name . '</td></tr>' . "\n";
			}
			
			#COMPANY
			$sql = $db->BindVars( $sql, ':cCompany', zen_db_prepare_input($company), 'string' );
			if( $company != '' ){
				$email_text_admin .= LABEL_CUSTOMER_COMPANY . ': ' . $company . "\n";
				$email_html_admin .= '<tr><th style="text-align:right;vertical-align:top;">' . LABEL_CUSTOMER_COMPANY . ':</th><td style="text-align:left;vertical-align:top;">' . $company . '</td></tr>' . "\n";
			}
			
			#PHONE
			$sql = $db->BindVars( $sql, ':cPhone', zen_db_prepare_input($phone), 'string' );
			if( $phone != '' ){
				$email_text_admin .= LABEL_CUSTOMER_PHONE . ': ' . $phone . "\n";
				$email_html_admin .= '<tr><th style="text-align:right;vertical-align:top;">' . LABEL_CUSTOMER_PHONE . ':</th><td style="text-align:left;vertical-align:top;">' . $phone . '</td></tr>' . "\n";
			}
			
			#EMAIL
			$sql = $db->BindVars( $sql, ':cEmail', zen_db_prepare_input($email), 'string' );
			if( $email != '' ){
				$email_text_admin .= LABEL_CUSTOMER_EMAIL . ': ' . $email . "\n";
				$email_html_admin .= '<tr><th style="text-align:right;vertical-align:top;">' . LABEL_CUSTOMER_EMAIL . ':</th><td style="text-align:left;vertical-align:top;">' . $email . '</td></tr>' . "\n";
			}
			
			#ACCOUNT ID
			$sql = $db->BindVars( $sql, ':cAccountID', zen_db_prepare_input($accountID), 'integer' );
			if( (int)$accountID > 0 ){
				$email_text_admin .= LABEL_CUSTOMER_ID . ': ' . (int)$accountID . "\n";
				$email_html_admin .= '<tr><th style="text-align:right;vertical-align:top;">' . LABEL_CUSTOMER_ID . ':</th><td style="text-align:left;vertical-align:top;">' . (int)$accountID . '</td></tr>' . "\n";
			}
			
			#IP ADDRESS
			$sql = $db->BindVars( $sql, ':cIP', zen_db_prepare_input($ip), 'string' );
			if( $ip != '' ){
				$email_text_admin .= LABEL_IP_ADDRESS . ' ' . $ip . "\n";
				$email_html_admin .= '<tr><th style="text-align:right;vertical-align:top;">' . LABEL_IP_ADDRESS . ':</th><td style="text-align:left;vertical-align:top;">' . $ip . '</td></tr>' . "\n";
			}
			
			/* ENVIRONMENT VARIABLES */
			#PLATFORM
			$sql = $db->BindVars($sql, ':platform', zen_db_prepare_input($env['platform']), 'string');
			if( $env['platform'] != '' ){
				$email_text_admin .= LABEL_PLATFORM . ' ' . $env['platform'] . "\n";
				$email_html_admin .= '<tr><th style="text-align:right;vertical-align:top;">' . LABEL_PLATFORM . ':</th><td style="text-align:left;vertical-align:top;">' . $env['platform'] . '</td></tr>' . "\n";
			}
			#IS MOBILE?
			$sql = $db->BindVars($sql, ':mobile', zen_db_prepare_input($env['mobile']), 'integer');
			if( $env['mobile'] != '' ){
				$email_text_admin .= LABEL_IS_MOBILE . ' ' . ($env['mobile']==1?LABEL_YES:LABEL_NO) . "\n";
				$email_html_admin .= '<tr><th style="text-align:right;vertical-align:top;">' . LABEL_IS_MOBILE . ':</th><td style="text-align:left;vertical-align:top;">' . ($env['mobile']==1?LABEL_YES:LABEL_NO) . '</td></tr>' . "\n";
			}
			#BROWSER NAME
			$sql = $db->BindVars($sql, ':browserName', zen_db_prepare_input($env['browser_name']), 'string');
			if( $env['browser_name'] != '' ){
				$email_text_admin .= LABEL_BROWSER . ' ' . $env['browser_name'] . "\n";
				$email_html_admin .= '<tr><th style="text-align:right;vertical-align:top;">' . LABEL_BROWSER . ':</th><td style="text-align:left;vertical-align:top;">' . $env['browser_name'] . '</td></tr>' . "\n";
			}
			#BROWSER VERSION
			$sql = $db->BindVars($sql, ':browserVersion', zen_db_prepare_input($env['browser_version']), 'string');
			if( $env['browser_version'] != '' ){
				$email_text_admin .= LABEL_BROWSER_VERSION . ' ' . $env['browser_version'] . "\n";
				$email_html_admin .= '<tr><th style="text-align:right;vertical-align:top;">' . LABEL_BROWSER_VERSION . ':</th><td style="text-align:left;vertical-align:top;">' . $env['browser_version'] . '</td></tr>' . "\n";
			}
			#USER AGENT
			$sql = $db->BindVars($sql, ':userAgent', zen_db_prepare_input($env['user_agent']), 'string');
			if( $env['user_agent'] != '' ){
				$email_text_admin .= LABEL_USER_AGENT . ' ' . $env['user_agent'] . "\n";
				$email_html_admin .= '<tr><th style="text-align:right;vertical-align:top;">' . LABEL_USER_AGENT . ':</th><td style="text-align:left;vertical-align:top;">' . $env['user_agent'] . '</td></tr>' . "\n";
			}
			
			$email_html_admin .= '</table>' . "\n";
			
			/* MESSAGE SECTION */
			$email_text_customer .= "\n\n" . EMAIL_PRODUCT_DESCRIPTION_TITLE . "\n";
			$email_html_customer .= '<h1>' . EMAIL_PRODUCT_DESCRIPTION_TITLE . '</h1>' . "\n";
			$email_html_customer .= '<table>' . "\n";
			
			#MESSAGE
			$sql = $db->BindVars( $sql, ':message', zen_db_prepare_input($message), 'string' );
			$file_uploads = array();
			if( strlen($message) > 10 ){
				$message_array = json_decode($message);
				foreach($message_array as $elObj ){
					$elArray = (array)$elObj;
					$fLabel = key($elArray);
					$fValue = $elArray[key($elArray)];
					if( is_array($fValue) ){
						if( !isset($fValue['file_name']) ){ #SKIP FILE UPLOAD INFO
							$vList = '';
							foreach( $fValue as $val ){
								$vList .= $this->GetTextFromOptionValue( $val ) . "\n";
							}
							$fValue = $vList;
						}
					}else{
						if( isset($fValue->original) ){ #FILE UPLOAD
							#LOAD ARRAY TO DISPLAY LINKS ON EMAIL
							$file_uploads[] = array(
								'original' => $fValue->original,
								'file_name' => $fValue->file_name
							);
							#REPLACE VALUE
							$fValue = $fValue->original;
						}else{
							$fValue = $this->GetTextFromOptionValue( $fValue );
						}
					}
					
					#LOAD EMAIL
					$email_text_customer .= $fLabel . ":\n" . $this->PrepStringForTextEmail($fValue) . "\n\n";
					$email_html_customer .= '<tr><th style="text-align:right;vertical-align:top;">' . $fLabel . '</th><td style="text-align:left;vertical-align:top;">' . $this->PrepStringForHTMLEmail($fValue) . '</td></tr>' . "\n";
				}
			}
			
			#STATUS
			$sql = $db->BindVars( $sql, ':status', zen_db_prepare_input(DEFAULT_REQUEST_STATUS), 'string' );
			
			#TIME STAMP
			$sql = $db->BindVars( $sql, ':timestamp', zen_db_prepare_input(date('Y-m-d H:i:s')), 'string' );
			
			#FINALIZE EMAIL MESSAGES
			$email_html_customer .= '</table>';
			$email_html_admin .= $email_html_customer;
			$email_text_admin .= $email_text_customer;
			
			#ADD LINKS TO UPLOADED FILES
			if( count($file_uploads) > 0 ){
				$email_html_admin .= '<h2>' . EMAIL_FILE_UPLOAD_LINKS_TITLE . '</h2>';
				$email_text_admin .= "\n\n" . EMAIL_FILE_UPLOAD_LINKS_TITLE . "\n";
				$email_html_admin .= '<ul>' . "\n";
				foreach($file_uploads as $file){
					$email_html_admin .= '<li><a href="' . $this->images_url . $file['file_name'] . '">' . $file['original'] . '</a></li>' . "\n";
					$email_text_admin .= $this->images_url . $file['file_name'] . "\n";
				}
				$email_html_admin .= '</ul>' . "\n";
			}
			
			#SAVE MESSAGE INTO THE DATABASE
			$submission_processed = $db->Execute( $sql );
			$request_id = $db->Insert_ID();
			#SEND CUSTOMER AN EMAIL
			$from_email_name = STORE_NAME;
			$from_email_address = STORE_OWNER_EMAIL_ADDRESS;
			if( isset($_POST['txtCustomerEmail']) and strpos($_POST['txtCustomerEmail'], '@') > 0 ){
				$to_name = ( isset($_POST['txtCustomerName']) ? $_POST['txtCustomerName'] : '' );
				$to_address = $_POST['txtCustomerEmail'];
				
				#SEND EMAIL TO CUSTOMER
				@zen_mail($to_name, $to_address, EMAIL_SUBJECT_CUSTOMER, $email_text_customer, $from_email_name, $from_email_address, array('EMAIL_MESSAGE_HTML' => $email_html_customer) );
			}
			
			
			#NOTIFY ADMIN
			if( CUSTOM_FORMS_RECIPIENT_EMAILS != '' ){
				$email_subject .= sprintf(EMAIL_SUBJECT_ADMIN, $request_id);
				
				$admin_emails = explode(";", CUSTOM_FORMS_RECIPIENT_EMAILS);
				foreach( $admin_emails as $to_address ){
					@zen_mail('', $to_address, $email_subject, $email_text_admin, $from_email_name, $from_email_address, array('EMAIL_MESSAGE_HTML' => $email_html_admin) );
				}
			}
			
			#REDIRECT
			if( $submission_processed ){
				$messageStack->add_session('custom_form',MESSAGE_FORM_SUBMITION_SUCCESS, 'success');
				
				$redirect_url = 'index.php?main_page=' . $this->main_page . '&'. zen_get_all_get_params(array('main_page','action')) . '&action=request_success';
				
				#RELOAD THE SAME PAGE WITHOUT POST
				zen_redirect( $redirect_url );
			}else{
				$messageStack->add_session('custom_form',MESSAGE_FORM_SUBMITION_ERROR, 'error');
			}
		} #END: if message stack has content
	} #END: ProcessSubmitForm()
	
	private function FormLoader( $customer ){
		global $db, $messageStack, $template_dir;
		
		#DETERMINE TEXT SIZE, MAXLENGTH
		$size = CUSTOM_FORMS_TEXT_MAX_CHAR;
		$maxlen = CUSTOM_FORMS_TEXT_MAX_CHAR;
		if( $size > 32 ) $size = 32;
		#LOAD PARAMETERS
		if( $this->page_url != '' ){
			$frm = zen_draw_form(
					'custom_form',
					$this->page_url,
					'post',
					'enctype="multipart/form-data" onSubmit="return ValidateForm(this);"'
				);
		}
		$frm .= zen_draw_hidden_field('action', 'request_confirmation');
		$frm .= zen_draw_hidden_field('form_id', $this->form_id);
		
		$frm .= '<div id="customProductForm">' . "\n";
		
		$frm .= $this->GetMessage( $this->main_page );
		
		#CUSTOMER INFORMATION
		$show_contact_info = false;
		$cInfo = '<hr />' . "\n";
		$cInfo .= '<h1>' . HEAD_CUSTOMER_INFORMATION . '</h1>';
		$cInfo .= '<table>' . "\n";
		#COMPANY
		if( CUSTOM_FORMS_INCLUDE_COMPANY == 'Yes' or CUSTOM_FORMS_REQUIRE_COMPANY == 'Yes' ){
			$cInfo .= '
			<tr>
				<th>' . 
					zen_draw_label(LABEL_CUSTOMER_COMPANY . ': ', 'txtCustomerCompany') .
					( in_array('txtCustomerCompany', $this->required_fields) ? REQUIRED_FLAG : '' ) .
				'</th>
				<td>' . 
					zen_draw_input_field('txtCustomerCompany', $customer['company'], zen_set_field_length(TABLE_CUSTOM_FORMS_REQUESTS, 'customer_company', $maxlen)) .
				'</td>
			</tr>';
			$show_contact_info = true;
		}
		#CUSTOMER NAME
		if( CUSTOM_FORMS_INCLUDE_NAME == 'Yes' or CUSTOM_FORMS_REQUIRE_NAME == 'Yes' ){
			$cInfo .= '
			<tr>
				<th>' . 
					zen_draw_label(LABEL_CUSTOMER_NAME . ': ', 'txtCustomerName') .
					( in_array('txtCustomerName', $this->required_fields) ? REQUIRED_FLAG : '' ) .
				'</th>
				<td class="tblFormFields">' . 
					zen_draw_input_field('txtCustomerName', $customer['name'], zen_set_field_length(TABLE_CUSTOM_FORMS_REQUESTS, 'customer_name', $maxlen)) .
				'</td>
			</tr>';
			$show_contact_info = true;
		}
		#CUSTOMER'S E-MAIL
		if( CUSTOM_FORMS_INCLUDE_EMAIL == 'Yes' or CUSTOM_FORMS_REQUIRE_EMAIL == 'Yes' ){
			$cInfo .= '
			<tr>
				<th>' . 
					zen_draw_label(LABEL_CUSTOMER_EMAIL . ': ', 'txtCustomerEmail') . 
					( in_array('txtCustomerEmail', $this->required_fields) ? REQUIRED_FLAG : '' ) .
				'</th>
				<td>' . 
					zen_draw_input_field('txtCustomerEmail', $customer['email'], zen_set_field_length(TABLE_CUSTOM_FORMS_REQUESTS, 'customer_email', $maxlen)) . 
				'</td>
			</tr>';
			$show_contact_info = true;
		}
		#PHONE
		if( CUSTOM_FORMS_INCLUDE_PHONE == 'Yes' or CUSTOM_FORMS_REQUIRE_PHONE == 'Yes' ){
			$cInfo .= '
			<tr>
				<th>' . 
					zen_draw_label(LABEL_CUSTOMER_PHONE . ': ', 'txtCustomerPhone') .
					( in_array('txtCustomerPhone', $this->required_fields) ? REQUIRED_FLAG : '' ) .
				'</th>
				<td>' . 
					zen_draw_input_field('txtCustomerPhone', $customer['phone'], zen_set_field_length(TABLE_CUSTOM_FORMS_REQUESTS, 'customer_phone', $maxlen)) . 
				'</td>
			</tr>';
		$show_contact_info = true;
		}
		
		$cInfo .= '</table>' . "\n";
		
		if( $show_contact_info ){
			$frm .= $cInfo;
		}
		
		#CUSTOM FORM FIELDS
		$show_custom_form = false;
		$cForm = '<hr />' . "\n";
		$cForm .= '<h1>' . $this->form_title . '</h1>';
		
		$sql = "SELECT `form_field_id`, `field_type`, `field_name`, `label`, `description`, `required`
				FROM `" . TABLE_CUSTOM_FORMS_FIELDS . "`
				WHERE `form_id` = :formID
				ORDER BY `sort_order` ASC";
		$sql = $db->BindVars($sql, ':formID', zen_db_prepare_input($this->form_id), 'integer');
		$rec = $db->Execute($sql);
		if( !$rec->EOF ){
			$show_custom_form = true;
		}else{
			#CUSTOM FORM NOT DEFINED: SHOW AN ERROR MESSAGE AND REDIRECT
			$messageStack->add_session('header', MESSAGE_NO_CUSTOM_FORM, 'error');
			zen_redirect(zen_href_link(FILENAME_DEFAULT));
		}
		$cForm .= '<table>';
		while( !$rec->EOF ){
			#LOAD OPTIONS IF ANY
			$options = $this->GetFormFieldOptions( $rec->fields['form_field_id'] );
			
			#SKIP SELECTED FIELDS IF NO OPTIONS ARE AVAILABLE
			if( in_array($rec->fields['field_type'], array('Dropdown', 'Radio', 'Checkbox') ) and count($options) == 0 ){
				$rec->MoveNext();
				continue;
			}
			
			#LOAD POSTED VALUE IF ANY
			$postValue = '';
			if( isset( $_POST[$rec->fields['field_name']] ) ){
				$postValue = $_POST[$rec->fields['field_name']];
			}
			
			#REQUIRED?
			$required = false;
			if( $rec->fields['required'] == 1 ){
				$required = true;
				$this->required_fields[] = $rec->fields['field_name'];
			}
			
			#START: FIELD OUTPUT
			$field = '<tr>
				<th>
					' . zen_draw_label($rec->fields['label'] . ': ', $rec->fields['field_name']) .
					( $required ? REQUIRED_FLAG : '' ) . '
				</th>' . "\n";
			
			switch( $rec->fields['field_type'] ){
				case 'Dropdown':
					if( count($options) > 0 ){
						$values = array();
						$selected = '';
						foreach( $options as $option ){
							$values[] = array(
								'text'	=> $option['text'],
								'id'	=> $option['value']
							);
							#LOAD DEFAULT SELECTION
							if( $option['selected'] ){
								$selected = $option['value'];
							}
						}
						#OVERRIDE DEFAULT SELECTION
						if( $postValue != '' ){
							$selected = $postValue;
						}
						
						$field .= '
							<td class="tblFormFields">' .
								zen_draw_pull_down_menu($rec->fields['field_name'], $values, $selected, 'class="dd260 hasCustomSelect"') . 
							'</td>' . "\n";
					}
					break;
				case 'Text':
					$defaultText = '';
					if( $postValue != '' ){
						$defaultText = $postValue;
					}else if( isset($options[0]) ){
						$defaultText = $options[0]['value'];
					}
					$field .= '
						<td class="tblFormFields">' . 
							zen_draw_input_field($rec->fields['field_name'], $defaultText, 'size="' . $size . '" maxlength="' . $maxlen . '"', 'text') . 
						'</td>' . "\n";
					break;
				case 'Text Area':
					$defaultText = '';
					if( $postValue != '' ){
						$defaultText = $postValue;
					}else if( isset($options[0]) ){
						$defaultText = $options[0]['value'];
					}
					$field .= '
						<td class="tblFormFields">' . 
							zen_draw_textarea_field($rec->fields['field_name'], 30, 7, $defaultText,'style="margin:0;width:100%;"') . 
						'</td>' . "\n";
					break;
				case 'Radio':
					$field .= '<td class="tblFormFields">';
					if( count($options) > 0 ){
						$field .= '<fieldset>';
						$i = 0;
						foreach( $options as $option ){
							$checked = false;
							if( isset( $_POST[ $rec->fields['field_name'] ]) ){
								if( $_POST[ $rec->fields['field_name'] ] == $option['value'] ){
									$checked = true;
								}
							}else if( $option['selected'] == 1 ){
								$checked = true;
							}
							if( $i > 0 ){ $field .= '<br />'; }
							$i++;
							$field .= zen_draw_radio_field($rec->fields['field_name'], $option['value'], $checked) . ' ';
							$field .= zen_draw_label($option['text'], '') . "\n";
						}
						$field .= '</fieldset>';
					}
					$field .=  '</td>' . "\n";
					break;
				case 'Checkbox':
					$field .= '<td class="tblFormFields">';
					if( count($options) > 0 ){
						$field .= '<fieldset>';
						$i = 0;
						foreach( $options as $option ){
							$checked = false;
							if( isset($_POST[$rec->fields['field_name']]) ){
								foreach( $_POST[$rec->fields['field_name']] as $cbx ){
									if( $cbx == $option['value']){
										$checked = true;
									}
								}
							}else if( $option['selected'] == 1 ){
								$checked = true;
							}
							$field .= zen_draw_checkbox_field( $rec->fields['field_name'] . '[]', $option['value'], $checked ) . ' ';
							$field .= zen_draw_label($option['text'], '') . '<br />' . "\n";
						}
						$field .= '</fieldset>';
					}
					$field .=  '</td>' . "\n";
					break;
				case 'File':
					$required = false;
					if( $rec->fields['required'] == '1' ){
						$required = true;
					}
					$field .= '
						<td class="tblFormFields">' . 
							zen_draw_file_field( $rec->fields['field_name'] . '[]', $required ) .
						'</td>' . "\n";
					break;
				case 'Read Only':
					$field .= '
					<td class="tblFormFields">' . "\n";
					if( count($options) > 0 ){
						foreach( $options as $option ){
							$field .= '<p style="margin-top:0;"><strong>' . $option['text'] . '</strong><br />' . $option['value'] . '</p>' . "\n";
						}
					}
					$field .=  '</td>' . "\n";
					break;
				default:
					break;
			}
			
			#FIND QUESTION ICON
			
			if( $rec->fields['description'] != '' ){
				$field .= '<td class="tooltip">
							<span class="tooltipicon"><img src="images/customform-help.png" class="customformhelp"></span>
							<span class="tooltiptext">' . $rec->fields['description'] . '</span>
						</td>' . "\n";
			}else{
				$field .= '<td> </td>' . "\n";
			}
			
			$field .= '</tr>' . "\n";
			$cForm .= $field;
			$rec->MoveNext();
		}
		$cForm .= '</table>' . "\n";
		if( $show_custom_form ){
			$frm .= $cForm;
		}
		
		$frm .= '</div>' . "\n";
		
		#SEND / RETURN BUTTONS
		$frm .= '
			<div class="buttonRow forward"><input class="cssButton submit_button button  button_send" onmouseover="this.className=\'cssButtonHover  button_send button_sendHover\'" onmouseout="this.className=\'cssButton submit_button button  button_send\'" type="submit" value="'. BTN_SEND . '"></div>
			
			<div class="buttonRow back" onClick="window.history.back();return false;" style="cursor:pointer;"><span class="cssButton normal_button button  button_back" onmouseover="this.className=\'cssButtonHover normal_button button  button_back button_backHover\'" onmouseout="this.className=\'cssButton normal_button button  button_back\'">' . BTN_BACK . '</span></div>
		</form>' . "\n";
		
		return $frm;
	} #END: FORM LOADER METHOD
	
	private function RequestConfirmationLoader(){
		global $messageStack;
		
		/* START: ENSURE ALL REQUIRED FIELDS ARE SET */
		if( count($this->required_fields) > 0 ){
			#RELOAD MESSAGE STACK
			foreach( $this->required_fields as $rf ){
				if( !isset( $_POST[$rf]) or $_POST[$rf] == '' ){
					$field_label = $this->GetFieldLabelFromFieldName( $rf );
					$messageStack->add_session($this->main_page,sprintf(MESSAGE_REQUIRED_FIELD_MISSING, $field_label), 'info');
				}
			}
		}
		
		/* START: HANDLE FIILE UPLOADS */
		$file_uploads = array();
		if( isset($_FILES) ){
			if( is_array($_FILES) ){
				foreach($_FILES as $fieldName => $value ){
					if( $value["type"][0] != '' and !in_array($value["type"][0], $this->acceptable_file_types) ) {
						//ADD MESSAGE STACK
						$messageStack->add_session('custom_form',MESSAGE_FILE_TYPE_ERROR, 'error');
					}else if( $value['error'][0] == 'UPLOAD_ERROR_OK' ){
						$temp = $value["tmp_name"][0];
						$name = basename($value["name"][0]);
						$new_name = date("Ymd-His-") . rand(101,999) . '.' . pathinfo($value["name"][0], PATHINFO_EXTENSION);
						$file_uploads[] = array(
								'field'	=> $fieldName,
								'tmp'	=> $tmp,
								'name'	=> $name,
								'new_name' => $new_name
							);
						@move_uploaded_file( $temp, $this->images_folder . 'tmp/' . $new_name );
					}
				}
			}
		}
		/* END: HANDLE FIILE UPLOADS */
		
		if( $messageStack->size( $this->main_page ) > 0 ){
			$customer = array(
				'name'		=> '',
				'company'	=> '',
				'email'		=> '',
				'phone'		=> ''
			);
			if( isset($_POST['txtCustomerCompany']) ){
				$customer['company'] = $_POST['txtCustomerCompany'];
			}
			if( isset($_POST['txtCustomerName']) ){
				$customer['name'] = $_POST['txtCustomerName'];
			}
			if( isset($_POST['txtCustomerEmail']) ){
				$customer['email'] = $_POST['txtCustomerEmail'];
			}
			if( isset($_POST['txtCustomerPhone']) ){
				$customer['phone'] = $_POST['txtCustomerPhone'];
			}
			
			return $this->FormLoader( $customer );
		}
		/* END: ENSURE ALL REQUIRED FIELDS ARE SET */
		
		$output = '<h1>' . HEAD_CONFIRMATION . '</h1>' . "\n";
		
		if( $this->page_url != '' ){
			$output .= zen_draw_form(
				'custom_form',
				$this->page_url,
				'post',
				'enctype="multipart/form-data"'
			) .
			zen_draw_hidden_field('action', 'send_request') . 
			zen_draw_hidden_field('form_id', $this->form_id) . "\n";
		}
		
		$output .= '<table id="tblConfirm">' . "\n";
		$no_show = array('securityToken','action','form_id');
		$rowCount = 0;
		if( isset($_POST) and is_array($_POST) ){
			foreach( $_POST as $fieldName => $value ){
				if( !in_array($fieldName, $no_show) ){
					$output .= '<tr class="'.($rowCount%2==0?'evenRow':'oddRow').'">';
					$label = $this->GetFieldLabelFromFieldName( $fieldName );
					$output .= '<td>' . zen_draw_label($label, '') . '</td>' . "\n";
					if( is_array($value) ){
						$output .= '<td>' . "\n";
						foreach( $value as $v ){
							$output .= nl2br($this->GetTextFromOptionValue( $v )) . '<br />' . "\n";
							$output .= zen_draw_hidden_field($fieldName . '[]', $v) . "\n";
						}
						$output .= '</td>' . "\n";
					}else{
						$output .= '<td>' . nl2br($this->GetTextFromOptionValue( $value )) . '</td>' . "\n";
						$output .= zen_draw_hidden_field($fieldName, $value) . "\n";
					}
					$output .= '</tr>' . "\n";
				}
				$rowCount++;
			}
			#HANDLE FILE UPLOADS
			if( count($file_uploads) > 0 ){
				foreach( $file_uploads as $file ){
					$label = $this->GetFieldLabelFromFieldName( $file['field'] );
					$output .= '<tr class="'.($rowCount%2==0?'evenRow':'oddRow').'">';
					$output .= '<td>' . zen_draw_label( nl2br($this->GetTextFromOptionValue( $label )), '') . '</td>' . "\n";
					$output .= '<td>' . nl2br($this->GetTextFromOptionValue( $file['name'] )) . '</td>' . "\n";
					$output .= zen_draw_hidden_field('file_uploads[]', $file['field'] . '::' . $file['name'] . '::' . $file['new_name']) . "\n";
					$output .= '</tr>' . "\n";
					$rowCount++;
				}
			}
		}
		$output .= '</table>' . "\n";
		
		#SEND / RETURN BUTTONS
		$output .= '
			<div class="buttonRow forward"><input class="cssButton submit_button button  button_send" onmouseover="this.className=\'cssButtonHover  button_send button_sendHover\'" onmouseout="this.className=\'cssButton submit_button button  button_send\'" type="submit" value="'. BTN_SEND . '"></div>
			
			<div class="buttonRow back" onClick="window.history.back();return false;" style="cursor:pointer;"><span class="cssButton normal_button button  button_back" onmouseover="this.className=\'cssButtonHover normal_button button  button_back button_backHover\'" onmouseout="this.className=\'cssButton normal_button button  button_back\'">' . BTN_BACK . '</span></div>
		</form>' . "\n";
		
		return $output;
	}
	
	/* UTILITY METHODS - LIBRARY */
	private function GetTextFromOptionValue( $optVal ){
		global $db;
		
		$optText = $optVal; #DEFAULT
		$sql = "SELECT f.`field_type`, fo.`field_text`
				FROM `" . TABLE_CUSTOM_FORMS_FIELDS . "` AS f 
					JOIN `" . TABLE_CUSTOM_FORMS_FIELDS_OPTIONS . "` AS fo
						ON f.`form_field_id` = fo.`form_field_id`
				WHERE f.`form_id` = :fID 
					AND fo.`field_value` = :fValue";
		$sql = $db->BindVars($sql, ':fID', zen_db_prepare_input($this->form_id), 'integer');
		$sql = $db->BindVars($sql, ':fValue', zen_db_prepare_input($optVal), 'string');
		$rec = $db->Execute( $sql );
		if( !$rec->EOF ){
			if( in_array($rec->fields['field_type'], array('Dropdown', 'Radio', 'Checkbox') ) ){
				$optText = $rec->fields['field_text'];
			}
		}
		
		return $optText;
	}
	
	private function GetFieldLabelFromFieldName( $fieldName ){
		global $db;
		
		$fieldLabel = $fieldName; //default
		
		#STATIC VALUES
		switch ( $fieldName ){
			case 'txtCustomerCompany':
				$fieldLabel = LABEL_CUSTOMER_COMPANY;
				break;
			case 'txtCustomerName':
				$fieldLabel = LABEL_CUSTOMER_NAME;
				break;
			case 'txtCustomerEmail':
				$fieldLabel = LABEL_CUSTOMER_EMAIL;
				break;
			case 'txtCustomerPhone':
				$fieldLabel = LABEL_CUSTOMER_PHONE;
				break;
			default:
				if( $fieldName != '' ){
					$sql = "SELECT `label`
							FROM `" . TABLE_CUSTOM_FORMS_FIELDS . "`
							WHERE `form_id` = :formID
								AND `field_name` = :fieldName";
					$sql = $db->BindVars($sql, ':formID', zen_db_prepare_input($this->form_id), 'integer');
					$sql = $db->BindVars($sql, ':fieldName', zen_db_prepare_input($fieldName), 'string');
					$rec = $db->Execute($sql);
					if( !$rec->EOF ){
						$fieldLabel = $rec->fields['label'];
					}
				}
				break;
		}
		
		return $fieldLabel;
	}
	
	private function GetOptionText( $field_name, $field_value ){
		global $db;
		
		$value_label = $field_value; //DEFAULT
		
		$form_field_id = 0;
		//GET FORM FIELD ID
		$sql = "SELECT `field_type`, `form_field_id`
				FROM `" . TABLE_CUSTOM_FORMS_FIELDS . "`
				WHERE `form_id` = :formID
					AND `field_name` = :fieldName";
		$sql = $db->BindVars($sql, ':formID', zen_db_prepare_input($this->form_id), 'integer');
		$sql = $db->BindVars($sql, ':fieldName', zen_db_prepare_input($field_name), 'string');
		$rec = $db->Execute($sql);
		if( !$rec->EOF ){
			if( in_array($rec->fields['field_type'], array('Text Area','Text', 'File', 'Read Only')) ){
				return $value_label; //NO NEED TO TRANSLATE
			}
			$form_field_id = $rec->fields['form_field_id'];
		}
		
		if( $form_field_id > 0 ){
			//GET OPTION TEXT
			$sql = "SELECT `field_text`
					FROM `" . TABLE_CUSTOM_FORMS_FIELDS_OPTIONS . "`
					WHERE `form_field_id` = :formFieldID
						AND `field_value` = :fieldValue";
			$sql = $db->BindVars($sql, ':formFieldID', $form_field_id, 'integer');
			$sql = $db->BindVars($sql, ':fieldValue', zen_db_prepare_input($field_value), 'string');
			$rec = $db->Execute($sql);
			if( !$rec->EOF ){
				$value_label = $rec->fields['field_text'];
			}
		}
		
		return $value_label;
	}
	
	private function MoveTempFilesToUploaded(){
		/* MOVE FILES UPLOADED DURING REQUEST CONFIRMATION
		 * FROM TEMP TO THE UPLOADED FOLDER.
		 */
		foreach( $_POST as $fieldName => $fieldValue ){
			if( $fieldName == 'file_uploads' ){
				if( is_array($fieldValue) ){
					foreach( $fieldValue as $value ){
						$fInfo = explode("::", $value);
						@rename( $this->images_folder . 'tmp/' . $fInfo[2], $this->images_folder . $fInfo[2] );
					}
				}
			}
		}
	}
	
	private function CompileJsonFromPostedValues(){
		$message = array();
		$ignore_list = array('securityToken', 'action', 'form_id');
		foreach( $_POST as $fieldName => $fieldValue ){
			if( in_array($fieldName, $ignore_list) ){
				continue;
			}
			
			if( $fieldName == 'file_uploads' ){
				if( is_array($fieldValue) ){
					foreach( $fieldValue as $value ){
						$fInfo = explode("::", $value);
						/* $fInfo[0] = field name
						 * $fInfo[1] = original name
						 * $fInfo[2] = current name
						 */
						$field_label = $this->GetFieldLabelFromFieldName( $fInfo[0] );
						$message[] = array( $field_label => array( 'original' => $fInfo[1], 'file_name' => $fInfo[2] ) );
					}
				}
			}else{
				$field_label = $this->GetFieldLabelFromFieldName( $fieldName );
				if( is_array($fieldValue) ){ #e.g. Checkbox
					$value = array();
					foreach( $fieldValue as $val ){
						$tmp = $this->GetOptionText( $fieldName, $val );
						$value[] = $this->PrepStringForJson( $tmp );
					}
				}else{
					$tmp = $this->GetOptionText( $fieldName, $fieldValue );
					$value = $this->PrepStringForJson( $tmp );
				}
				
				$message[] = array( $field_label => $value );
			}
		}
		
		return json_encode( $message, JSON_UNESCAPED_UNICODE );
	}
	
	private function PrepStringForJson( $my_string ){
		$my_string = str_replace("\r\n", JSON_LINE_BREAK_PLACEHOLDER, $my_string);
		$my_string = str_replace("\n\r", JSON_LINE_BREAK_PLACEHOLDER, $my_string);
		$my_string = str_replace("\n", JSON_LINE_BREAK_PLACEHOLDER, $my_string);
		$my_string = str_replace("\r", JSON_LINE_BREAK_PLACEHOLDER, $my_string);
		$my_string = addcslashes( $my_string, '"');
		
		return $my_string;
	}
	
	private function PrepStringForTextEmail( $my_string ){
		$my_string = str_replace(JSON_LINE_BREAK_PLACEHOLDER, "\r\n", $my_string);
		
		return $my_string;
	}
	
	private function PrepStringForHTMLEmail( $my_string ){
		$my_string = nl2br($my_string);
		$my_string = str_replace(JSON_LINE_BREAK_PLACEHOLDER, "<br />\r\n", $my_string);
		
		return $my_string;
	}
	
	private function LogFormHits(){
		/* RECORD HITS TO PAGES WITH NOTIFY_custom_form_LOAD
		 * AND A FORM_ID GET PARAMETER.
		 */
		global $db;
		
		if( $this->form_id > 0 ){
			#DECLARE VARS WITH DEFAULT PARAMETERS
			$acountID = 0;
			$referer = '';
			
			#ACCOUNT ID (IF USER IS LOGGED IN)
			if( isset($_SESSION['customer_id']) ){
				$acountID = (int)$_SESSION['customer_id'];
			}
			#REFERING URL
			if( isset($_SERVER["HTTP_REFERER"]) ){
				$referer = $_SERVER["HTTP_REFERER"];
			}
			if( $referer != '' ){
				#DECLARE SQL WITH PLACEHOLDERS
				$sql = "INSERT INTO `" . TABLE_CUSTOM_FORMS_HITS . "` (
							`form_id`, 
							`account_id`, 
							`referer`, 
							`timestamp`
						)VALUES(
							:formID,
							:acountID,
							:referer,
							:timeStamp
						)";
				
				#BIND SQL TO VALUES
				$sql = $db->BindVars($sql, ':formID', zen_db_prepare_input($this->form_id), 'integer');
				$sql = $db->BindVars($sql, ':acountID', zen_db_prepare_input($acountID), 'integer');
				$sql = $db->BindVars($sql, ':referer', zen_db_prepare_input($referer), 'string');
				$sql = $db->BindVars($sql, ':timeStamp', zen_db_prepare_input(date('Y-m-d H:i:s')), 'string');
				
				#RUN SQL COMMAND
				$db->Execute($sql);
			}
		}
	}
	
	private function GetMessage( $class ){
		global $messageStack;
		ob_start();
		$messageStack->Output( $class );
		return ob_get_clean();
	}
	
	private function GetFormFieldOptions( $fieldID ){
		global $db;
		
		$options = array();
		$sql = "SELECT `field_text`, `field_value`, `selected`
				FROM `" . TABLE_CUSTOM_FORMS_FIELDS_OPTIONS . "`
				WHERE `form_field_id` = :fieldID
				ORDER BY `sort_order` ASC";
		$sql = $db->BindVars($sql, ':fieldID', zen_db_prepare_input($fieldID), 'integer');
		$rec = $db->Execute($sql);
		while( !$rec->EOF ){
			$options[] = array(
				'text' => $rec->fields['field_text'],
				'value' => $rec->fields['field_value'],
				'selected' => $rec->fields['selected']
			);
			$rec->MoveNext();
		}
		
		return $options;
	}
	
	private function GetUsersEnvironmentInfo(){
		#INSTANTIATE THE RETURN ARRAY
		$env = array(
				'browser_name'		=> '',
				'browser_version'	=> '',
				'platform'			=> '',
				'user_agent'		=> '',
				'mobile'			=> 0
			);
		
		if( isset($_SERVER['HTTP_USER_AGENT']) ){
			/* FULL ENVIRONMENT VARIABLE */
			$env['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
			
			/* BROWSER */
			$browsers = array('firefox', 'msie', 'opera', 'safari', 'chrome');
			$user_agent = strtolower( $env['user_agent'] );
			
			foreach( $browsers as $b ){
				if( preg_match("/($b)[\/ ]?([0-9.]*)/", $user_agent, $match) ){
					$env['browser_name'] = $match[1];
					$env['browser_version'] = $match[2];
					if( $match[1] == 'msie' ){
						$env['browser_name'] = "Internet Explorer";
					} else {
						$env['browser_name'] = ucfirst( $env['browser_name'] );
					}
					#TEST FOR IE10 AND LATER
					if( $match[1] == 'chrome'){
						if( preg_match("/(edge)[\/ ]?([0-9.]*)/i", $user_agent, $match)){
							$env['browser_name'] = ucfirst($match[1]);
							$env['browser_version'] = $match[2];
						}
					}
				}
			}
			
			if( $env['browser_name'] == '' ){
				$browsers = array('mozilla', 'seamonkey', 'konqueror', 'netscape', 'gecko', 'navigator', 'mosaic', 'lynx', 'amaya', 'omniweb', 'avant', 'camino', 'flock', 'aol');
				foreach( $browsers as $b ){
					if( preg_match("/($b)[\/ ]?([0-9.]*)/", $user_agent, $match) ){
						$env['browser_name'] = $match[1];
						$env['browser_version'] = $match[2];
					}
				}
			}
			
			/* OPERATING SYSTEM */
			$env['platform']=   "Unknown OS Platform";
			$os_array		=   array(
						'/windows NT 10.0/i'	=>	'Windows 10.0',
						'/windows Phone 10.0/i'	=>	'Windows 10.0',
						'/windows nt 6.3/i'     =>  'Windows 8.1',
						'/windows nt 6.2/i'     =>  'Windows 8.0',
						'/windows nt 6.1/i'     =>  'Windows 7.0',
						'/windows nt 6.0/i'     =>  'Windows Vista',
						'/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
						'/windows nt 5.1/i'     =>  'Windows XP',
						'/windows xp/i'         =>  'Windows XP',
						'/windows nt 5.0/i'     =>  'Windows 2000',
						'/windows me/i'         =>  'Windows ME',
						'/win98/i'              =>  'Windows 98',
						'/win95/i'              =>  'Windows 95',
						'/win16/i'              =>  'Windows 3.11',
						'/macintosh|mac os x/i' =>  'Mac OS X',
						'/mac_powerpc/i'        =>  'Mac OS 9',
						'/linux/i'              =>  'Linux',
						'/ubuntu/i'             =>  'Ubuntu',
						'/iphone/i'             =>  'iPhone',
						'/ipod/i'               =>  'iPod',
						'/ipad/i'               =>  'iPad',
						'/android/i'            =>  'Android',
						'/blackberry/i'         =>  'BlackBerry',
						'/webos/i'              =>  'Mobile'
					);
			foreach ($os_array as $regex => $value) { 
				if (preg_match($regex, $env['user_agent'])){
					$env['platform'] = $value;
				}
			}
			
			if( preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]) ){
				$env['mobile'] = 1;
			}
		}
		
		return $env;
	}
	
	/* PUBLIC GETTER METHODS */
	public function GetForm(){
		return $this->form;
	}
	
	public function GetFormtitle(){
		return $this->form_title;
	}
	
	public function GetDescription(){
		return $this->description;
	}
	
	public function GetPageTitle(){
		return $this->page_title;
	}
	
	public function GetPageHeading(){
		return $this->page_heading;
	}
	
	public function GetNavbarTitle(){
		return $this->navbar_title;
	}
	public function GetRequiredFields(){
		return $this->required_fields;
	}
}
?>