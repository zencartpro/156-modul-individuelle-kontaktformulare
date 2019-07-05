<?php
/**
 * Custom Forms plug-in
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @Author: Will Davies Vasconcelos <willvasconcelos@outlook.com>
 * @Version: 1.3.0
 * @Dev Start Date: Wednesday, May 30 2018
 * @Dev End Date:   Friday, June 15 2018
 * @Last Update:	July 5 2019
 * @Tested on Zen Cart v1.5.6 German webchills $
 */
	require('includes/application_top.php');
	
	require(DIR_WS_CLASSES . 'addon_custom_forms_builder.php');
	$cprObj = new addon_custom_forms_builder();
	/* DATABASE OPERATIONS */
	switch ( $cprObj->GetAction() ) {
		#INSERT
		case 'insertForm':
			$cprObj->InsertForm($_POST);
			break;
		case 'insertField':
			$cprObj->InsertFormField( $_POST );
			break;
		case 'insertOption':
			$cprObj->InsertOption( $_POST );
			break;
		# UPDATE
		case 'updateForm':
			$cprObj->UpdateForm($_POST);
			break;
		case 'updateField':
			$cprObj->UpdateFormField($_POST);
			break;
		case 'updateOption':
			$cprObj->UpdateOption( $_POST );
			break;
		
		#DELETE
		#FORM
		case 'deleteFormConfirmed':
			$cprObj->DeleteForm( $cprObj->GetFormId() ); //DELETE SELECTED FORM
			break;
		#FIELD
		case 'deleteFieldConfirmed':
			$cprObj->DeleteFormField( (int)$_POST['ffID'] );
			zen_redirect( $cprObj->GetActionUrl('', $cprObj->GetFormId()) );
			break;
		#OPTION
		case 'deleteOptionConfirmed':
			$cprObj->DeleteOption( (int)$_POST['oID'] );
			zen_redirect( $cprObj->GetActionUrl('', $cprObj->GetFormId(), $cprObj->GetFieldId()) );
			break;
		
		default:
			break;
	}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" href="includes/stylesheet.css">
    <link rel="stylesheet" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script src="includes/menu.js"></script>
    <script src="includes/general.js"></script>
    <script>
      function init() {
          cssjsmenu('navbar');
          if (document.getElementById) {
              var kill = document.getElementById('hoverJS');
              kill.disabled = true;
          }
      }
    </script>
<style type="text/css">
	.info-labels{
		display:inline-block;
		width:90px;
		margin-left:10px;
	}
	.info-label-head{
		font-size:16px;
		color:#F66438;
		margin:10px 0 5px 10px;
	}
	.info-label-head2{
		font-size:14px;
		color:#777;
		margin:10px;
		padding-top:5px;
		display:block;
		border-top:solid 2px WhiteSmoke;
	}
	.frm-emails, .frm-description{
		max-width:calc(100% - 110px);
		vertical-align:top;
	}
	.infoBoxContent p{
		font-size:14px;
		margin:5px;
		padding:5px;
		background-color:white;
		color:##05316F;
		border:solid 1px #F66438;
		border-radius:5px;
	}
	.table-listing-display{
		width:100%;
	}
	.table-listing-display th{
		padding:2px 5px;
		background-color:Gainsboro;
	}
	.table-listing-display td{
		padding:2px 5px;
		border:solid 1px Gainsboro;
	}
	.listBox{
		border:solid 1px Silver;
		background-color:white;
		margin:2px 5px;
		padding:5px;
	}
	.listBox a{
		float:right;
	}
	#tblReport{
		width:100%;
	}
	#tblReport th, #tblReport td{
		padding:2px 5px;
		margin:10px 0;
	}
	#tblReport th{
		text-align:left;
	}
	#tblReport tr{
		background-color:Gainsboro;
	}
	#tblReport .evenRow{
		background-color:White;
	}
	#tblReport .oddRow{
		background-color:WhiteSmoke;
	}
	#widgetBox, #linkBox{
		border:solid 1px White;
		border-radius:5px;
		background-color:WhiteSmoke;
		padding:5px;
		margin:5px;
	}
</style>
<?php if ( $editor_handler != '' ) include ($editor_handler); ?>
</head>
<body onLoad="init()">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
 <div class="container-fluid">
<table border="0" width="100%" cellspacing="2" cellpadding="2">
	<tr>
		<td width="100%" valign="top">
			<table border="0" width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
					<td class="pageHeading"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
					<td class="pageHeading" align="right">
						<button type="button" class="btn btn-default btn-md" onClick="window.location='?action=addForm'">
							<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <?php echo BTN_ADD; ?>
						</button>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table border="0" width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td valign="top">
<?php
	if( !in_array($cprObj->GetAction(), array('addForm', 'editForm') ) ){ #IF NOT ADD/EDIT FORM
		#GET NUMBER OF ROWS
		$number_of_rows = 10; #DEFAULT
		if( (int)CUSTOM_FORMS_NUMBER_ROWS > 10 ){
			$number_of_rows = CUSTOM_FORMS_NUMBER_ROWS; #CONFIGURATION
		}
		#GET PARAMETERS
		$parameters = zen_get_all_get_params(array('action','rID','page'));
		#LOAD DATA
		if( $sniffer->table_exists( TABLE_CUSTOM_FORMS ) ){
			$sql = "SELECT `form_id`, `form_title`, `page_title`, `page_heading`, `timestamp`
					 FROM `" . TABLE_CUSTOM_FORMS . "`
					 ORDER BY `form_id` ASC";
			$rec = $db->Execute($sql);
			if(!$rec->EOF){
				$total_records = $rec->RecordCount();
				$pages_split = new splitPageResults( $_GET['page'], $number_of_rows, $sql, $total_records );
				$rec = $db->Execute($sql);
				echo $pages_split->display_count($total_records, $number_of_rows, (int)$_GET['page'], PAGINATION_LABEL);
?>
						<br /><br />
						<table border="0" width="100%" cellspacing="0" cellpadding="2">
							<tr class="dataTableHeadingRow">
								<td class="dataTableHeadingContent"><?php echo TBL_HEAD_FORM_ID; ?></td>
								<td class="dataTableHeadingContent"><?php echo TBL_HEAD_FORM_TITLE; ?></td>
								<td class="dataTableHeadingContent"><?php echo TBL_HEAD_PAGE_TITLE; ?></td>
								<td class="dataTableHeadingContent"><?php echo TBL_HEAD_PAGE_HEADING; ?></td>
								<td class="dataTableHeadingContent"><?php echo TBL_HEAD_FIELD_COUNT; ?></td>
								<td class="dataTableHeadingContent"><?php echo TBL_HEAD_HIT_COUNT; ?></td>
								<td class="dataTableHeadingContent"><?php echo TBL_HEAD_CREATED; ?></td>
								<td class="dataTableHeadingContent"><?php echo TBL_HEAD_ACTION; ?></td>
							</tr>
<?php
				while( !$rec->EOF ){
					if( $cprObj->GetFormId() == 0 ){
						$cprObj->InitVars( $rec->fields['form_id'] );
					}
					$tr_class = 'dataTableRow';
					if( $cprObj->GetFormId() == $rec->fields['form_id'] ){
						$tr_class = 'dataTableRowSelected';
					}
?>
							<tr class="<?php echo $tr_class; ?>" onMouseOver="rowOverEffect(this)" onMouseOut="rowOutEffect(this)" onClick="document.location.href='<?php echo zen_href_link(FILENAME_ADDON_CUSTOM_FORM_BUILDER, 'fID=' . $rec->fields['form_id']); ?>'" \>
								<td class="dataTableContent"><?php echo $rec->fields['form_id']; ?></td>
								<td class="dataTableContent"><?php echo $rec->fields['form_title']; ?></td>
								<td class="dataTableContent"><?php echo $rec->fields['page_title']; ?></td>
								<td class="dataTableContent"><?php echo $rec->fields['page_heading']; ?></td>
								<td class="dataTableContent"><?php echo $cprObj->FieldCount( $rec->fields['form_id'] ); ?></td>
								<td class="dataTableContent"><?php echo $cprObj->HitCount( $rec->fields['form_id'] ); ?></td>
								<td class="dataTableContent"><?php echo date("m/d/y", strtotime($rec->fields['timestamp'])); ?></td>
								<td class="dataTableContent" width="90"><?php
					echo ' <a href="' . $cprObj->GetActionUrl('editForm', $rec->fields['form_id']) . '">'.
						zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT).
					'</a>'.
					' <a href="'.$cprObj->GetActionUrl('deleteFormConfirm', $rec->fields['form_id']).'">'.
					zen_image(DIR_WS_IMAGES . 'icon_delete.gif', ICON_DELETE) . '</a>' .
					( $cprObj->GetFormId() == $rec->fields['form_id'] ? zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '', '', '', 'style="margin-left:10px;"') : zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO, '', '', 'style="margin-left:10px;"') );
?>
								</td>
							</tr>
<?php
					$rec->MoveNext();
				}
