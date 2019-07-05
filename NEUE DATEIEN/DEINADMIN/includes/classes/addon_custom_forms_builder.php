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
class addon_custom_forms_builder{
	/* FORM RELATED INSTANCE VARIABLES */
	private $action = '';
	private $id = 0;
	private $title = '';
	private $description = '';
	private $field_count = 0;
	private $hit_count = 0;
	private $created = '';
	private $creator = '';
	private $page_title = '';
	private $page_heading = '';
	private $navbar_title = '';
	
	/* FORM FIELDS INSTANCE VARIABLES */
	private $fields = array();
	
	/* OTHER */
	private $field_types = array();
	private $field_id = 0;
	private $option_id = 0;
	
	private $unavailable_field_attributes = array(
		'Dropdown' => array(),
		'Text' => array(),
		'Radio' => array(),
		'Checkbox' => array('required'),
		'File' => array('required'),
		'Read Only' => array('required')
	);
	
	function __construct(){
		$this->InitVars();
	}
	
	private function LoadFormData( $fID ){
		global $db, $sniffer;
		
		if( $sniffer->table_exists( TABLE_CUSTOM_FORMS ) ){
			$sql = "SELECT `page_title`, `page_heading`, `navbar_title`, `form_title`, `form_description`, `created_by`, `timestamp`
					FROM `" . TABLE_CUSTOM_FORMS . "`
					WHERE `form_id` = :formID";
			$sql = $db->BindVars($sql, ':formID', $fID, 'integer');
			$rec = $db->Execute( $sql );
			if( !$rec->EOF ){
				$this->SetPageTitle( $rec->fields['page_title'] );
				$this->SetPageHeading( $rec->fields['page_heading'] );
				$this->SetNavbarTitle( $rec->fields['navbar_title'] );
				$this->SetFormTitle( $rec->fields['form_title'] );
				$this->SetDescription( $rec->fields['form_description'] );
				$this->SetCreated( $rec->fields['timestamp'] );
				$this->SetCreator( $rec->fields['created_by'] );
			}
			$this->SetFieldCount( $this->FieldCount( $fID ) );
			$this->SetHitCount( $this->HitCount( $fID ) );
			$this->SetFields( $this->Fields( $fID ) );
		} 
	}
	
	private function Fields( $fID ){
		global $db, $sniffer;
		$fields = array();
		
		if( $sniffer->table_exists( TABLE_CUSTOM_FORMS_FIELDS ) ){
			$sql = "SELECT `form_field_id`, `field_type`, `field_name`, `label`, `description`, `required`, `sort_order`, `modified_by`, `timestamp`
					FROM `" . TABLE_CUSTOM_FORMS_FIELDS . "`
					WHERE `form_id` = :formID
					ORDER BY `sort_order` ASC";
			$sql = $db->BindVars($sql, ':formID', $fID, 'integer');
			$rec = $db->Execute( $sql );
			while( !$rec->EOF ){
				$fields[] = array(
					'id' => $rec->fields['form_field_id'],
					'type' => $rec->fields['field_type'],
					'name' => $rec->fields['field_name'],
					'label' => $rec->fields['label'],
					'description' => $rec->fields['description'],
					'required' => $rec->fields['required'],
					'sort_order' => $rec->fields['sort_order'],
					'modified_by' => $rec->fields['modified_by'],
					'timestamp' => $rec->fields['timestamp'],
					'options' => $this->FieldOptions( $rec->fields['form_field_id'] )
				);
				$rec->MoveNext();
			}
		}
		
		return $fields;
	}
	
	private function FieldOptions( $ffID ){
		global $db;
		
		$options = array();
		#CHECK IF FIELD HAS OPTIONS
		$sql = "SELECT `form_field_option_id`, `field_text`, `field_value`, `read_only`, `selected`, `sort_order`
				FROM `" . TABLE_CUSTOM_FORMS_FIELDS_OPTIONS . "`
				WHERE `form_field_id` = :ffID
				ORDER BY `sort_order` ASC";
		$sql = $db->BindVars($sql, ':ffID', $ffID, 'integer');
		$rec = $db->Execute($sql);
		while( !$rec->EOF ){
			$options[] = array(
				'id' => $rec->fields['form_field_option_id'],
				'field_id' => $ffID,
				'text' => $rec->fields['field_text'],
				'value' => $rec->fields['field_value'],
				'read_only' => $rec->fields['read_only'],
				'selected' => ((int)$rec->fields['selected'] > 0 ? true : false ),
				'sort_order'=> $rec->fields['sort_order']
			);
			$rec->MoveNext();
		}
		
		return $options;
	}
	
	private function AvailableFieldTypes(){
		global $db, $sniffer;
		$fields = array();
		
		if( $sniffer->table_exists( TABLE_CUSTOM_FORMS_FIELDS ) ){
			$sql = "SELECT SUBSTRING(COLUMN_TYPE,5) AS type
					FROM information_schema.COLUMNS
					WHERE TABLE_SCHEMA = '" . DB_DATABASE . "'
					AND TABLE_NAME = '" . TABLE_CUSTOM_FORMS_FIELDS . "'
					AND COLUMN_NAME='field_type'";
			$rec = $db->Execute( $sql );
			if( !$rec->EOF ){
				$options = trim($rec->fields['type'], "()");
				$options = str_replace( "'", "", $options );
				$field_types = explode( ",", $options );
				foreach( $field_types as $type ){
					$fields[] = array(
						'id'	=>	$type,
						'text'	=>	$type
					);
				}
			}
		}
		
		return $fields;
	}
	
	private function FieldNameValidator( $form_id, $field_name, $count = 1 ){
		/* GET THE FORM ID AND THE FORM FIELD AND RETURN
		 * A UNIQUE NAME WITHIN THE FORMS CONTEXT.
		 * NOTE: COUNT IS HERE TO BE USED BY THE RECURSIVE FUNCTION CALL
		 ***/
		global $db;
		
		$namePad = '';
		if( $count > 1 ) $namePad = $count;
		$new_name = $field_name . $namePad;
		$sql = "SELECT `form_field_id`
				FROM `" . TABLE_CUSTOM_FORMS_FIELDS . "`
				WHERE `form_id` = :formID
				AND `field_name` = :fieldName";
		$sql = $db->BindVars($sql, ':formID', $form_id, 'integer');
		$sql = $db->BindVars($sql, ':fieldName', $new_name, 'string');
		$rec = $db->Execute($sql);
		if( !$rec->EOF ){
			//NAME ALREADY EXISTS IN THE CURRENT CONTEXT
			$count++;
			$new_name = $this->FieldNameValidator( $form_id, $field_name, $count );
		}
		
		return $new_name;
	}
	
