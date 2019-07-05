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
class addon_custom_forms_dashboard{
	/* FORM RELATED INSTANCE VARIABLES */
	private $action = '';
	private $request_id = 0;
	private $form_id = 0;
	private $customer_name = '';
	private $customer_company = '';
	private $customer_phone = '';
	private $customer_email = '';
	private $account_id = '';
	private $remote_ip = '';
	private $platform = '';
	private $mobile = '';
	private $browser_name = '';
	private $browser_version = '';
	private $user_agent = '';
	private $message = '';
	private $status = '';
	private $message_timestamp = '';
	private $images_url = '';
	
	function __construct(){
		$this->InitVars();
	}
	
	/* PUBLIC METHODS */
	public function InitVars( $rID = 0 ){
		global $db;
		#FORM ID
		if( (int)$rID > 0 ){
			$this->SetRequestId( (int)$rID );
		}else if( isset($_GET['rID']) and (int)$_GET['rID'] > 0 ){
			$this->SetRequestId( (int)$_GET['rID'] );
		}else if( isset($_POST['rID']) and (int)$_POST['rID'] > 0 ){
			$this->SetRequestId( (int)$_POST['rID'] );
		}
		#ACTION
		if( isset($_GET['action']) ){
			$this->SetAction( $_GET['action'] );
		}else if( isset($_POST['action']) ){
			$this->SetAction( $_POST['action'] );
		}
		
		#LOAD VALUES FROM DATABASE
		if( $this->GetRequestId() > 0 ){
			$sql = "SELECT `form_id`,
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
					FROM `" . TABLE_CUSTOM_FORMS_REQUESTS . "`
					WHERE `request_id` = :requestID";
			$sql = $db->BindVars($sql, ':requestID', $this->GetRequestId(), 'integer');
			
			$rec = $db->Execute($sql);
			if( !$rec->EOF ){
				$this->SetFormId( $rec->fields['form_id'] );
				$this->SetCustomerName( $rec->fields['customer_name'] );
				$this->SetCustomerCompany( $rec->fields['customer_company'] );
				$this->SetCustomerPhone( $rec->fields['customer_phone'] );
				$this->SetCustomerEmail( $rec->fields['customer_email'] );
				$this->SetAccountId( $rec->fields['account_id'] );
				$this->SetRemoteIp( $rec->fields['remote_ip'] );
				$this->SetPlatform( $rec->fields['platform'] );
				$this->SetIsMobile( $rec->fields['mobile'] );
				$this->SetBrowserName( $rec->fields['browser_name'] );
				$this->SetBrowserVersion( $rec->fields['browser_version'] );
				$this->SetUserAgent( $rec->fields['user_agent'] );
				$this->SetMessage( $rec->fields['message'] );
				$this->SetStatus( $rec->fields['status'] );
				$this->SetMessageTimestamp( $rec->fields['message_timestamp'] );
			}
		}
		
		#LOAD IMAGES FOLDER URL
		$dirImages = substr(DIR_FS_CATALOG_IMAGES, strlen(DIR_FS_CATALOG) );
		if( strtolower(ENABLE_SSL_CATALOG) == 'true' ){
			$this->images_url = HTTPS_CATALOG_SERVER . DIR_WS_HTTPS_CATALOG . $dirImages . 'uploads/';
		}else{
			$this->images_url = HTTP_CATALOG_SERVER . DIR_WS_CATALOG . $dirImages . 'uploads/';
		}
	}
	
	public function GetForm( $action = '', $rID = 0, $ffID = 0, $oID = 0 ){
		/* BASED ON ACTION and REQUEST ID
		 * RETURN A FORMATTED ARRAY READY FOR THE CONTENTS[] RIGHT-BOX LOADER.
		 */
		$form = '';
		$form = zen_draw_form(
				'frmCustomForm', 
				FILENAME_ADDON_CUSTOM_FORM_DASHBOARD, 
				zen_get_all_get_params(array('action', 'rID'))
			);
		
		if( $action != '' ){
			$form .= zen_draw_hidden_field( 'action', $action );
		}
		
		if( (int)$rID > 0 ){
			$form .= zen_draw_hidden_field( 'rID', (int)$rID );
		}
			
		return array('form' => $form);
	}
	
