<script type="text/javascript"><!--
	function ValidateForm(frm) {
		var valid = true;
		var required = [];
<?php
	if( is_array($cf->GetRequiredFields()) ){
		echo "\t\t" . "required = ['" . implode("','", $cf->GetRequiredFields()) . "'];" . "\n";
	}
?>
		if( required.length > 0 ){
			for(var i = 0; i<required.length; i++){
				if( document.getElementsByName( required[i] )[0].value == '' ){
					document.getElementsByName( required[i] )[0].style.border = "2px solid #FF0000";
					valid = false;
				}else{
					document.getElementsByName( required[i] )[0].style.border = "inherit";
				}
			}
		}
		if( !valid ){
			alert("Required field missing!");
		}
		return valid;
	}
//--></script>