	private function OptionValueValidator( $field_id, $value, $count = 1 ){
		/* GET THE FORM FIELD ID AND THE OPTION VALUE AND RETURN
		 * A UNIQUE STRING WITHIN THAT FORM FIELD'S CONTEXT.
		 * NOTE: COUNT IS HERE TO BE USED BY THE RECURSIVE FUNCTION CALL
		 ***/
		global $db;
		
		$valuePad = '';
		if( $count > 1 ) $valuePad = $count;
		$new_value = $value . $valuePad;
		$sql = "SELECT `form_field_option_id`
				FROM `" . TABLE_CUSTOM_FORMS_FIELDS_OPTIONS . "`
				WHERE `form_field_id` = :fieldID
				AND `field_value` = :fieldValue";
		$sql = $db->BindVars($sql, ':fieldID', $field_id, 'integer');
		$sql = $db->BindVars($sql, ':fieldValue', $new_value, 'string');
		$rec = $db->Execute($sql);
		if( !$rec->EOF ){
			//NAME ALREADY EXISTS IN THE CURRENT CONTEXT
			$count++;
			$new_value = $this->OptionValueValidator( $field_id, $value, $count );
		}
		
		return $new_value;
	}
	
	/* PUBLIC METHODS */
	public function InitVars( $fID = 0 ){
		#FORM ID
		if( (int)$fID > 0 ){
			$this->SetFormId( (int)$fID );
		}else if( isset($_GET['fID']) and (int)$_GET['fID'] > 0 ){
			$this->SetFormId( (int)$_GET['fID'] );
		}else if( isset($_POST['fID']) and (int)$_POST['fID'] > 0 ){
			$this->SetFormId( (int)$_POST['fID'] );
		}
		#ACTION
		if( isset($_GET['action']) ){
			$this->SetAction( $_GET['action'] );
		}else if( isset($_POST['action']) ){
			$this->SetAction( $_POST['action'] );
		}
		#FIELD ID
		if( isset($_GET['ffID']) and (int)$_GET['ffID'] > 0 ){
			$this->SetFieldId( (int)$_GET['ffID'] );
		}else if( isset($_POST['ffID']) and (int)$_POST['ffID'] > 0 ){
			$this->SetFieldId( (int)$_POST['ffID'] );
		}
		#OPTION ID
		if( isset($_GET['oID']) and (int)$_GET['oID'] > 0 ){
			$this->SetOptionId( (int)$_GET['oID'] );
		}else if( isset($_POST['oID']) and (int)$_POST['oID'] > 0 ){
			$this->SetOptionId( (int)$_POST['oID'] );
		}
		#AVAILABLE FIELD TYPES
		$this->SetFieldTypes( $this->AvailableFieldTypes() );
		
		if( $this->GetFormId() > 0 ){
			$this->LoadFormData( $this->GetFormId() );
		}
	}
	
	public function FieldInfo( $ffID ){
		/* RETURNS AN ARRAY WITH INFORMATION FROM A FORM FIELD
		 */
		global $db;
		
		$fieldInfo = array();
		$sql = "SELECT `field_type`, `field_name`, `label`, `description`, `required`, `sort_order`, `modified_by`, `timestamp`
				FROM `" . TABLE_CUSTOM_FORMS_FIELDS . "`
				WHERE `form_field_id` = :ffID";
		$sql = $db->BindVars($sql, ':ffID', $ffID, 'integer');
		$rec = $db->Execute($sql);
		if( !$rec->EOF ){
			$fieldInfo = array(
				'id'			=> $ffID,
				'type'			=> $rec->fields['field_type'],
				'name'			=> $rec->fields['field_name'],
				'label'			=> $rec->fields['label'],
				'description'	=> $rec->fields['description'],
				'required'		=> $rec->fields['required'],
				'sort_order'	=> $rec->fields['sort_order'],
				'modified_by'	=> $rec->fields['modified_by'],
				'timestamp'		=> $rec->fields['timestamp'],
				'options'		=> $this->FieldOptions( $ffID )
			);
		}
		return $fieldInfo;
	}
	
	public function FieldOptionInfo( $oID ){
		global $db;
		
		$optionInfo = array();
		$sql = "SELECT `form_field_id`, `field_text`, `field_value`, `read_only`, `selected`, `sort_order`
				FROM `" . TABLE_CUSTOM_FORMS_FIELDS_OPTIONS . "`
				WHERE `form_field_option_id` = :oID";
		$sql = $db->BindVars($sql, ':oID', $oID, 'integer');
		
		$rec = $db->Execute($sql);
		if( !$rec->EOF ){
			$optionInfo = array(
				'id'		=> $oID,
				'field_id'	=> $rec->fields['form_field_id'],
				'text'		=> $rec->fields['field_text'],
				'value'		=> $rec->fields['field_value'],
				'read_only' => $rec->fields['read_only'],
				'selected'	=> $rec->fields['selected'],
				'sort_order'=> $rec->fields['sort_order']
			);
		}
		
		return $optionInfo;
	}
	
	public function FieldCount( $fID ){
		global $db, $sniffer;
		$field_count = 0;
		
		if( $sniffer->table_exists( TABLE_CUSTOM_FORMS_FIELDS ) ){
			$sql = "SELECT COUNT(*) AS count
					FROM `" . TABLE_CUSTOM_FORMS_FIELDS . "`
					WHERE `form_id` = :formID";
			$sql = $db->BindVars($sql, ':formID', $fID, 'integer');
			$rec = $db->Execute( $sql );
			if( !$rec->EOF ){
				$field_count = (int)$rec->fields['count'];
			}
		}
		
		return $field_count;
	}
	
	public function HitCount( $fID ){
		global $db, $sniffer;
		$hit_count = 0;
		
		if( $sniffer->table_exists( TABLE_CUSTOM_FORMS_HITS ) ){
			$sql = "SELECT COUNT(*) AS count
					FROM `" . TABLE_CUSTOM_FORMS_HITS . "`
					WHERE `form_id` = :formID";
			$sql = $db->BindVars($sql, ':formID', $fID, 'integer');
			$rec = $db->Execute( $sql );
			if( !$rec->EOF ){
				$hit_count = (int)$rec->fields['count'];
			}
		}
		
		return $hit_count;
	}
	
