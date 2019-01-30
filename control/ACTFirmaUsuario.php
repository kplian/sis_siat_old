<?php
/**
*@package pXP
*@file gen-ACTFirmaUsuario.php
*@author  (miguel.mamani)
*@date 23-01-2019 13:35:04
*@description Clase que recibe los parametros enviados por la vista para mandar a la capa de Modelo
*/

class ACTFirmaUsuario extends ACTbase{    
			
	function listarFirmaUsuario(){
		$this->objParam->defecto('ordenacion','id_firma_usuario');

		$this->objParam->defecto('dir_ordenacion','asc');
		if($this->objParam->getParametro('tipoReporte')=='excel_grid' || $this->objParam->getParametro('tipoReporte')=='pdf_grid'){
			$this->objReporte = new Reporte($this->objParam,$this);
			$this->res = $this->objReporte->generarReporteListado('MODFirmaUsuario','listarFirmaUsuario');
		} else{
			$this->objFunc=$this->create('MODFirmaUsuario');
			
			$this->res=$this->objFunc->listarFirmaUsuario($this->objParam);
		}
		$this->res->imprimirRespuesta($this->res->generarJson());
	}
				
	function insertarFirmaUsuario(){
		$this->objFunc=$this->create('MODFirmaUsuario');	
		if($this->objParam->insertar('id_firma_usuario')){
			$this->res=$this->objFunc->insertarFirmaUsuario($this->objParam);			
		} else{			
			$this->res=$this->objFunc->modificarFirmaUsuario($this->objParam);
		}
		$this->res->imprimirRespuesta($this->res->generarJson());
	}
						
	function eliminarFirmaUsuario(){
			$this->objFunc=$this->create('MODFirmaUsuario');	
		$this->res=$this->objFunc->eliminarFirmaUsuario($this->objParam);
		$this->res->imprimirRespuesta($this->res->generarJson());
	}
			
}

?>