?>
							<tr>
								<td colspan=8>&nbsp;</td>
							</tr>
							<tr>
								<td colspan=8 align=center>
<?php
				echo $pages_split->display_links($total_records, $number_of_rows, $highest_page_number, $_GET['page'], $parameters);
?>
								</td>
							</tr>
						</table>
<?php
			}else{
				echo '<h1>' . INFO_NO_FORM . '</h1>' . "\n";
			}
		}else{
			echo '<h1>' . MSG_INSTALL_ERROR . '</h1>' . "\n";
		}
	}else{ # ADD/EDIT FORM
		#LOAD POSTED VALUES
		if( $cprObj->GetAction() == 'addForm' ){
			$form = '<h4>' . INFO_HEAD_ADD_FORM . '</h4>' . "\n";
			$tmp = $cprObj->GetForm('insertForm'); #LOAD FORM TAG (ARRAY)
			$form .= $tmp['form'] . "\n"; #GET FORM TAG FROM ARRAY
		}else{ #update
			$form = '<h4>' . INFO_HEAD_EDIT_FORM . '</h4>' . "\n";
			$tmp = $cprObj->GetForm('updateForm', $cprObj->GetFormId() ); #LOAD FORM TAG (ARRAY)
			$form .= $tmp['form'] . "\n"; #GET FORM TAG FROM ARRAY
			$form .= zen_draw_hidden_field('fID', $cprObj->GetFormId()) . "\n";
			$cprObj->SetAction(""); #EMPTY ACTION TO SHOW DEFAULT RIGHT BOX
		}
		
		#PAGE TITLE
		$form .= zen_draw_label(
					INFO_PAGE_TITLE_LABEL . ": ", 
					'txtPageTitle'
				) . '<br />' . "\n";
		$form .= zen_draw_input_field(
					'txtPageTitle', 
					$cprObj->GetPageTitle(),
					zen_set_field_length(
						TABLE_CUSTOM_FORMS,
						'page_title',
						30
					)
				) . "\n";
		$form .= '<br /><br />' . "\n";
		
		#HEADING
		$form .= zen_draw_label(
					INFO_PAGE_HEADER_LABEL . ": ", 
					'txtHeading'
				) . '<br />' . "\n";
		$form .= zen_draw_input_field(
					'txtHeading', 
					$cprObj->GetPageHeading(), 
					zen_set_field_length(
						TABLE_CUSTOM_FORMS,
						'page_heading',
						30
					)
				) . "\n";
		$form .= '<br /><br />' . "\n";
		
		#NAVBAR
		$form .= zen_draw_label(
					INFO_NAVBAR_TITLE_LABEL . ": ", 
					'txtNavbar'
				) . '<br />' . "\n";
		$form .= zen_draw_input_field(
					'txtNavbar', 
					$cprObj->GetNavbarTitle(), 
					zen_set_field_length(
						TABLE_CUSTOM_FORMS,
						'navbar_title',
						30
					)
				) . "\n";
		$form .= '<br /><br />' . "\n";
		
		#FORM TITLE
		$form .= zen_draw_label(
					INFO_FORM_TITLE_LABEL . ": ", 
					'txtTitle'
				) . '<br />' . "\n";
		$form .= zen_draw_input_field(
					'txtTitle', 
					$cprObj->GetFormTitle(), 
					zen_set_field_length(
						TABLE_CUSTOM_FORMS,
						'form_title',
						30
					)
				) . "\n";
		$form .= '<br /><br />' . "\n";
		
		#DESCRIPTION / CONTENT
		$form .= zen_draw_label(
					INFO_FORM_DESCRIPTION_LABEL . ": ", 
					'txtDescription'
				) . '<br />' . "\n";
		$form .= zen_draw_textarea_field(
					'txtDescription', 
					100, 
					50, 
					'3',
					htmlspecialchars(stripslashes($cprObj->GetDescription()), ENT_COMPAT, CHARSET, TRUE),
					'class="editorHook"'
				) . "\n";
		$form .= '<br /><br />' . "\n";
		
		#BUTTONS
		if( $cprObj->GetAction() == 'addForm' ){
			$form .= zen_image_submit('button_add.gif', BTN_ADD) . "\n";
		}else{ #update
			$form .= zen_image_submit('button_update.gif', BTN_MODIFY) . "\n";
		}
		$form .= ' <a href="' . $cprObj->GetActionUrl('', $cprObj->GetFormId()) . '">' . 
					zen_image_button( 'button_cancel.gif', IMAGE_CANCEL ) .
				'</a>' . "\n";
		
		$form .= '</form>' . "\n";
		$form .= '<br /><br />' . "\n";

		echo $form;
	} #END: if action != editForm
?>
					</td>
					