	public function GetThisFieldType( $ffID = 0 ){
		if( $ffID == 0 ) $ffID = $this->GetFieldId();
		$type = '';
		if( count( $this->GetFields() ) > 0 ){
			foreach( $this->GetFields() as $f ){
				if( $f['id'] == $ffID ){
					$type = $f['type'];
				}
			}
		}
		return $type;
	}
	
	/* DISPLAY ONLY */
	#FORM: CONTENT DISPLAY
	public function GetFormDisplayContents( $formID ){
		/* RECEIVES A FORM ID AND RETURNS
		 * A CONTENTS ARRAY CONTAINING THE REQUIRED
		 * PARTS TO DISPLAY THAT FORM INFO INTO
		 * THE RIGHT BOX.
		*/
		global $db;
		
		$contents = array();
		#TITLE
		$contents[] = array(
			'text' => zen_draw_label(
				INFO_HEAD_FORM_INFO,
				'',
				'class="info-label-head"'
			)
		);
		
		$sql = "SELECT `page_title`, `page_heading`, `navbar_title`, `form_title`, `created_by`, `timestamp`
				FROM `" . TABLE_CUSTOM_FORMS . "`
				WHERE `form_id` = :formID";
		$sql = $db->BindVars($sql, ':formID', $formID, 'integer');
		$rec = $db->Execute( $sql );
		if( $rec->EOF ){
			$contents[] = array(
				'text' => zen_draw_label(
					INFO_NO_FORM, 
					''
				)
			);
		}else{
			#FORM ID
			$contents[] = array(
				'text' => zen_draw_label(
					INFO_FORM_ID_LABEL, 
					'', 
					'class="info-labels"'
				) . 
				$formID
			);
			#PAGE TITLE 
			$contents[] = array(
				'text' => zen_draw_label(
					INFO_PAGE_TITLE_LABEL, 
					'', 
					'class="info-labels"'
				) . 
				$rec->fields['page_title']
			);
			
			#PAGE HEADING 
			$contents[] = array(
				'text' => zen_draw_label(
					INFO_PAGE_HEADER_LABEL, 
					'', 
					'class="info-labels"'
				) . 
				$rec->fields['page_heading']
			);
			
			#NAVBAR TITLE
			$contents[] = array(
				'text' => zen_draw_label(
					INFO_NAVBAR_TITLE_LABEL, 
					'', 
					'class="info-labels"'
				) . 
				$rec->fields['navbar_title']
			);
			
			#FORM TITLE
			$contents[] = array(
				'text' => zen_draw_label(
					INFO_FORM_TITLE_LABEL, 
					'', 
					'class="info-labels"'
				) . 
				$rec->fields['form_title']
			);
			#CREATOR
			$contents[] = array(
				'text' => zen_draw_label(
					INFO_FORM_CREATOR_LABEL, 
					'', 
					'class="info-labels"'
				) . 
				$this->GetCreatorNameFromId( $rec->fields['created_by'] )
			);
			#CREATED
			$contents[] = array(
				'text' => zen_draw_label(
					INFO_FORM_CREATED_LABEL, 
					'', 
					'class="info-labels"'
				) . 
				date("m/d/y", strtotime($rec->fields['timestamp']))
			);
			#FIELD COUNT
			$contents[] = array(
				'text' => zen_draw_label(
					INFO_FORM_FIELDS_COUNTER_LABEL, 
					'', 
					'class="info-labels"'
				) . 
				$this->FieldCount( $formID )
			);
			#HIT COUNT
			$contents[] = array(
				'text' => zen_draw_label(
					INFO_FORM_HITS_COUNTER_LABEL, 
					'', 
					'class="info-labels"'
				) . 
				$this->HitCount( $formID )
			);
			
			#PAGE LINK
			$contents[] = array(
				'text' => zen_draw_label(
					INFO_FORM_PAGE_LINK_LABEL, 
					'', 
					'class="info-labels"'
				) . 
				'<div id="txtLink"><a href="'.$this->GetWidgetLink( $this->id ).'" target="_blank">' . $this->GetWidgetLink( $this->id ) . '</a></div>'
			);
		}
		
		return $contents;
	} //END: GET FORM CONTENT METHOD
	
	#FORM FIELD: CONTENT DISPLAY
	public function GetFormFieldDisplayContents( $ffID ){
		/* RECEIVES A FORM FIELD ID AND RETURNS
		 * A CONTENTS ARRAY CONTAINING THE REQUIRE
		 * PARTS TO DISPLAY THAT FORM FIELD INFO INTO
		 * THE RIGHT BOX.
		*/
		$contents = array();
		$field = array();
		if( (int)$ffID > 0 ){
			$field = $this->FieldInfo( $ffID );
		}
		if( count($field) > 0 ){
			#TITLE
			$contents[] = array(
				'text' => zen_draw_label(
					INFO_HEAD_SELECTED_FIELD_TITLE,
					'',
					'class="info-label-head"'
				)
			);
			
			#ID
			$contents[] = array(
				'text' => zen_draw_label(
					INFO_FIELD_ID_LABEL, 
					'', 
					'class="info-labels"'
				) . 
				$field['id']
			);
			
			#TYPE
			$contents[] = array(
				'text' => zen_draw_label(
					INFO_FIELD_TYPE_LABEL, 
					'', 
					'class="info-labels"'
				) . 
				$field['type']
			);
			
			#NAME
			$contents[] = array(
				'text' => zen_draw_label(
					INFO_FIELD_NAME_LABEL, 
					'', 
					'class="info-labels"'
				) . 
				$field['name']
			);
			
			#LABEL
			$contents[] = array(
				'text' => zen_draw_label(
					INFO_FIELD_LABEL_LABEL, 
					'', 
					'class="info-labels"'
				) . 
				$field['label']
			);
			
			#DESCRIPTION
			$contents[] = array(
				'text' => zen_draw_label(
					INFO_FIELD_DESCRIPTION_LABEL, 
					'', 
					'class="info-labels"'
				) . 
				$field['description']
			);
			
			#REQUIRED
			$contents[] = array(
				'text' => zen_draw_label(
					INFO_FIELD_REQUIRED_LABEL, 
					'', 
					'class="info-labels"'
				) . 
				( $field['required'] == 1 ? LBL_YES : LBL_NO )
			);
		}
		
		return $contents;
	} //END: GET FIELD CONTENT METHOD
	
