<?php

$hReport = AppBaseReportTmplHandler::_CreateInstanceRepContent();

/*
<!DOCTYPE html>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
charset="UTF-8"
charset="ISO-8859-1"
*/

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo AppBaseReportTmplHandler::GetPathCSS('exj_base_reports', 'vu_rep.screen'); ?>" />
<link rel="stylesheet" type="text/css" media="print" href="<?php echo AppBaseReportTmplHandler::GetPathCSS('exj_base_reports', 'vu_rep.printer'); ?>" />

<?php
	$hReport->writeLinksStyles();
?>

</head>
<body <?php echo $hReport->getAttrBody(); ?> >
<script type="text/javascript">

	function setValueCmpRep(field, value){
		var elCmp = document.getElementById(field);
		if(elCmp){
			elCmp.value = value;
		}
	};
	
	function getValuesReport(){
		var valuesReport = null;
		// document.getElementsByTagName('input')[1].value

		var fieldsInputs = document.getElementsByTagName('input');
		if(fieldsInputs.length > 0){
			valuesReport = {};
			
			for(var i=0, fieldInput, valueField, nameField; i < fieldsInputs.length; i++){
				fieldInput = fieldsInputs[i];
				if(fieldInput.readOnly){
					continue;
				}
				
				nameField = fieldInput.id;
				if(!nameField){
					nameField = fieldInput.name;
					if(!nameField){
						continue;
					}
				}
				
				valueField = fieldInput.value;
				
				if((fieldInput.id.indexOf("id_") == 0|| fieldInput.id.indexOf("es_") == 0) && !isNaN(parseInt(valueField))){
					valueField = parseInt(valueField);
				}
				
				valuesReport[nameField] = valueField;
				
				/*
				valuesReport.push({
					name: nameField,
					value: valueField,
				});
				*/
			}
		}

		return valuesReport;
	};
	
	function imprimirReporte(params) {
		if(params && (typeof params == "object")){
			var valuesReport = getValuesReport();
			
			if(params.beforePrint){
				if(params.beforePrint(valuesReport) === false){
					return;
				}
			}
			
			if(params.callbackPrint){
				params.callbackPrint({
					valuesReport: valuesReport,
					print: function(){
						window.print(); 
					},
					setValueCmp: setValueCmpRep
				});
				
				return;
			}
		}
		
		window.print(); 
	};
	
	var AppBaseReport = {
		_fnCallbackCambioNumFac: null,
		setActionCambioNumFac: function(fnCallback){
			this._fnCallbackCambioNumFac = fnCallback;
		},
		notificarCambioNumFac: function(senderCmp){
			// alert('notificarCambioNumFac valueNumFac: '+senderCmp.innerHTML);
			if(!this._fnCallbackCambioNumFac){
				return;
			}
			
			var numFacActual = null;
			if(senderCmp){
				numFacActual = senderCmp.innerHTML;
			}
			
			this._fnCallbackCambioNumFac({
				senderCmp: senderCmp,
				numFacActual: numFacActual
			});
		}
	};
	
	
	
</script>
<?php


$hReport->printHTML();
if ($hReport->haveItemsForProcess()) {
	$maxTest = 6000;
	while ($hReport->haveItemsForProcess()) {
		echo $hReport->getDivPageBreak();
		$hReport->printHTML();
		
		if (--$maxTest <= 0) {
			break;
		}
		
	//	echo '<br/>NumItemsForProcess: '.$hReport->getNumItemsForProcess();
	}
}

?>
</body>
</html>