<?php
	/* RIGHT-SIDE INFO BOX */
	$heading = array();
	$contents = array();
	#BUTTONS TO SHOW ON FORM RELATED OPERATIONS
	$frm_buttons = array(
		'align' => 'center', 
		'text' => '
			<button type="button" class="btn btn-success btn-sm" aria-label="Left Align" onClick="window.location=\'' . $cprObj->GetActionUrl('editForm', $cprObj->GetFormId()) . '\'">
				<span class="glyphicon glyphicon glyphicon glyphicon-edit" aria-hidden="true"></span>
				' . BTN_EDIT . '
			</button>
			
			<button type="button" class="btn btn-default btn-sm" aria-label="Left Align" onClick="window.location=\'' . $cprObj->GetActionUrl('getHitsReport', $cprObj->GetFormId()) . '\'">
				<span class="glyphicon glyphicon-star" aria-hidden="true"></span>
				' . BTN_SHOW_HITS . '
			</button>
			
			<button type="button" class="btn btn-default btn-sm" aria-label="Left Align" onClick="window.location=\'' . $cprObj->GetActionUrl('getWidget', $cprObj->GetFormId()) . '\'">
				' . BTN_GET_WIDGET . '
			</button>
			
			<button type="button" class="btn btn-danger btn-sm" aria-label="Left Align" onClick="window.location=\'' . $cprObj->GetActionUrl('deleteFormConfirm', $cprObj->GetFormId()) . '\'">
				<span class="glyphicon glyphicon glyphicon-remove" aria-hidden="true"></span>
				' . BTN_DELETE . '
			</button>'
	);
	
	if( $cprObj->GetAction() != '' ){
		switch ($cprObj->GetAction()) {
			/***************************
			 ********** FORMS **********
			 ***************************/
			
			/********** DELETE (FORM) - CONFIRMATION **********/
			case 'deleteFormConfirm':
				$heading[] = array(
					'text' => '<strong>' . INFO_HEAD_DELETE_FORM_CONFIRM . '</strong>'
				);
				$contents = $cprObj->GetForm('deleteFormConfirmed', $cprObj->GetFormId());
				
				$contents[] = array('text' => '<p>' . INFO_DELETE_FORM_CONFIRM . '</p>');
				
				#LOAD FORM INFO DISPLAY
				if( $cprObj->GetFormId() > 0 ){
					#LOAD FORM INFO DISPLAY
					$content = $cprObj->GetFormDisplayContents( $cprObj->GetFormId() );
					if( count($content) > 0 ){
						foreach($content as $c){
							$contents[] = $c;
						}
					}
				}
				
				$contents[] = array(
					'align' => 'center', 
					'text' => '<br />' . 
						zen_image_submit('button_delete.gif', IMAGE_DELETE) . 
						' <a href="' . $cprObj->GetActionUrl('', $cprObj->GetFormId()) . '">' . 
							zen_image_button('button_cancel.gif', IMAGE_CANCEL) . 
						'</a>');
				break;
			
			/********** ADD / CREATE (FORM) **********/
			case 'addForm': #SHOW IN THE MAIN AREA
				break;
			
			/********** EDIT (FORM) **********/
			case 'editForm': #SHOW IN THE MAIN AREA
				break;
			
			/*********************************
			 ********** FORM FIELDS **********
			 *********************************/
			 
			/********** DELETE (FORM FIELD) - CONFIRMATION **********/
			case 'deleteFieldConfirm':
				$heading[] = array(
					'text' => '<strong>' . INFO_HEAD_DELETE_FIELD_CONFIRM . '</strong>'
				);
				$contents = $cprObj->GetForm('deleteFieldConfirmed', $cprObj->GetFormId(), $cprObj->GetFieldId());
				
				if( $cprObj->GetFieldId() > 0 ){
					if( $cprObj->GetFormId() > 0 ){
						#LOAD FORM INFO DISPLAY
						$content = $cprObj->GetFormDisplayContents( $cprObj->GetFormId() );
						if( count($content) > 0 ){
							foreach($content as $c){
								$contents[] = $c;
							}
						}
					}
					
					$field = array();
					#SEARCH FOR A FIELD ID MATCH
					foreach( $cprObj->GetFields() as $f){
						if( $f['id'] == $cprObj->GetFieldId() ){
							$field = $f;
						}
					}
					
					//IF MATCH FOUND
					if( count($field) > 0 ){
						
						$content = $cprObj->GetFormFieldDisplayContents( $field['id'] );
						if( count($content) > 0 ){
							$contents[] = array('text' => '<p>' . INFO_DELETE_FIELD_CONFIRM . '</p>');
							foreach( $content as $c ){
								$contents[] = $c;
							}
						}
						
						$contents[] = array(
							'align' => 'center', 
							'text' => '<br />' . 
								zen_image_submit('button_delete.gif', IMAGE_DELETE) . 
								' <a href="' . $cprObj->GetActionUrl('', $cprObj->GetFormId()) . '">' . 
									zen_image_button('button_cancel.gif', IMAGE_CANCEL) . 
								'</a>'
						);
					}
				}
				break;
			
			/********** ADD (FORM FIELDS) **********/
			case 'addField':
				$type = ( isset($_GET['type'])  ? $_GET['type'] : 'Text' );
				$name = ( isset($_GET['name'])  ? $_GET['name'] : '' );
				$value = ( isset($_GET['value']) ? $_GET['value'] : '' );
				$label	= ( isset($_GET['label'])  ? $_GET['label'] : '' );
				$description = ( isset($_GET['description']) ? $_GET['description']: '' );
				$required = ( ( isset($_GET['required']) and $_GET['required'] != '' ) ? '1' : '0' );
				$sort_order = ( isset($_GET['sort_order']) ? (int)$_GET['sort_order'] : '' );
				
				$heading[] = array(
					'text' => '<strong>' . INFO_HEAD_ADD_FIELD . '</strong>'
				);
				
				$contents = $cprObj->GetForm('insertField', $cprObj->GetFormId());
				
				#SHOW FORM INFO
				$content = $cprObj->GetFormDisplayContents( $cprObj->GetFormId() );
				if( count($content) > 0 ){
					foreach( $content as $c ){
						$contents[] = $c;
					}
				}
				
				#SHOW FIELDS INFO
				if( $cprObj->GetFieldCount() > 0 ){
					$contents[] = array('text' => zen_draw_label( INFO_HEAD_EXISTING_FIELDS, '', 'class="info-label-head2"'));
					$output = '<table class="table-listing-display">
						<tr>
							<th>'.INFO_FIELD_TYPE_LABEL.'</th>
							<th>'.INFO_FIELD_LABEL_LABEL.'</th>
							<th>'.INFO_FIELD_NAME_LABEL.'</th>
							<th>'.INFO_FIELD_REQUIRED_LABEL.'</th>
						</tr>';
					foreach( $cprObj->GetFields() as $field ){
						$output .= '<tr>
							<td>' . $field['type'] . '</td>
							<td>' . $field['label'] . '</td>
							<td>' . $field['name'] . '</td>
							<td>' . ((int)$field['required']>0?LBL_YES:LBL_NO) . '</td>
						</tr>';
					}
					$output .= '</table>';
					$contents[] = array( 'text' => $output );
				}
				
				#SHOW ADD FORM FIELD FORM
				#TITLE (read-only)
				$contents[] = array(
					'text' => zen_draw_hidden_field(
						'fID', 
						$cprObj->GetFormId() 
					) . 
					zen_draw_label(
							INFO_HEAD_ADD_FIELD, 
							'', 
							'class="info-label-head2"'
						)
				);
				#TYPES
				$contents[] = array(
					'text' => zen_draw_label(
							INFO_FIELD_TYPE_LABEL, 
							'txtType', 
							'class="info-labels"'
						) . 
						zen_draw_pull_down_menu(
							'txtType', 
							$cprObj->GetFieldTypes(), 
							$type,
							'onChange="GetNewFields( this.value );"'
						)
				);
				#LABEL
				$contents[] = array(
					'text' => zen_draw_label(
							INFO_FIELD_LABEL_LABEL . ": ", 
							'txtLabel', 
							'class="info-labels" name="lblLabel"'
						) . 
						zen_draw_input_field(
							'txtLabel', 
							$label, 
							zen_set_field_length(
								TABLE_CUSTOM_FORMS_FIELDS, 
								'label',
								20
							) .
							' onKeyUp="FillUpName();" onChange="InputChange(this);"'
						)
				);
				#NAME
				$contents[] = array(
					'text' => zen_draw_hidden_field(
							'txtName', 
							$name, 
							zen_set_field_length(
								TABLE_CUSTOM_FORMS_FIELDS, 
								'field_name', 
								20
							)
						)
				);
				#DESCRIPTION
				$contents[] = array(
					'text' => zen_draw_label(
							INFO_FORM_DESCRIPTION_LABEL . ": ", 
							'txtDescription', 
							'class="info-labels" name="lblDescription"'
						) .
						zen_draw_textarea_field(
							'txtDescription',
							'soft',
							70,
							5,
							$description, 
							zen_set_field_length(
								TABLE_CUSTOM_FORMS_FIELDS, 
								'description', 
								20
							) . 'class="frm-description noEditor" onChange="InputChange(this);"'
						)
				);
				#REQUIRED
				$contents[] = array(
					'text' => zen_draw_label(
							INFO_FIELD_REQUIRED_LABEL,
							'rbxRequired', 
							'class="info-labels" name="lblRequired"'
						) . 
						zen_draw_checkbox_field(
							'rbxRequired', 
							'1', 
							( (int)$required > 0 ? true : false )
						)
				);
				#SORT ORDER
				$contents[] = array(
					'text' => zen_draw_label(
							INFO_FIELD_ORDER_LABEL . ": ", 
							'txtSortOrder', 
							'class="info-labels"'
						) . 
						zen_draw_input_field(
							'txtSortOrder', 
							$sort_order,
							'maxlength=3 size=3'
						)
				);
				
				#BUTTONS
				$contents[] = array(
					'align' => 'center', 
					'text' => zen_image_submit(
									'button_add.gif', 
									BTN_ADD,
									'onClick="return CheckAddField();"'
								) . 
								' <a href="' . $cprObj->GetActionUrl() . '">' . 
								zen_image_button(
									'button_cancel.gif', 
									IMAGE_CANCEL
								) . 
							'</a>'
				);
				break;
			
			/********** EDIT (FORM FIELDS) **********/
			case 'editField':
				$heading[] = array(
					'text' => '<strong>' . INFO_HEAD_EDIT_FIELD . '</strong>'
				);
				
				#LOAD EDIT FORM FIELD FORM
				$fieldInfo = $cprObj->FieldInfo( $cprObj->GetFieldId() );
				if( count($fieldInfo) <= 0 ){
					#MESSAGE
					$contents[] = array(
						'text' => '<p>' . MSG_FIELD_INFO_NOT_AVAILABLE . '</p>'
					);
				}else{
					$contents = $cprObj->GetForm('updateField', $cprObj->GetFormId(), $cprObj->GetFieldId());
					
					#LOAD DISPLAY FORM DETAILS
					if( $cprObj->GetFormId() > 0 ){
						$content = $cprObj->GetFormDisplayContents( $cprObj->GetFormId() );
						if( count($content) > 0 ){
							foreach($content as $c){
								$contents[] = $c;
							}
						}
					}
					
					$contents[] = array(
						'text' => zen_draw_hidden_field(
							'fID', 
							$cprObj->GetFormId()
						) . 
						zen_draw_hidden_field(
							'ffID', 
							$fieldInfo['id']
						) . 
						zen_draw_label(
								INFO_HEAD_SELECTED_FIELD_TITLE,
								'', 
								'class="info-label-head"'
							)
					);
					#TYPE
					$contents[] = array(
						'text' => zen_draw_label(
								INFO_FIELD_TYPE_LABEL . ": ", 
								'txtType', 
								'class="info-labels"'
							) . 
							zen_draw_pull_down_menu(
								'txtType', 
								$cprObj->GetFieldTypes(), 
								$fieldInfo['type'],
								'onChange="GetNewFields( this.value );"'
							)
					);
					#LABEL
					$contents[] = array(
						'text' => zen_draw_label(
								INFO_FIELD_LABEL_LABEL . ": ", 
								'txtTitle', 
								'class="info-labels"'
							) . 
							zen_draw_input_field(
								'txtLabel', 
								$fieldInfo['label'], 
								zen_set_field_length(
									TABLE_CUSTOM_FORMS_FIELDS, 
									'label',
									20
								) .
								'onkeyup="FillUpName();" onChange="InputChange(this);"'
							)
					);
					#NAME
					$contents[] = array(
							'text' => zen_draw_hidden_field(
								'txtName', 
								$fieldInfo['name'], 
								zen_set_field_length(
									TABLE_CUSTOM_FORMS_FIELDS, 
									'field_name',
									20
								)
							)
					);
					#DESCRIPTION
					$contents[] = array(
						'text' => zen_draw_label(
								INFO_FORM_DESCRIPTION_LABEL . ": ", 
								'txtDescription', 
								'class="info-labels"'
							) .
							zen_draw_textarea_field(
								'txtDescription',
								'soft',
								70,
								5,
								$fieldInfo['description'], 
								zen_set_field_length(
									TABLE_CUSTOM_FORMS_FIELDS, 
									'description', 
									20
								) . 'class="frm-description noEditor"'
							)
					);
					#REQUIRED
					$contents[] = array(
						'text' => zen_draw_label(
								INFO_FIELD_REQUIRED_LABEL,
								'rbxRequired', 
								'class="info-labels"'
							) . 
							zen_draw_checkbox_field(
								'rbxRequired', 
								'1', 
								( $fieldInfo['required'] > 0 ? 'checked' : '' )
							)
					);
					#SORT ORDER
					$contents[] = array(
						'text' => zen_draw_label(
								INFO_FIELD_ORDER_LABEL . ": ", 
								'txtSortOrder', 
								'class="info-labels"'
							) . 
							zen_draw_input_field(
								'txtSortOrder', 
								$fieldInfo['sort_order'],
								'maxlength=3 size=3'
							)
					);
					
					$contents[] = array('text' => '<div id="additionalFields"></div>');
					#BUTTONS
					$contents[] = array(
						'align' => 'center', 
						'text' => zen_image_submit(
										'button_update.gif', 
										BTN_MODIFY,
										'onClick="return CheckAddField();"'
									) . 
									' <a href="' . $cprObj->GetActionUrl() . '">' . 
									zen_image_button(
										'button_cancel.gif', 
										IMAGE_CANCEL
									) . 
								'</a>'
					);
				}
				
				$txtValue = '';
				$optionID = 0;
				if( $fieldInfo['type'] == "Text" and isset($fieldInfo['options'][0]['value']) ){
					$txtValue = $fieldInfo['options'][0]['value'];
					$optionID = $fieldInfo['options'][0]['id'];
				}
				break;
				
			/****************************************
			 ********** FORM FIELD OPTIONS **********
			 ****************************************/
			 
			/********** DELETE (FORM FIELD OPTIONS) **********/
			case 'deleteOptionConfirm':
				$heading[] = array(
					'text' => '<strong>' . INFO_HEAD_DELETE_FIELD_OPTION_CONFIRM . '</strong>'
				);
				
				$fieldOptionInfo = $cprObj->FieldOptionInfo( $cprObj->GetOptionId() );
				if( count($fieldOptionInfo) <= 0 ){
					$contents[] = array(
						'text' => '<p>' . MSG_OPTION_INFO_NOT_AVAILABLE . '</p>'
					);
				}else{
					$contents = $cprObj->GetForm('deleteOptionConfirmed', $cprObj->GetFormId(), $cprObj->GetFieldId(), $cprObj->GetOptionId());
					
					#LOAD FORM INFO
					if( $cprObj->GetFormId() > 0 ){
						$content = $cprObj->GetFormDisplayContents( $cprObj->GetFormId() );
						if( count($content) > 0 ){
							foreach( $content as $c ){
								$contents[] = $c;
							}
						}
					}
					
					#LOAD FORM FIELD INFO
					if( $cprObj->GetFieldId() > 0 ){
						$content = $cprObj->GetFormFieldDisplayContents( $cprObj->GetFieldId() );
						if( count($content) > 0 ){
							foreach( $content as $c ){
								$contents[] = $c;
							}
						}
					}
					
					#SHOW CONFIRMATION MESSAGE 
					$contents[] = array('text' => '<p>' . INFO_DELETE_OPTION_CONFIRM . '</p>');
					
					#OPTION TITLE
					if( $cprObj->GetOptionId() > 0 ){
						$content = $cprObj->GetFormFieldOptionDisplayContents( $cprObj->GetOptionId() );
						if( count($content) > 0 ){
							foreach( $content as $c ){
								$contents[] = $c;
							}
						}
					}
					
					#BUTTONS
					$contents[] = array(
						'align' => 'center', 
						'text' => zen_image_submit(
									'button_delete.gif', 
									IMAGE_DELETE
								) . 
								' <a href="' . $cprObj->GetActionUrl('', $cprObj->GetFormId(), $cprObj->GetFieldId()) . '">' . 
									zen_image_button('button_cancel.gif', IMAGE_CANCEL) . 
								'</a>'
					);
				}
				break;
			
			/***************************************
			 ********** OPTION OPERATIONS **********
			 ***************************************/
			 
			/********** ADD (FORM FIELD OPTION) **********/
			case 'addOption':
				$heading[] = array(
					'text' => '<strong>' . INFO_HEAD_ADD_OPTION . '</strong>'
				);
				if( (int)$cprObj->GetFormId() > 0 ){
					if( (int)$cprObj->GetFieldId() > 0 ){
						$contents = $cprObj->GetForm( 'insertOption', $cprObj->GetFormId(), $cprObj->GetFieldId() );
						
						#SHOW FORM INFO DISPLAY
						$content = $cprObj->GetFormDisplayContents( $cprObj->GetFormId() );
						if( count($content) > 0 ){
							foreach( $content as $c ){
								$contents[] = $c;
							}
						}
						
						#SHOW FORM FIELD INFO DISPLAY
						$content = $cprObj->GetFormFieldDisplayContents( $cprObj->GetFieldId() );
						if( count($content) > 0 ){
							foreach( $content as $c ){
								$contents[] = $c;
							}
						}
						
						/* START: ADD FIELD FORM */
						#SHOW ADD FIELD OPTION TITLE
						$contents[] = array(
							'text' => zen_draw_label(
								INFO_HEAD_ADD_OPTION,
								'',
								'class="info-label-head"'
							)
						);
						
						/* FIELD-TYPE BASED OPTIONS */
						$show = $cprObj->GetWhatToShowFromField( $cprObj->GetThisFieldType() );
						$hidden_fields = '';
						
						#TEXT
						if( $show['text'] ){
							$contents[] = array(
								'text' => zen_draw_label(
									INFO_OPTION_TEXT_LABEL . ": ", 
									'', 
									'class="info-labels"'
								) . 
								zen_draw_input_field(
									'txtText', 
									'', 
									zen_set_field_length(
										TABLE_CUSTOM_FORMS_FIELDS_OPTIONS, 
										'field_text',
										20
									) . ' onKeyUp="FillUpValue(\''.$cprObj->GetThisFieldType().'\');"'
								)
							);
						}else{
							$hidden_fields .= zen_draw_hidden_field( 'txtText', '' );
						}
						
						#VALUE
						if( $show['value'] ){
							$contents[] = array(
								'text' => zen_draw_label(
									INFO_OPTION_VALUE_LABEL . ": ", 
									'', 
									'class="info-labels"'
								) . 
								zen_draw_input_field(
									'txtValue', 
									'', 
									zen_set_field_length(
										TABLE_CUSTOM_FORMS_FIELDS_OPTIONS, 
										'field_value',
										20
									)
								)
							);
						}else{
							$hidden_fields .= zen_draw_hidden_field( 'txtValue', '' );
						}
						
						#READ ONLY
						if( $show['read_only'] ){
							$contents[] = array(
								'text' => zen_draw_label(
									INFO_OPTION_READ_ONLY_LABEL . ": ", 
									'', 
									'class="info-labels"'
								) . 
								zen_draw_checkbox_field(
									'cbxReadOnly', 
									'1',
									''
								)
							);
						}else{
							$hidden_fields .= zen_draw_hidden_field( 'cbxReadOnly', '0' );
						}
						
						#SELECTED
						if( $show['selected'] ){
							$contents[] = array(
								'text' => zen_draw_label(
									INFO_OPTION_SELECTED_LABEL . ": ", 
									'', 
									'class="info-labels"'
								) . 
								zen_draw_checkbox_field(
									'cbxSelected', 
									'1',
									''
								)
							);
						}else{
							$hidden_fields .= zen_draw_hidden_field( 'cbxSelected', '0' );
						}
						
						#SORT ORDER
						if( $show['sort_order'] ){
							$contents[] = array(
								'text' => zen_draw_label(
									INFO_OPTION_ORDER_LABEL, 
									'', 
									'class="info-labels"'
								) . 
								zen_draw_input_field(
									'txtSortOrder', 
									'',
									zen_set_field_length(
										TABLE_CUSTOM_FORMS_FIELDS_OPTIONS, 
										'sort_order',
										20
									)
								)
							);
						}else{
							$hidden_fields .= zen_draw_hidden_field( 'txtSortOrder', '0' );
						}
						
						#BUTTONS
						$contents[] = array(
							'align' => 'center', 
							'text' => zen_image_submit(
										'button_add.gif', 
										BTN_ADD
									) . 
									' <a href="' . $cprObj->GetActionUrl('', $cprObj->GetFormId(), $cprObj->GetFieldId()) . '">' . 
										zen_image_button( 'button_cancel.gif', IMAGE_CANCEL ) . 
									'</a>' . 
									$hidden_fields
						);
					}else{ //MISSING FORM FIELD ID (ffID)
						$contents[] = array('text' => '<p>' . INFO_NO_FIELD_ID . '</p>');
						$contents[] = array(
							'align' => 'center', 
							'text' => '<a href="' . $cprObj->GetActionUrl('', $cprObj->GetFormId(), $cprObj->GetFieldId()) . '">' . 
										zen_image_button( 'button_cancel.gif', IMAGE_CANCEL ) . 
									'</a>'
						);
					}
				}else{ //MISSING FORM ID (fID)
					$contents[] = array('text' => '<p>' . INFO_NO_FORM_ID . '</p>');
					$contents[] = array(
							'align' => 'center', 
							'text' => '<a href="' . $cprObj->GetActionUrl('', $cprObj->GetFormId(), $cprObj->GetFieldId()) . '">' . 
										zen_image_button( 'button_cancel.gif', IMAGE_CANCEL ) . 
									'</a>'
						);
				}
				
				break;
			
			/********** EDIT (FORM FIELD OPTION) **********/
			case 'editOption':
				$heading[] = array(
					'text' => '<strong>' . INFO_HEAD_EDIT_FIELD_OPTION . '</strong>'
				);
				
				$contents = $cprObj->GetForm('updateOption', $cprObj->GetFormId(), $cprObj->GetFieldId(), $cprObj->GetOptionId());
				#LOAD DISPLAY FORM INFO
				if( $cprObj->GetFormId() > 0 ){
					$content = $cprObj->GetFormDisplayContents( $cprObj->GetFormId() );
					if( count($content) > 0 ){
						foreach( $content as $c ){
							$contents[] = $c;
						}
					}
				}
				
				#SHOW FORM FIELD INFO DISPLAY
				if( $cprObj->GetFieldId() > 0 ){
					$content = $cprObj->GetFormFieldDisplayContents( $cprObj->GetFieldId() );
					if( count($content) > 0 ){
						foreach( $content as $c ){
							$contents[] = $c;
						}
					}
				}
				
				#SHOW EDIT OPTION
				$contents[] = array(
					'text' => zen_draw_label(
							INFO_HEAD_SELECTED_OPTION_TITLE, 
							'', 
							'class="info-label-head"'
						)
				);
				
				$fieldOptionInfo = $cprObj->FieldOptionInfo( $cprObj->GetOptionId() );
				
				if( count($fieldOptionInfo) <= 0 ){
					$contents[] = array(
						'text' => '<p>' . MSG_OPTION_INFO_NOT_AVAILABLE . '</p>'
					);
				}else{
					$contents[] = array(
						'text' => 
						zen_draw_hidden_field( 'fID', (int)$cprObj->GetFormId() ) .
						zen_draw_hidden_field( 'ffID',$cprObj->GetFieldId() ) .
						zen_draw_hidden_field( 'oID', $cprObj->GetOptionId() ) .
						
						zen_draw_label( INFO_OPTION_ID . ": ", '', 'class="info-labels"' ) . 
						$fieldOptionInfo['id']
					);
					
					/* FIELD-TYPE BASED OPTIONS */
					$show = $cprObj->GetWhatToShowFromField( $cprObj->GetThisFieldType() );
					$hidden_fields = '';
					
					#TEXT
					if( $show['text'] ){
						$contents[] = array(
							'text' => 
							zen_draw_label(INFO_OPTION_TEXT_LABEL . ": ", '', 'class="info-labels"') .
							zen_draw_input_field(
								'txtText', 
								$fieldOptionInfo['text'], 
								zen_set_field_length(
									TABLE_CUSTOM_FORMS_FIELDS_OPTIONS, 
									'field_text',
									20
								) . 
								' onKeyUp="FillUpValue(\''.$cprObj->GetThisFieldType().'\');"'
							)
						);
					}else{
						$hidden_fields = zen_draw_hidden_field( 'txtText', '' );
					}
					
					#VALUE
					if( $show['value'] ){
						$contents[] = array(
							'text' => zen_draw_label(
								INFO_OPTION_VALUE_LABEL . ": ", 
								'', 
								'class="info-labels"'
							) . 
							zen_draw_input_field(
								'txtValue', 
								$fieldOptionInfo['value'], 
								zen_set_field_length(
									TABLE_CUSTOM_FORMS_FIELDS_OPTIONS, 
									'field_value',
									20
								)
							)
						);
					}else{
						$hidden_fields = zen_draw_hidden_field( 'txtValue', $fieldOptionInfo['value'] );
					}
					
					#READ ONLY (Y/N)
					if( $show['read_only'] ){
						$contents[] = array(
							'text' => zen_draw_label(
								INFO_OPTION_READ_ONLY_LABEL,
								'', 
								'class="info-labels"'
							) . 
							zen_draw_checkbox_field(
								'cbxReadOnly', 
								'1',
								( $fieldOptionInfo['read_only'] > 0 ? 'checked' : '' ),
								'',
								' onClick="UpdateOptionValue(\''.$cprObj->GetThisFieldType().'\');"'
							)
						);
					}
					
					#SELECTED
					if( $show['selected'] ){
						$contents[] = array(
							'text' => zen_draw_label(
								INFO_OPTION_SELECTED_LABEL,
								'', 
								'class="info-labels"'
							) . 
							zen_draw_checkbox_field(
								'cbxSelected', 
								'1',
								( $fieldOptionInfo['selected'] > 0 ? 'checked' : '' )
							)
						);
					}else{
						$hidden_fields = zen_draw_hidden_field( 'cbxSelected', '0' );
					}
					
					#SORT ORDER
					if( $show['sort_order'] ){
						$contents[] = array(
							'text' => zen_draw_label(
								INFO_OPTION_ORDER_LABEL . ": ", 
								'', 
								'class="info-labels"'
							) . 
							zen_draw_input_field(
								'txtSortOrder', 
								$fieldOptionInfo['sort_order'],
								zen_set_field_length(
									TABLE_CUSTOM_FORMS_FIELDS_OPTIONS, 
									'sort_order',
									20
								)
							)
						);
					}else{
						$hidden_fields = zen_draw_hidden_field( 'txtSortOrder', '0' );
					}
					#BUTTONS
					$contents[] = array(
						'align' => 'center', 
						'text' => zen_image_submit(
									'button_save.gif', 
									IMAGE_SAVE
								) . 
								' <a href="' . $cprObj->GetActionUrl('', $cprObj->GetFormId(), $cprObj->GetFieldId()) . '">' . 
									zen_image_button( 'button_cancel.gif', IMAGE_CANCEL ) . 
								'</a>' .
								$hidden_fields
					);
				}
				break;
			case 'getHitsReport':
				$heading[] = array(
					'text' => '<strong>' . REPORT_HITS_TITLE . '</strong>'
				);
				#BUTTONS
				$contents[] = $frm_buttons;
				#TITLE
				if( $cprObj->GetFormId() > 0 ){
					$contents[] = array(
						'text' => '<h1>' . REPORT_HITS_TITLE . '</h1>'
					);
				}
				#REPORT
				if( $cprObj->GetFormId() > 0 ){
					$contents[] = array( 'text' => $cprObj->GetHitsReport( $cprObj->GetFormId() ) );
				}
				#BUTTONS
				$contents[] = array(
					'align' => 'center', 
					'text' => '<a href="' . $cprObj->GetActionUrl('', $cprObj->GetFormId()) . '">' . 
								zen_image_button( 'button_back.gif', IMAGE_BACK ) . 
							'</a>'
				);
				break;
			case 'getWidget':
				$heading[] = array(
					'text' => '<strong>' . REPORT_WIDGET_TITLE . '</strong>'
				);
				#BUTTONS
				$contents[] = $frm_buttons;
				#TITLE
				if( $cprObj->GetFormId() > 0 ){
					$contents[] = array(
						'text' => '<h1>' . REPORT_WIDGET_TITLE . '</h1>'
					);
				}
				
				#WIDGET
				$contents[] = array( 'text' => zen_draw_label(
								LABEL_BUTTON_WIDGET,
								'widget',
								'class="info-label"'
							) . $cprObj->GetWidget( $cprObj->GetFormId() )
					);
				#COPY BUTTON
				$contents[] = array(
					'align' => 'center', 
					'text' => zen_image_button( 'button_copy.gif', IMAGE_COPY, 'onClick="CopyWidget();" id="btnCopy"' )
				);
				$contents[] = array( 'text' => zen_draw_label(
								LABEL_WIDGET_URL,
								'widget',
								'class="info-label"'
							) . 
							'<div id="linkBox">'.$cprObj->GetWidgetLink( $cprObj->GetFormId() ).'</div>'
					);
				#BACK BUTTON
				$contents[] = array(
					'align' => 'center', 
					'text' => '<a href="' . $cprObj->GetActionUrl('', $cprObj->GetFormId()) . '">' .
								zen_image_button( 'button_back.gif', IMAGE_BACK ) .
							'</a>'
				);
				break;
			default:
				break;
		}
	}else if( $cprObj->GetFormId() > 0 ){
		#NO ACTION BUT FORM SELECTED
		#SHOW FORM INFO
		$heading[] = array('text' => '<strong>' . INFO_HEAD_FORM_NAME . $cprObj->GetFormTitle() . '</strong>');
		
		$contents[] = $frm_buttons;
		
		#LOAD FORM INFO
		$content = $cprObj->GetFormDisplayContents( $cprObj->GetFormId() );
		if( count($content) > 0 ){
			foreach( $content as $c ){
				$contents[] = $c;
			}
		}
		
		/* SHOW FORM INFO: FIELDS */
		if( count($cprObj->GetFields()) > 0 ){
			$contents[] = array(
					'text' => 
					zen_draw_label(
						INFO_HEAD_FIELDS_SECTION_TITLE,
						'',
						'class="info-label-head"'
					) .
					zen_image_button(
						'button_add.gif', 
						BTN_ADD,
						'style="float:right;" onClick="window.location=\'' . $cprObj->GetActionUrl('addField',$cprObj->GetFormId()) . '\'" class="btn"'
					)
			);
			foreach( $cprObj->GetFields() as $field ){
				#CREATE THE ACTION BUTTONS
				$content = '<div class="listBox">';
				$content .= '<a href="' . $cprObj->GetActionUrl('editField', $cprObj->GetFormId(), $field['id']) . '">' .
						zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . 
					'</a>';
				$content .= '<a href="' . $cprObj->GetActionUrl('deleteFieldConfirm', $cprObj->GetFormId(), $field['id']) . '">' .
						zen_image(DIR_WS_IMAGES . 'icon_delete.gif', ICON_DELETE) . 
					'</a>';
				$content .= 
						zen_draw_label( 
							INFO_FIELD_TYPE_LABEL . ": ",
							'',
							'class=info-labels'
						) . $field['type'] . '<br />';
				$content .= 
						zen_draw_label( 
							INFO_FIELD_LABEL_LABEL . ": ",
							'',
							'class=info-labels'
						) . $field['label'] . '<br />';
				$content .= 
						zen_draw_label( 
							INFO_FIELD_NAME_LABEL . ": ",
							'',
							'class=info-labels'
						) . $field['name'] . '<br />';
				$content .= 
						zen_draw_label( 
							INFO_FIELD_REQUIRED_LABEL,
							'',
							'class=info-labels'
						) . ((int)$field['required']>0?'Ja':'Nein') . '<br />';
				$content .= 
						zen_draw_label( 
							INFO_FIELD_ORDER_LABEL . ": ",
							'',
							'class=info-labels'
						) . 
						$field['sort_order'] . '<br />';
				if( $field['description'] != ''){
					$content .= 
						zen_draw_label( 
							INFO_FIELD_DESCRIPTION_LABEL . ": ",
							'',
							'class=info-labels'
						) . $field['description'] . '<br />';
				}
				
				#LOAD OPTIONS
				if( $field['type'] != 'File' ){
					$content .= $cprObj->GetOptionsListDisplayContents( $field );
				}
				
				$content .= '</div>';
				
				$contents[] = array( 'text' => $content );
			} //END: FOREACH
		}else{
			#NO FIELD AVAILABLE: SHOW ADD BUTTON
			$contents[] = array(
					'text' => zen_draw_label( INFO_HEAD_FIELDS_SECTION_TITLE,
					'',
					'class="info-label-head"') . 
					zen_image_button(
						'button_add.gif', 
						BTN_ADD,
						'style="float:right;" onClick="window.location=\'?action=addField&fID=' . $cprObj->GetFormId() . '\'" class="btn"'
					)
				);
		} //END: IF FIELDS AVAILABLE
	} //END: IF NO ACTION AND FORM ID AVAILABLE