	#FORM FIELD OPTION: CONTENT DISPLAY
	public function GetOptionsListDisplayContents( $field ){
		/* RECEIVES THE ENTIRE FIELD ARRAY
		 * RETURNS A DISPLAY READY STRING.
		*/
		
		$options = $field['options'];
		$columns_count = 0;
		$output = 
			zen_draw_label( 
				INFO_OPTION_OPTIONS_LABEL,
				'',
				'style="margin:10px 0 5px 10px;font-weight:bold;"'
			);
		$show = $this->GetWhatToShowFromField( $field['type'] );
		$output .= '<table class="table-listing-display">
				<tr>' . "\n";
		if( $show['text'] ){
			$output .= '<th>' . INFO_OPTION_TEXT_LABEL . '</th>' . "\n";
			$columns_count++;
		}
		if( $show['value'] ){
			$output .= '<th>' . INFO_OPTION_VALUE_LABEL . '</th>' . "\n";
			$columns_count++;
		}
		if( $show['sort_order'] ){
			$output .= '<th>' . INFO_OPTION_SORT_LABEL . '</th>';
			$columns_count++;
		}
		if( $show['read_only'] ){
			$output .= '<th>' . INFO_OPTION_READ_ONLY_LABEL . '</th>';
			$columns_count++;
		}
		
		if( $show['selected'] ){
			$output .= '<th>' . INFO_OPTION_SELECTED_LABEL . '</th>' . "\n";
			$columns_count++;
		}
		
		$output .= '<th width=70>' . INFO_OPTION_ACTIONS_LABEL . '</th>
				</tr>' . "\n";
		$columns_count++;
		
		if( is_array($options) and count($options) > 0 ){
			foreach( $options as $option ){
				$output .= '<tr>' . "\n";
				
				if( $show['text'] ){
					$output .= '<td>' . $option['text'] . '</td>' . "\n";
				}
				
				if( $show['value'] ){
					$value = str_replace(JSON_LINE_BREAK_PLACEHOLDER, "<br />", $option['value']);
					$output .= '<td>' . $value . '</td>' . "\n";
				}
				if( $show['sort_order'] ){
					$output .= '<td>' . $option['sort_order'] . '</td>' . "\n";
				}
				if( $show['read_only'] ){
					$output .= '<td>' . ($option['read_only']==1? LBL_YES : LBL_NO ) . '</td>' . "\n";
				}
				if( $show['selected'] ){
					$output .= '<td>' . ($option['selected']>0?LBL_YES:LBL_NO) . '</td>' . "\n";
				}
				$output .= '<td>
						<a href="' . $this->GetActionUrl('deleteOptionConfirm', $this->GetFormId(), $option['field_id'], $option['id']) . '">' .
							zen_image(DIR_WS_IMAGES . 'icon_delete.gif', ICON_DELETE) . 
						'</a>
						<a href="' . $this->GetActionUrl('editOption', $this->GetFormId(), $option['field_id'], $option['id']) . '">' .
							zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . 
						'</a>
					</td>
				</tr>' . "\n";
			}
		}
		#ADD ADD BUTTON?
		$add_add_btn = false;
		#FIELDS WITH UNLIMITED OPTIONS
		if( in_array($field['type'], array('Dropdown', 'Checkbox', 'Radio','Read Only')) ){
			$add_add_btn = true;
		}
		#FILDS WITH ONLY ONE OPTION
		if( in_array($field['type'], array('Text', 'Text Area')) and is_array($options) and count($options) == 0 ){
			$add_add_btn = true;
		}
		#NOTE: NO OPTIONS FOR File TYPE
		
		if( $add_add_btn ){
			$output .= '<tr>
					<td colspan=' . $columns_count . '>
						<a href="' . $this->GetActionUrl('addOption', $this->GetFormId(), $field['id']) . '">' .
							zen_image(DIR_WS_IMAGES . 'icon_add.gif', BTN_ADD) . 
						'</a>
					</td>
				</tr>' . "\n";
		}
		
		$output .= '</table>' . "\n";
		
		return $output;
	} //END: GET FIELD CONTENT METHOD
	
	#FORM FIELD OPTION: CONTENT DISPLAY
	public function GetFormFieldOptionDisplayContents( $oID ){
		/* RECEIVES A FORM FIELD OPTION ID AND RETURNS
		 * A CONTENTS ARRAY CONTAINING THE REQUIRED
		 * PARTS TO DISPLAY THAT FORM FIELD INFO INTO
		 * THE BOX ON THE RIGHT-SIDE OF THE UI.
		*/
		global $db;
		
		$contents = array();
		if( (int)$oID > 0 ){
			$sql = "SELECT `form_field_id`, `field_text`, `field_value`, `selected`
					FROM `" . TABLE_CUSTOM_FORMS_FIELDS_OPTIONS . "`
					WHERE `form_field_option_id` = :ffoID
					ORDER BY `sort_order` ASC";
			$sql = $db->BindVars($sql, ':ffoID', $oID, 'integer');
			$rec = $db->Execute( $sql );
			if( !$rec->EOF ){
				$contents[] = array(
						'text' => zen_draw_label(
							INFO_HEAD_SELECTED_OPTION_TITLE, 
							'', 
							'class="info-label-head"'
					)
				);
				#ID
				$contents[] = array(
					'text' => zen_draw_hidden_field(
						'oID', 
						$oID
					) .
					zen_draw_label(
						INFO_OPTION_ID, 
						'', 
						'class="info-labels"'
					) . $oID
				);
				#TEXT
				$contents[] = array(
					'text' => zen_draw_label(
						INFO_OPTION_TEXT_LABEL, 
						'', 
						'class="info-labels"'
					) . $rec->fields['field_text']
				);
				#VALUE
				$contents[] = array(
					'text' => zen_draw_label(
						INFO_OPTION_VALUE_LABEL, 
						'', 
						'class="info-labels"'
					) . $rec->fields['field_value']
				);
				#SELECTED
				$contents[] = array(
					'text' => zen_draw_label(
						INFO_OPTION_SELECTED_LABEL, 
						'', 
						'class="info-labels"'
					) . ($rec->fields['selected'] > 0 ? LBL_YES : LBL_NO )
				);
				#SORT ORDER
				$contents[] = array(
					'text' => zen_draw_label(
						INFO_OPTION_ORDER_LABEL, 
						'', 
						'class="info-labels"'
					) . $rec->fields['sort_order']
				);
			}
		}
		
		return $contents;
	} //END: GET FIELD CONTENT METHOD
	