	public function GetDisplayRequest(){
		$contents = array();
		#ID
		$contents[] = array('text' => zen_draw_label(
				TBL_HEAD_REQUEST_ID . ': ',
				'',
				'class="info-labels"'
			) . $this->GetRequestId()
		);
		#NAME
		$contents[] = array('text' => zen_draw_label(
				TBL_HEAD_NAME . ': ',
				'',
				'class="info-labels"'
			) . $this->GetCustomerName()
		);
		#COMPANY
		$contents[] = array('text' => zen_draw_label(
				TBL_HEAD_COMPANY . ': ',
				'',
				'class="info-labels"'
			) . $this->GetCustomerCompany()
		);
		#PHONE
		$contents[] = array('text' => zen_draw_label(
				TBL_HEAD_PHONE . ': ',
				'',
				'class="info-labels"'
			) . $this->GetCustomerPhone()
		);
		#EMAIL
		$contents[] = array('text' => zen_draw_label(
				TBL_HEAD_EMAIL . ': ',
				'',
				'class="info-labels"'
			) . $this->GetCustomerEmail()
		);
		#CUSTOMER NAME (IF LOGGED IN)
		$contents[] = array('text' => zen_draw_label(
				TBL_HEAD_ACCOUNT . ': ',
				'',
				'class="info-labels"'
			) . ($this->GetAccountId() > 0 ? zen_customers_name($this->GetAccountId()) : 'n/a')
		);
		#IP ADDRESS
		$contents[] = array('text' => zen_draw_label(
				TBL_HEAD_IP . ': ',
				'',
				'class="info-labels"'
			) . $this->GetRemoteIp()
		);
		#OPERATING SYSTEM
		$contents[] = array('text' => zen_draw_label(
				TBL_HEAD_PLATFORM . ': ',
				'',
				'class="info-labels"'
			) . $this->GetPlatform()
		);
		#IS MOBILE?
		$contents[] = array('text' => zen_draw_label(
				TBL_HEAD_IS_MOBILE . ': ',
				'',
				'class="info-labels"'
			) . ($this->GetIsMobile()>0?LBL_YES:LBL_NO)
		);
		#BROWSER NAME
		$contents[] = array('text' => zen_draw_label(
				TBL_HEAD_BROWSER . ': ',
				'',
				'class="info-labels"'
			) . $this->GetBrowserName()
		);
		#BROWSER VERSION
		$contents[] = array('text' => zen_draw_label(
				TBL_HEAD_BROWSER_VERSION . ': ',
				'',
				'class="info-labels"'
			) . $this->GetBrowserVersion()
		);
		#REQUEST STATUS
		$contents[] = array('text' => zen_draw_label(
				TBL_HEAD_STATUS . ': ',
				'',
				'class="info-labels"'
			) . $this->GetStatus()
		);
		#REQUEST DATE/TIME
		$contents[] = array('text' => zen_draw_label(
				TBL_HEAD_TIMESTAMP . ': ',
				'',
				'class="info-labels"'
			) . $this->GetMessageTimestamp()
		);
		#MESSAGE
		$contents[] = array('text' => zen_draw_label(
				TBL_HEAD_MESSAGE . ': ',
				'',
				'class="info-label-head"'
			) . $this->GetMessage()
		);
		#USER AGENT
		$contents[] = array('text' => zen_draw_label(
				TBL_HEAD_USER_AGENT . ': ',
				'',
				'class="info-labels"'
			)  . $this->GetUserAgent()
		);
		return $contents;
	}
	
