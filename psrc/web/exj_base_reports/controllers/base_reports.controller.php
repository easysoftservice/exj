<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppBaseReportsController
 * Controlador base para Reportes
 */
class AppBaseReportsController extends ExjController {
	private $_canViewFile = false;
	
	public function dispatch() {
		// global $exj;
		$response = new ExjResponse();
		$response->fixFormatHTML();
		
		$baseReportParams = new stdClass();
		
		$baseReportParams->nameCmp = ExjRequest::GetParam('nameCmp');
		$baseReportParams->nameTmpl = ExjRequest::GetParam('nameTmpl');
		$baseReportParams->dataRpt = ExjRequest::GetParam('dataRpt');
		$baseReportParams->outPrint = ExjRequest::GetParam('outPrint', 0);
		$baseReportParams->outScreen = ExjRequest::GetParam('outScreen', 0);
		$baseReportParams->isPreView = ExjRequest::GetParam('isPreView', 0);
		
		if ($baseReportParams->isPreView == 'false') {
			$baseReportParams->isPreView = 0;
		}
		
	//	$baseReportParams->isPreView = 1;
		
		if (!$baseReportParams->nameCmp) {
			return $response->setMsgError("No se indicó componente!");
		}
		$baseReportParams->nameCmp = trim($baseReportParams->nameCmp);
		if (strlen($baseReportParams->nameCmp) <= 8) {
			return $response->setMsgError("Nombre de componente no válido!");
		}
		
		if (!$baseReportParams->outPrint && !$baseReportParams->outScreen) {
			$outScreen = true;
		}
		
		if ($baseReportParams->dataRpt) {
			$baseReportParams->dataRpt = Exj::JsonDecode($baseReportParams->dataRpt);
		}
		else {
			$baseReportParams->dataRpt = null;
		}
		
		if (!$baseReportParams->nameTmpl) {
			$baseReportParams->nameTmpl = substr($baseReportParams->nameCmp, 7);
		}
		
		
//		$fullPathFile = AppBaseReportsModel::DecodePathFile($pathFile, $entrada, $isPathFull);

	//	print_r($baseReportParams->dataRpt);
		
		// $response->setMsgInfo('En construcción ' . __METHOD__);
		
		AppBaseReportsModel::IncludeReport($response, $baseReportParams);
		
		return $response;
	}

}

?>