	public function GetForm( $action = '', $fID = 0, $ffID = 0, $oID = 0 ){
		/* BASED ON ACTION, FORM ID, FIELD ID, AND OPTION ID PARAMETERS
		 * RETURN A FORMATTED ARRAY READY FOR THE CONTENTS[] RIGHT-BOX LOADER.
		 */
		$form = '';
		$form = zen_draw_form(
				'frmCustomProductForm', 
				FILENAME_ADDON_CUSTOM_FORM_BUILDER, 
				zen_get_all_get_params(array('action', 'fID', 'ffID', 'oID'))
			);
		
		if( $action != '' ){
			$form .= zen_draw_hidden_field( 'action', $action );
		}
		
		if( (int)$fID > 0 ){
			$form .= zen_draw_hidden_field( 'fID', (int)$fID );
			if( (int)$ffID > 0 ){
				$form .= zen_draw_hidden_field('ffID', (int)$ffID);
				if( (int)$oID > 0 ){
					$form .= zen_draw_hidden_field('oID', (int)$oID);
				}
			}
		}
			
		return array('form' => $form);
	}
	
	public function GetActionUrl( $action = '', $fID = 0, $ffID = 0, $oID = 0 ){
		/* BASED ON ACTION, FORM ID, FIELD ID, AND OPTION ID PARAMETERS
		 * RETURN A FORMATTED URL TO BE USED IN BUTTONS AND LINKS.
		 */
		
		$pars = zen_get_all_get_params(array('action', 'fID', 'ffID', 'oID'));
		
		if( $action != '' ){
			$pars = 'action=' . $action;
		}
		
		if( (int)$fID  > 0 ){
			$pars .= ( $pars != '' ? '&' : '' );
			$pars .= 'fID='  . $fID;
			if( (int)$ffID > 0 ){
				$pars .= '&ffID=' . $ffID;
				if( (int)$oID  > 0 ){
					$pars .= '&oID='  . $oID;
				}
			}
		}
			
		$url = zen_href_link(
				FILENAME_ADDON_CUSTOM_FORM_BUILDER, 
				$pars,
				'SSL'
			);
		
		return $url;
	}
	
	public function GetWhatToShowFromField( $type ){
		/* FIELD-TYPE BASED OPTIONS */
		$what_to_show_array = array(
			'text' => false,
			'value' => false,
			'read_only' => false,
			'selected' => false,
			'sort_order' => false
		);
		switch( $type ){
			case 'Dropdown':
				$what_to_show_array['read_only'] = true;
			case 'Radio':
			case 'Checkbox':
				$what_to_show_array['text'] = true;
				//$what_to_show_array['value'] = true;
				$what_to_show_array['selected'] = true;
				$what_to_show_array['sort_order'] = true;
				break;
			case 'Text':
			case 'Text Area':
				$what_to_show_array['value'] = true;
				break;
			case 'Read Only':
				$what_to_show_array['text'] = true;
				$what_to_show_array['value'] = true;
				$what_to_show_array['sort_order'] = true;
				break;
			case 'File':
			default:
				break;
		}
		return $what_to_show_array;
	}
	
	public function GetHitsReport( $fID ){
		global $db;
		
		#GET HIT REFERALS
		$report = '';
		$sql = "SELECT DISTINCT `referer`, COUNT(*) as count, MAX(`timestamp`) as time
				FROM `" . TABLE_CUSTOM_FORMS_HITS . "`
				WHERE `form_id` = :formID
				GROUP BY `referer`";
		$sql = $db->BindVars($sql, ':formID', $fID, 'integer');
		$rec = $db->Execute( $sql );
		$cnt = 0;
		if( !$rec->EOF ){
			$report = '<table id="tblReport">';
			$report .= '<tr><th>' . REPORT_HITS_REFERER . '</th><th>' . REPORT_HITS_COUNT . '</th><th>'.REPORT_HITS_LAST_TIME.'</th></tr>';
			
			while( !$rec->EOF ){
				$report .= '<tr'.($cnt%2==0?' class="evenRow"':' class="oddRow"').'><td>' . $rec->fields['referer'] . '</td><td>' . $rec->fields['count'] . '</td><td>' . date("m/d/y", strtotime($rec->fields['time'])) .'</td></tr>';
				$cnt++;
				$rec->MoveNext();
			}
			$report .= '</table>';
		}
		
		return $report;
	}
	
	public function GetWidget( $fID ){
		$url = $this->GetWidgetLink( $fID );
		
		$widget = '<div class="buttonRow forward"><a href="' . $url . '"><input class="cssButton submit_button button  button_send" onmouseover="this.className=\'cssButtonHover\'" onmouseout="this.className=\'cssButton submit_button button  button_send\'" type="button" value="' . CUSTOM_FORMS_WIDGET_BUTTON_TEXT . '" /></a></div>';
		
		$page = zen_draw_input_field( 'widget', $widget, 'id="widget" style="display:none;"' );
		$page .= '<div id="widgetBox">' . htmlentities( $widget ) . '</div>' . "\n";
		
		return $page;
	}
	
	public function GetWidgetLink( $fID ){
		if( ENABLE_SSL_CATALOG=='true' ){
			$url = HTTPS_CATALOG_SERVER . DIR_WS_HTTPS_CATALOG;
		}else{
			$url = HTTP_CATALOG_SERVER . DIR_WS_CATALOG;
		}
		$url .= 'index.php?main_page=' . CATALOG_CUSTOM_FORMS_MAIN_PAGE . '&form_id=' . $fID;
		return $url;
	}
	