	public function GetAvailableStatus(){
		$output = array();
		$status = array('', 'Received', 'Read', 'Processed');
		foreach($status as $s){
			$output[] = array(
				'id' => $s,
				'text' => $s
			);
		}
		return $output;
	}
	public function ActionUrl( $action = '', $rID = 0 ){
		/* BASED ON ACTION AND REQUEST ID
		 * RETURN A FORMATTED URL
		 */
		
		$pars = zen_get_all_get_params(array('action', 'rID'));
		
		if( $action != '' ){
			$pars = 'action=' . $action;
		}
		
		if( (int)$rID  > 0 ){
			$pars .= ( $pars != '' ? '&' : '' );
			$pars .= 'rID='  . $rID;
		}
			
		$url = zen_href_link(
				FILENAME_ADDON_CUSTOM_FORM_DASHBOARD, 
				$pars,
				'SSL'
			);
		
		return $url;
	}
	
	public function ProcessMessage( $msg ){
		/* CONVERT JSON BACK INTO A READABLE MESSAGE
		 */
		$message = '';
		$mArray = json_decode($msg);
		
		$file_uploads = array();
		if( is_array($mArray) ){
			foreach( $mArray as $m ){
				$mInfo = get_object_vars($m);
				foreach($mInfo as $label => $value){
					if( is_array($value) ){
						$tmp = $value;
						$value = '<ul>';
						foreach($tmp as $v){
							$value .= '<li>' . $this->GetTextFromOptionValue( $v ) . '</li>' . "\n";
						}
						$value .= '</ul>';
					}else{
						#LOAD UPLOADED FILE INFO INTO ARRAY
						if( isset($value->file_name) ){
							$file_uploads[] = array(
								'original' => $value->original,
								'file_name' => $value->file_name
							);
							$value = '<a href="' . $this->images_url . $value->file_name . '" target="_blank">' . $value->file_name . '</a>' . ' [' . $value->original . ']' . "\n";
						}else{
							$value = $this->GetTextFromOptionValue( $value );
						}
					}
					$message .= '<div class="myMessage">' . zen_draw_label(
							$label . ': ',
							'',
							'style="font-weight:bold;margin-right:10px;"'
						) . 
						str_replace(JSON_LINE_BREAK_PLACEHOLDER, '<br />', $value) . '</div>' . "\n";
				}
			}
		}
		
		return $message;
	}
	
	private function GetTextFromOptionValue( $optVal ){
		global $db;
		
		$optText = $optVal; #DEFAULT
		$sql = "SELECT f.`field_type`, fo.`field_text`
				FROM `" . TABLE_CUSTOM_FORMS_FIELDS . "` AS f 
					JOIN `" . TABLE_CUSTOM_FORMS_FIELDS_OPTIONS . "` AS fo
						ON f.`form_field_id` = fo.`form_field_id`
				WHERE f.`form_id` = :fID 
					AND fo.`field_value` = :fValue";
		$sql = $db->BindVars($sql, ':fID', $this->GetFormId(), 'integer');
		$sql = $db->BindVars($sql, ':fValue', $optVal, 'string');
		$rec = $db->Execute( $sql );
		if( !$rec->EOF ){
			if( in_array($rec->fields['field_type'], array('Dropdown', 'Radio', 'Checkbox') ) ){
				$optText = $rec->fields['field_text'];
			}
		}
		return $optText;
	}
	
	/********** DATABASE OPERATIONS **********/
	#UPDATE
	public function UpdateRequest( $post ){
		global $db, $messageStack;
		
		$error_message = '';
		if( !zen_not_null($post['cbxStatus']) ){
			$error_message = MSG_TITLE_REQUIRED_ERROR;
		}
		if( $error_message != '' ){
			$messageStack->add_session($error_message, 'error');
			zen_redirect( $this->ActionUrl() );
		}else{
			$sql = "UPDATE `" . TABLE_CUSTOM_FORMS_REQUESTS . "`
					SET `status` = :status
					WHERE `request_id` = :requestID";
			$sql = $db->BindVars($sql, ':status', $post['cbxStatus'], 'string');
			$sql = $db->BindVars($sql, ':requestID', $post['rID'], 'integer');
			$success = $db->Execute($sql);			
			if( $success ){
				$messageStack->add_session(MSG_REQUEST_UPDATED, 'success');
				zen_redirect( $this->ActionUrl('', $this->request_id) );
			}else{ #ERROR
				$messageStack->add_session(MSG_REQUEST_NOT_UPDATED, 'error');
				zen_redirect( $this->ActionUrl('', $this->request_id) );
			}
		}
	} //END: UPDATE REQUEST METHOD
	