?>
					<td class="noprint" valign="top" style="width:25%; min-width:200px;">
<?php
	if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
		$box = new box;
		echo $box->infoBox($heading, $contents);
	}else if( !isset($_GET['action']) or $_GET['action'] != 'addForm' ){
		echo INFO_NO_FORM;
	}
?>
					</td>
				</tr>
			</table>
		</td>	
	</tr>
</table>
</div>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>

</body>
<script type="text/javascript">
	$(document).ready( FormController() );
	
	var highlighted = [];
	
	function FormController(){
		if( $("[name=txtType]").val() != undefined ){
			GetNewFields( $("[name=txtType]").val() );
		}
	}
	
	function GetNewFields( type ){
		ResetDisplay();
		switch( type ){
<?php
	foreach( $cprObj->GetUnavailableFieldAttributes() as $type => $attributes){
		echo "\t\t\t" . 'case "'.$type.'":' . "\n";
		foreach($attributes as $attr){
			switch($attr){
				case 'field_name':
					echo "\t\t\t\t" . '$("[name=lblName]").css("display", "none");' . "\n";
					echo "\t\t\t\t" . '$("[name=txtName]").css("display", "none");' . "\n";
					break;
				case 'label':
					echo "\t\t\t\t" . '$("[name=lblLabel]").css("display", "none");' . "\n";
					echo "\t\t\t\t" . '$("[name=txtLabel]").css("display", "none");' . "\n";
					break;
				case 'description':
					echo "\t\t\t\t" . '$("[name=lblDescription]").css("display", "none");' . "\n";
					echo "\t\t\t\t" . '$("[name=txtDescription]").css("display", "none");' . "\n";
					break;
				case 'required':
					echo "\t\t\t\t" . '$("[name=lblRequired]").css("display", "none");' . "\n";
					echo "\t\t\t\t" . '$("[name=rbxRequired]").css("display", "none");' . "\n";
					break;
				default:
					break;
			}
		}
		echo "\t\t\t\t" . 'break;' . "\n";
	}
	echo "\t\t\t" . 'default:' . "\n";
	echo "\t\t\t\t" . 'break;' . "\n";
?>
		}
	}
	
	function ResetDisplay(){
		$("[name=lblLabel]").css('display', 'inline-block');
		$("[name=txtLabel]").css('display', 'inline-block');
		
		$("[name=lblName]").css('display', 'inline-block');
		$("[name=txtName]").css('display', 'inline-block');
		
		$("[name=lblDescription]").css('display', 'inline-block');
		$("[name=txtDescription]").css('display', 'inline-block');
		
		$("[name=lblRequired]").css('display', 'inline-block');
		$("[name=rbxRequired]").css('display', 'inline-block');
	}
	
	/* UTILS LIBRARY */
	function EmptyElement( el ){
		while (el.hasChildNodes()) {
			el.removeChild(el.lastChild);
		}
	}
	
	function RemoveElement( elName ){
		var el = document.getElementById( elName );
		el.parentNode.removeChild( el );
	}
	
	function FillUpName(){
		/* AUTO GENERATES A FIELD NAME */
		var prefix = 'input';
		var label = $("[name=txtLabel]").val();
		var name = CamelCase( label );
		
		switch( $("[name=txtType").val() ){
			case 'Dropdown':
				prefix = 'cbx';
				break;
			case 'Text':
				prefix = 'txt';
				break;
			case 'Text Area':
				prefix = 'txa';
				break;
			case 'Radio':
				prefix = 'rbx';
				break;
			case 'Checkbox':
				prefix = 'cbx';
				break;
			case 'File':
				prefix = 'btn';
				break;
			default:
				break;
		}
		name = name.replace(/\W/g, '');
		$("[name=txtName]").val( prefix + name );
	}
	
	function FillUpValue( fieldType ){
		/* AUTO-GENERATES UNIQUE PLACEHOLDER VALUES FOR 
		 * DROP-DOWN, RADIO AND CHECKBOX FIELDS. THOSE
		 * VALUES ARE MAPPED TO THE ORIGINAL LABEL LATER.
		 */
		if( $("[name=cbxReadOnly]").is(':checked') ){
			return; /* NO VALUE */
		}
		/* AUTO GENERATES AN OPTION VALUE */
		var prefix = '';
		var label = $("[name=txtText]").val();
		var value = CamelCase( label );
		
		switch( fieldType ){
			case 'Dropdown':
				prefix = 'ddo';
				break;
			case 'Radio':
				prefix = 'ro';
				break;
			case 'Checkbox':
				prefix = 'cbo';
				break;
			default:
				break;
		}
		value = value.replace(/\W/g, '');
		if( prefix != '' ){
			$("[name=txtValue]").val( prefix + value );
		}
	}
	
	function UpdateOptionValue( fieldType ){
		if( $("[name=cbxReadOnly]").is(':checked') ){
			$("[name=txtValue]").val("");
		}else{
			FillUpValue(fieldType);
		}
	}
	
	function CamelCase(str) {
		var output = '';
		var arr = str.split(" ");
		
		if( arr[0] != '' ){
			arr[0] = arr[0].toLowerCase();
			output += arr[0].replace(arr[0].charAt(0), arr[0].charAt(0).toUpperCase());
		}
		
		for( var i = 1; i < arr.length; i++ ){
			if( arr[i] != undefined ){
				arr[i] = arr[i].toLowerCase();
				output += arr[i].replace(arr[i].charAt(0), arr[i].charAt(0).toUpperCase());
			}
		}
		
		return output;
	}
	
	function CheckAddField(){
		if( $("[name=txtLabel]").val() != '' && $("[name=txtName]").val() != '' ){
			return true;
		}
		
		if( $("[name=txtLabel]").val() == '' ){
			SetHighlightElementName('txtLabel');
		}
		
		if( $("[name=txtName]").val() == '' ){
			SetHighlightElementName('txtName');
		}
		
		return false;
	}
	
	function SetHighlightElementName( name ){
		highlighted.push(name);
		$("[name=" + name + "]").css("border", "solid 2px red");
	}
	
	function UnsetHighlightElementName( name ){
		highlighted.pop(name);
		$("[name=" + name + "]").css("border", "none");
	}
	
	function InputChange(el){
		/* RESET ANY HIGHLIGHT */
		if(highlighted.length > 0){
			for(var i = 0; i < highlighted.length; i++){
				UnsetHighlightElementName( highlighted[i] );
			}
		}
	}
	
	function CopyWidget(){
		var copyText = document.getElementById("widget");
		copyText.style.display = 'inline-block';
		copyText.select();
		document.execCommand("copy");
		copyText.style.display = 'none';
		document.getElementById("btnCopy").style.display = "none";
	}
</script>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>