	/********** DATABASE OPERATIONS **********/
	/********** INSERT **********/
	#FORM [INSERT]
	public function InsertForm( $post ){
		global $db, $messageStack;
		
		$error_message = '';
		#FORM TITLE
		if( zen_not_null($post['txtTitle']) ){
			$txtTitle	= $post['txtTitle'];
		}else{
			$error_message = MSG_TITLE_REQUIRED_ERROR;
		}
		#PAGE TITLE
		if( zen_not_null($post['txtPageTitle']) ){
			$pageTitle	= $post['txtPageTitle'];
		}else{
			$error_message = MSG_PAGE_TITLE_REQUIRED_ERROR;
		}
		#PAGE HEADING
		if( zen_not_null($post['txtHeading']) ){
			$pageHeading	= $post['txtHeading'];
		}else{
			$error_message = MSG_HEADER_REQUIRED_ERROR;
		}
		
		#NAVBAR TITLE
		if( zen_not_null($post['txtNavbar']) ){
			$navbarTitle	= $post['txtNavbar'];
		}else{
			$error_message = MSG_NAVBAR_TITLE_REQUIRED_ERROR;
		}
		
		#DESCRIPTION
		$description = '';
		if( zen_not_null($post['txtDescription']) ){
			$description = $post['txtDescription'];
		}
		
		$pars = 'action=addForm';
		
		if( $error_message != '' ){
			$messageStack->add_session($error_message, 'error');
			zen_redirect( $this->GetActionUrl() );
		}else{
			$insertID = 0;
			$sql = "INSERT INTO `" . TABLE_CUSTOM_FORMS . "`
					(`form_title`, `page_title`, `page_heading`, `navbar_title`, `form_description`, `created_by`, `timestamp`)
					VALUES
					(:formTitle, :pageTitle, :pageHeading, :navbarTitle, :description, :creator, :timestamp)";
			$sql = $db->BindVars($sql, ':formTitle', $txtTitle, 'string');
			$sql = $db->BindVars($sql, ':pageTitle', $pageTitle, 'string');
			$sql = $db->BindVars($sql, ':pageHeading', $pageHeading, 'string');
			$sql = $db->BindVars($sql, ':navbarTitle', $navbarTitle, 'string');
			$sql = $db->BindVars($sql, ':description', html_entity_decode($description), 'string');
			$sql = $db->BindVars($sql, ':creator', $_SESSION['admin_id'], 'integer');
			$sql = $db->BindVars($sql, ':timestamp', date("Y-m-d H:i:s"), 'string');
			$db->Execute($sql);
			$insertID = $db->insert_ID();
			
			if( $insertID > 0){
				$messageStack->add_session(MSG_FORM_ADDED, 'success');
				zen_redirect( $this->GetActionUrl('', $this->GetFormId()) );
			}else{ #ERROR
				$messageStack->add_session(MSG_FORM_NOT_ADDED, 'error');
				zen_redirect( $this->GetActionUrl('', $this->GetFormId()) );
			}
		}
	}
	
	#FIELD [INSERT]
	public function InsertFormField( $post ){
		global $db, $messageStack;
		
		$fieldID = -1;
		$field_name = $this->FieldNameValidator( $post['fID'], $post['txtName']);
		if( isset($post['fID']) and (int)$post['fID'] > 0 ){
			//INSERT FORM FIELD
			$sql = "INSERT INTO `" . TABLE_CUSTOM_FORMS_FIELDS . "`
					(`form_id`, `field_type`, `field_name`, `label`, `description`, `required`, `sort_order`, `modified_by`, `timestamp`)
					VALUES
					(:frmID, :fType, :fName, :fLabel, :fDesc, :fRequired, :fSortOrder, :fModified, :fTime)";
			
			$sql = $db->BindVars($sql, ':frmID', $post['fID'], 'integer');
			$sql = $db->BindVars($sql, ':fType', $post['txtType'], 'string');
			$sql = $db->BindVars($sql, ':fName', $field_name, 'string');
			$sql = $db->BindVars($sql, ':fLabel', $post['txtLabel'], 'string');
			$sql = $db->BindVars($sql, ':fDesc', $post['txtDescription'], 'string');
			$required = 0;
			if( isset( $post['rbxRequired'] ) and $post['rbxRequired'] == 1 ){
				$required = 1;
			}
			$sql = $db->BindVars($sql, ':fRequired', $required, 'integer');
			$sql = $db->BindVars($sql, ':fSortOrder', $post['txtSortOrder'], 'integer');
			$sql = $db->BindVars($sql, ':fModified', $_SESSION['admin_id'], 'string');
			$sql = $db->BindVars($sql, ':fTime', date("Y-m-d H:i:s"), 'string');
			$db->Execute($sql);
			$insertID = $db->insert_ID();
			
			if( $insertID > 0){
				$messageStack->add_session(MSG_FORM_FIELD_ADDED, 'success');
				zen_redirect( $this->GetActionUrl('', $this->GetFormId()) );
			}else{ #ERROR
				$messageStack->add_session(MSG_FORM__FIELD_NOT_ADDED, 'error');
				zen_redirect( $this->GetActionUrl('addField', $this->GetFormId()) );
			}
		}
		
		return $fieldID;
	}
	
	#OPTION [INSERT]
	public function InsertOption( $post ){
		global $db, $messageStack;
		
		$optionID = -1;
		if( isset($post['ffID']) and (int)$post['ffID'] > 0 ){
			//INSERT FORM FIELD
			$sql = "INSERT INTO `" . TABLE_CUSTOM_FORMS_FIELDS_OPTIONS . "`
					(`form_field_id`, `field_text`, `field_value`, `selected`, `sort_order`)
					VALUES
					(:ffID, :ffText, :ffValue, :ffSelected, :fSortOrder)";
			
			$sql = $db->BindVars($sql, ':ffID', (int)$post['ffID'], 'integer');
			$sql = $db->BindVars($sql, ':ffText', (isset($post['txtText'])?$post['txtText']:''), 'string');
			$option_value = '';
			if( isset($post['txtValue']) ){
				$fieldType = $this->GetThisFieldType();
				if( in_array($fieldType, array('Dropdown', 'Radio', 'Checkbox')) ){
					//ABSTRACT VALUES - NOT VISIBLE TO END USERS
					$option_value = $this->OptionValueValidator( $post['ffID'], $post['txtValue']);
				}else{
					//VISIBLE VALUE
					$option_value = $post['txtValue'];
				}
			}
			$sql = $db->BindVars($sql, ':ffValue', $option_value, 'string');
			$selected = 0;
			if( isset( $post['cbxSelected'] ) and (int)$post['cbxSelected'] > 0 ){
				$selected = 1;
				if( isset($post['ffID']) and (int)$post['ffID'] > 0 ){
					$this->UnselectAllOptions( (int)$post['ffID'] ); //RESET ALL OPTIONS
				}
			}
			$sql = $db->BindVars($sql, ':ffSelected', $selected, 'integer');
			$sql = $db->BindVars($sql, ':fSortOrder', (int)$post['txtSortOrder'], 'integer');
			$db->Execute($sql);
			$insertID = $db->insert_ID();
			
			if( $insertID > 0){
				$messageStack->add_session(MSG_OPTION_ADDED, 'success');
				zen_redirect( $this->GetActionUrl('', $this->GetFormId(), $this->GetFieldId(), $insertID) );
			}else{ #ERROR
				$messageStack->add_session(MSG_OPTION_NOT_ADDED, 'error');
				zen_redirect( $this->GetActionUrl('editOption', $this->GetFormId(), $this->GetFieldId()) );
			}
		}
		
		return $optionID;
	}
	