	#DELETE
	public function DeleteRequest(){
		global $db, $messageStack;
		$error_message = '';
		if( !zen_not_null( $this->GetRequestId() ) ){
			$error_message = MSG_MISSING_REQUEST_ID_ERROR;
		}
		if( $error_message != '' ){
			$messageStack->add_session($error_message, 'error');
			zen_redirect( $this->ActionUrl() );
		}else{
			$sql = "DELETE FROM `" . TABLE_CUSTOM_FORMS_REQUESTS . "`
					WHERE `request_id` = :requestID";
			$sql = $db->BindVars($sql, ':requestID', $this->GetRequestId(), 'integer');
			$success = $db->Execute($sql);			
			if( $success ){
				$messageStack->add_session(MSG_REQUEST_DELETED, 'success');
				zen_redirect( $this->ActionUrl() );
			}else{ #ERROR
				$messageStack->add_session(MSG_REQUEST_NOT_DELETED, 'error');
				zen_redirect( $this->ActionUrl('', $this->request_id) );
			}
		}
	}
	
	/********** GETTER METHODS **********/
	public function GetRequestId(){
		return $this->request_id;
	}
	public function GetFormId(){
		return $this->form_id;
	}
	public function GetAction(){
		return $this->action;
	}
	public function GetCustomerName(){
		return $this->customer_name;
	}
	public function GetCustomerCompany(){
		return $this->customer_company;
	}
	public function GetCustomerPhone(){
		return $this->customer_phone;
	}
	public function GetCustomerEmail(){
		return $this->customer_email;
	}
	public function GetAccountId(){
		return $this->account_id;
	}
	public function GetRemoteIp(){
		return $this->remote_ip;
	}
	public function GetPlatform(){
		return $this->platform;
	}
	public function GetIsMobile(){
		return $this->mobile;
	}
	public function GetBrowserName(){
		return $this->browser_name;
	}
	public function GetBrowserVersion(){
		return $this->browser_version;
	}
	public function GetUserAgent(){
		return $this->user_agent;
	}
	public function GetMessage(){
		return $this->ProcessMessage( $this->message );
	}
	public function GetStatus(){
		return $this->status;
	}
	public function GetMessageTimestamp(){
		return $this->message_timestamp;
	}
	
	/**********  SETTER METHODS **********/
	public function SetRequestId( $id ){
		$this->request_id = $id;
	}
	public function SetFormId( $id ){
		$this->form_id = $id;
	}
	public function SetAction( $action ){
		$this->action = $action;
	}
	public function SetCustomerName( $name ){
		$this->customer_name = $name;
	}
	public function SetCustomerCompany( $company ){
		$this->customer_company = $company;
	}
	public function SetCustomerPhone( $phone ){
		$this->customer_phone = $phone;
	}
	public function SetCustomerEmail( $email ){
		$this->customer_email = $email;
	}
	public function SetAccountId( $id ){
		$this->account_id = $id;
	}
	public function SetRemoteIp( $ip ){
		$this->remote_ip = $ip;
	}
	public function SetPlatform( $platform ){
		$this->platform = $platform;
	}
	public function SetIsMobile( $mobile ){
		$this->mobile = $mobile;
	}
	public function SetBrowserName( $name ){
		$this->browser_name = $name;
	}
	public function SetBrowserVersion( $version ){
		$this->browser_version = $version;
	}
	public function SetUserAgent( $user_agent ){
		$this->user_agent = $user_agent;
	}
	public function SetMessage( $message ){
		$this->message = $message;
	}
	public function SetStatus( $status ){
		$this->status = $status;
	}
	public function SetMessageTimestamp( $timestamp ){
		$this->message_timestamp = $timestamp;
	}
} #END: CLASS
