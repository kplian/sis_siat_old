<?php
/**
*@package pXP
*@file gen-MODFirmaUsuario.php
*@author  (miguel.mamani)
*@date 23-01-2019 13:35:04
*@description Clase que envia los parametros requeridos a la Base de datos para la ejecucion de las funciones, y que recibe la respuesta del resultado de la ejecucion de las mismas
*/

class MODFirmaUsuario extends MODbase{
	
	function __construct(CTParametro $pParam){
		parent::__construct($pParam);
	}
			
	function listarFirmaUsuario(){
		//Definicion de variables para ejecucion del procedimientp
		$this->procedimiento='gfd.ft_firma_usuario_sel';
		$this->transaccion='GFD_FDU_SEL';
		$this->tipo_procedimiento='SEL';//tipo de transaccion
				
		//Definicion de la lista del resultado del query
		$this->captura('id_firma_usuario','int4');
		$this->captura('id_usuario','int4');
		$this->captura('estado_reg','varchar');
		$this->captura('password','varchar');
		$this->captura('url_firma','varchar');
		$this->captura('extencion','varchar');
		$this->captura('id_usuario_reg','int4');
		$this->captura('usuario_ai','varchar');
		$this->captura('fecha_reg','timestamp');
		$this->captura('id_usuario_ai','int4');
		$this->captura('fecha_mod','timestamp');
		$this->captura('id_usuario_mod','int4');
		$this->captura('usr_reg','varchar');
		$this->captura('usr_mod','varchar');
		
		//Ejecuta la instruccion
		$this->armarConsulta();
		$this->ejecutarConsulta();
		
		//Devuelve la respuesta
		return $this->respuesta;
	}
			
	function insertarFirmaUsuario(){
		//Definicion de variables para ejecucion del procedimiento
		$this->procedimiento='gfd.ft_firma_usuario_ime';
		$this->transaccion='GFD_FDU_INS';
		$this->tipo_procedimiento='IME';
				
		//Define los parametros para la funcion
		$this->setParametro('id_usuario','id_usuario','int4');
		$this->setParametro('estado_reg','estado_reg','varchar');
		$this->setParametro('password','password','varchar');
		$this->setParametro('url_firma','url_firma','varchar');
		$this->setParametro('extencion','extencion','varchar');

		//Ejecuta la instruccion
		$this->armarConsulta();
		$this->ejecutarConsulta();

		//Devuelve la respuesta
		return $this->respuesta;
	}
			
	function modificarFirmaUsuario(){
		//Definicion de variables para ejecucion del procedimiento
		$this->procedimiento='gfd.ft_firma_usuario_ime';
		$this->transaccion='GFD_FDU_MOD';
		$this->tipo_procedimiento='IME';
				
		//Define los parametros para la funcion
		$this->setParametro('id_firma_usuario','id_firma_usuario','int4');
		$this->setParametro('id_usuario','id_usuario','int4');
		$this->setParametro('estado_reg','estado_reg','varchar');
		$this->setParametro('password','password','varchar');
		$this->setParametro('url_firma','url_firma','varchar');
		$this->setParametro('extencion','extencion','varchar');

		//Ejecuta la instruccion
		$this->armarConsulta();
		$this->ejecutarConsulta();

		//Devuelve la respuesta
		return $this->respuesta;
	}
			
	function eliminarFirmaUsuario(){
		//Definicion de variables para ejecucion del procedimiento
		$this->procedimiento='gfd.ft_firma_usuario_ime';
		$this->transaccion='GFD_FDU_ELI';
		$this->tipo_procedimiento='IME';
				
		//Define los parametros para la funcion
		$this->setParametro('id_firma_usuario','id_firma_usuario','int4');

		//Ejecuta la instruccion
		$this->armarConsulta();
		$this->ejecutarConsulta();

		//Devuelve la respuesta
		return $this->respuesta;
	}
			
}
?>