	/********** UPDATE **********/
	#FORM [UPDATE]
	public function UpdateForm($post){
		global $db, $messageStack;
		
		$success = false;
		if( isset($post['fID']) and (int)$post['fID'] > 0 ){
			$sql = "UPDATE `" . TABLE_CUSTOM_FORMS . "`
					SET `form_title` = :title,
						`page_title` = :pageTitle,
						`page_heading` = :pageHeading,
						`navbar_title` = :navbarTitle,
						`form_description` = :description
					WHERE `form_id` = :formID";
			$sql = $db->BindVars($sql, ':title', $post['txtTitle'], 'string');
			$sql = $db->BindVars($sql, ':pageTitle', $post['txtPageTitle'], 'string');
			$sql = $db->BindVars($sql, ':pageHeading', $post['txtHeading'], 'string');
			$sql = $db->BindVars($sql, ':navbarTitle', $post['txtNavbar'], 'string');
			$sql = $db->BindVars($sql, ':description', zen_db_prepare_input( html_entity_decode($post['txtDescription'] ) ), 'string');
			$sql = $db->BindVars($sql, ':formID', (int)$post['fID'], 'integer');
			$success = $db->Execute($sql);
			
		}
		if( $success ){
			$messageStack->add_session(MSG_FORM_UPDATED, 'success');
		}else{
			$messageStack->add_session(MSG_FORM_NOT_UPDATED, 'error');
		}
		zen_redirect( $this->GetActionUrl('', $this->GetFormId()) );
	}
	
	#FIELD [UPDATE]
	public function UpdateFormField($post){
		global $db, $messageStack;
		
		$success = false;
		if( (int)$post['fID'] > 0 ){
			$field_name = $this->FieldNameValidator( $post['fID'], $post['txtName']);
			$sql = "UPDATE `" . TABLE_CUSTOM_FORMS_FIELDS . "`
					SET
						`field_type` 	= :fType, 
						`field_name`	= :fName,
						`label`			= :fLabel,
						`description`	= :fDesc,
						`required`		= :fRequired,
						`sort_order`	= :fSortOrder,
						`modified_by`	= :fModified,
						`timestamp`		= :fTime
					WHERE `form_field_id` = :ffID";
			
			$sql = $db->BindVars($sql, ':fType', $post['txtType'], 'string');
			$sql = $db->BindVars($sql, ':fName', $field_name, 'string');
			$sql = $db->BindVars($sql, ':fLabel', $post['txtLabel'], 'string');
			$sql = $db->BindVars($sql, ':fDesc', $post['txtDescription'], 'string');
			$required = 0;
			if( isset( $post['rbxRequired'] ) and $post['rbxRequired'] == 1 ){
				$required = 1;
			}
			$sql = $db->BindVars($sql, ':fRequired', $required, 'integer');
			$sql = $db->BindVars($sql, ':fSortOrder', $post['txtSortOrder'], 'integer');
			
			$sql = $db->BindVars($sql, ':fModified', $_SESSION['admin_id'], 'string');
			$sql = $db->BindVars($sql, ':fTime', date("Y-m-d H:i:s"), 'string');
			$sql = $db->BindVars($sql, ':ffID', $post['ffID'], 'integer');
			
			$success = $db->Execute($sql);
		}
		if( $success ){
			$messageStack->add_session(MSG_FORM_FIELD_UPDATED, 'success');
		}else{
			$messageStack->add_session(MSG_FORM_FIELD_NOT_UPDATED, 'error');
		}
		zen_redirect( $this->GetActionUrl('', $this->GetFormId()) );
	}
	
	#OPTION [UPDATE]
	public function UpdateOption($post){
		global $db, $messageStack;
		
		$success = false;
		if( (int)$post['oID'] > 0 ){
			$sql = "UPDATE `" . TABLE_CUSTOM_FORMS_FIELDS_OPTIONS . "`
					SET
						`field_text` 	= :fText, 
						`field_value`	= :fValue,
						`read_only`		= :fReadOnly,
						`selected`		= :fSelected,
						`sort_order`	= :fSortOrder
					WHERE `form_field_option_id` = :oID";
			
			$sql = $db->BindVars($sql, ':fText', $post['txtText'], 'string');
			$option_value = '';
			if( isset($post['txtValue']) ){
				$fieldType = $this->GetThisFieldType();
				if( in_array($fieldType, array('Dropdown', 'Radio', 'Checkbox')) ){
					//ABSTRACT VALUES - NOT VISIBLE TO END USERS
					$option_value = $this->OptionValueValidator( $post['ffID'], $post['txtValue']);
				}else{
					//VISIBLE CUSTOM VALUE
					$option_value = $post['txtValue'];
				}
			}
			$sql = $db->BindVars($sql, ':fValue', $option_value, 'string');
			$read_only = 0;
			if( isset( $post['cbxReadOnly'] ) and $post['cbxReadOnly'] == 1 ){
				$read_only = 1;
			}
			$sql = $db->BindVars($sql, ':fReadOnly', $read_only, 'integer');
			$selected = 0;
			if( isset( $post['cbxSelected'] ) and $post['cbxSelected'] == 1 ){
				$selected = 1;
				if( isset($post['ffID']) and (int)$post['ffID'] > 0 ){
					$this->UnselectAllOptions( (int)$post['ffID'] ); //RESET ALL OPTIONS
				}
			}
			$sql = $db->BindVars($sql, ':fSelected', $selected, 'integer');
			$sql = $db->BindVars($sql, ':fSortOrder', (int)$post['txtSortOrder'], 'integer');
			$sql = $db->BindVars($sql, ':oID', $post['oID'], 'integer');
			$success = $db->Execute($sql);
		}
		if( $success ){
			$messageStack->add_session(MSG_OPTION_UPDATED, 'success');
		}else{
			$messageStack->add_session(MSG_OPTION_NOT_UPDATED, 'error');
		}
		zen_redirect( $this->GetActionUrl('', $this->GetFormId(), $this->GetFieldId()) );
	}
	
	/********** DELETE **********/
	#FORM [DELETE]
	public function DeleteForm($formID){
		global $db, $messageStack;
		
		$success = false;
		if( $formID > 0 ){
			#DELETE FORM FIELDS
			$sql = "SELECT `form_field_id`
					FROM `" . TABLE_CUSTOM_FORMS_FIELDS . "`
					WHERE `form_id` = :formID";
			$sql = $db->BindVars($sql, ':formID', $formID, 'integer');
			$rec = $db->Execute($sql);
			while( !$rec->EOF ){
				$this->DeleteFormField( $rec->fields['form_field_id'] );
				$rec->MoveNext();
			}
			
			#DELETE FORM
			$sql = "DELETE FROM `" . TABLE_CUSTOM_FORMS . "`
					WHERE `form_id` = :fID";
			$sql = $db->BindVars($sql, ':fID', $formID, 'integer');
			
			$success = $db->Execute($sql);
		}
		if( $success ){
			$messageStack->add_session(MSG_FORM_DELETED, 'success');
		}else{
			$messageStack->add_session(MSG_FORM_NOT_DELETED, 'error');
		}
		zen_redirect( $this->GetActionUrl() );
	}
	
	#FIELD [DELETE]
	public function DeleteFormField( $fieldID ){
		global $db, $messageStack;
		
		$success = false;
		#DELETE FORM FIELD
		if( $fieldID > 0 ){
			#DELETE FORM FIELDS OPTIONS
			$sql = "SELECT `form_field_option_id`
					FROM `" . TABLE_CUSTOM_FORMS_FIELDS_OPTIONS . "`
					WHERE `form_field_id` = :ffID";
			$sql = $db->BindVars($sql, ':ffID', $fieldID, 'integer');
			$rec = $db->Execute($sql);
			while( !$rec->EOF ){
				$this->DeleteOption( $rec->fields['form_field_option_id'] );
				$rec->MoveNext();
			}
			
			#DELETE FORM FIELD
			$sql = "DELETE FROM `" . TABLE_CUSTOM_FORMS_FIELDS . "`
					WHERE `form_field_id` = :ffID";
			$sql = $db->BindVars($sql, ':ffID', $fieldID, 'integer');
			$success = $db->Execute($sql);
		}
		if( $success ){
			$messageStack->add_session(MSG_FORM_FIELD_DELETED, 'success');
		}else{
			$messageStack->add_session(MSG_FORM_FIELD_NOT_DELETED, 'error');
		}
		#NO REDIRECTION DUE TO DAISY-CHAIN DELETE
	}
	
	#OPTION [DELETE]
	public function DeleteOption( $oID ){
		global $db, $messageStack;
		
		$success = false;
		if( (int)$oID > 0 ){
			$sql = "DELETE FROM `" . TABLE_CUSTOM_FORMS_FIELDS_OPTIONS . "`
					WHERE `form_field_option_id` = :oID";
			$sql = $db->BindVars($sql, ':oID', $oID, 'integer');
			$success = $db->Execute($sql);
		}
		if( $success ){
			$messageStack->add_session(MSG_OPTION_DELETED, 'success');
		}else{
			$messageStack->add_session(MSG_OPTION_NOT_DELETED, 'error');
		}
		#NO REDIRECTION DUE TO DAISY-CHAIN DELETE
	}
	
	#RESET OPTIONS SELECTED
	private function UnselectAllOptions( $fieldID ){
		global $db;
		
		if( (int)$fieldID > 0 ){
			$sql = "UPDATE `" . TABLE_CUSTOM_FORMS_FIELDS_OPTIONS . "`
					SET `selected` = 0
					WHERE `form_field_id` = :ffID";
			$sql = $db->BindVars($sql, ':ffID', (int)$fieldID, 'integer');
			$db->Execute($sql);
		}
	}
	
	/* PUBLIC METHODS */
	public function GetCreatorNameFromId( $creatorID ){
		global $db;
		
		$creator = '';
		$sql = "SELECT `admin_name`
				FROM `" . TABLE_ADMIN . "`
				WHERE `admin_id` = :adminID";
		$sql = $db->BindVars($sql, ':adminID', $creatorID, 'integer');
		$rec = $db->Execute($sql);
		if( !$rec->EOF ){
			$creator = $rec->fields['admin_name'];
		}
		return $creator;
	}
	
	/* PUBLIC GETTER METHODS */
	public function GetAction(){
		return $this->action;
	}
	public function GetFormId(){
		return $this->id;
	}
	public function GetFormTitle(){
		return $this->title;
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
	public function GetDescription(){
		return $this->description;
	}
	public function GetFieldCount(){
		return $this->field_count;
	}
	public function GetHitCount(){
		return $this->hit_count;
	}
	public function GetCreated(){
		return $this->created;
	}
	public function GetCreator(){
		return $this->creator;
	}
	public function GetFields(){
		return $this->fields;
	}
	public function GetFieldTypes(){
		return $this->field_types;
	}
	public function GetFieldId(){
		return $this->field_id;
	}
	public function GetOptionId(){
		return $this->option_id;
	}
	public function GetUnavailableFieldAttributes(){
		return $this->unavailable_field_attributes;
	}
	
	/* PUBLIC SETTER METHODS */
	public function SetAction( $action ){
		$this->action = $action;
	}
	public function SetFormId( $id ){
		$this->id = $id;
	}
	public function SetFormTitle( $title ){
		$this->title = $title;
	}
	public function SetPageTitle( $title ){
		$this->page_title = $title;
	}
	public function SetPageHeading( $heading ){
		$this->page_heading = $heading;
	}
	public function SetNavbarTitle( $title ){
		$this->navbar_title = $title;
	}
	public function SetDescription( $description ){
		$this->description = $description;
	}
	public function SetFieldCount( $count ){
		$this->field_count = $count;
	}
	public function SetHitCount( $count ){
		$this->hit_count = $count;
	}
	public function SetCreated( $created ){
		$this->created = $created;
	}
	public function SetCreator( $creator ){
		$this->creator = $creator;
	}
	public function SetFields( $fields ){
		$this->fields = $fields;
	}
	public function SetFieldTypes( $types ){
		$this->field_types = $types;
	}
	public function SetFieldId( $id ){
		$this->field_id = $id;
	}
	public function SetOptionId( $id ){
		$this->option_id = $id;
	}
	public function SetUnavailableFieldAttributes( $attributes ){
		$this->unavailable_field_attributes = $attributes;
	}
}
