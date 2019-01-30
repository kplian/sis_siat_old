CREATE OR REPLACE FUNCTION "siat"."ft_firma_usuario_ime" (
				p_administrador integer, p_id_usuario integer, p_tabla character varying, p_transaccion character varying)
RETURNS character varying AS
$BODY$

/**************************************************************************
 SISTEMA:		Gestionar Firmas Digital
 FUNCION: 		siat.ft_firma_usuario_ime
 DESCRIPCION:   Funcion que gestiona las operaciones basicas (inserciones, modificaciones, eliminaciones de la tabla 'gfd.tfirma_usuario'
 AUTOR: 		 (miguel.mamani)
 FECHA:	        23-01-2019 13:35:04
 COMENTARIOS:	
***************************************************************************
 HISTORIAL DE MODIFICACIONES:
#ISSUE				FECHA				AUTOR				DESCRIPCION
 #0				23-01-2019 13:35:04								Funcion que gestiona las operaciones basicas (inserciones, modificaciones, eliminaciones de la tabla 'gfd.tfirma_usuario'	
 #
 ***************************************************************************/

DECLARE

	v_nro_requerimiento    	integer;
	v_parametros           	record;
	v_id_requerimiento     	integer;
	v_resp		            varchar;
	v_nombre_funcion        text;
	v_mensaje_error         text;
	v_id_firma_usuario	integer;
			    
BEGIN

    v_nombre_funcion = 'siat.ft_firma_usuario_ime';
    v_parametros = pxp.f_get_record(p_tabla);

	/*********************************    
 	#TRANSACCION:  'GFD_FDU_INS'
 	#DESCRIPCION:	Insercion de registros
 	#AUTOR:		miguel.mamani	
 	#FECHA:		23-01-2019 13:35:04
	***********************************/

	if(p_transaccion='GFD_FDU_INS')then
					
        begin
        	--Sentencia de la insercion
        	insert into siat.tfirma_usuario(
			id_usuario,
			estado_reg,
			password,
			url_firma,
			extencion,
			id_usuario_reg,
			usuario_ai,
			fecha_reg,
			id_usuario_ai,
			fecha_mod,
			id_usuario_mod
          	) values(
			v_parametros.id_usuario,
			'activo',
			v_parametros.password,
			v_parametros.url_firma,
			v_parametros.extencion,
			p_id_usuario,
			v_parametros._nombre_usuario_ai,
			now(),
			v_parametros._id_usuario_ai,
			null,
			null
							
			
			
			)RETURNING id_firma_usuario into v_id_firma_usuario;
			
			--Definicion de la respuesta
			v_resp = pxp.f_agrega_clave(v_resp,'mensaje','Firma Usuario almacenado(a) con exito (id_firma_usuario'||v_id_firma_usuario||')'); 
            v_resp = pxp.f_agrega_clave(v_resp,'id_firma_usuario',v_id_firma_usuario::varchar);

            --Devuelve la respuesta
            return v_resp;

		end;

	/*********************************    
 	#TRANSACCION:  'GFD_FDU_MOD'
 	#DESCRIPCION:	Modificacion de registros
 	#AUTOR:		miguel.mamani	
 	#FECHA:		23-01-2019 13:35:04
	***********************************/

	elsif(p_transaccion='GFD_FDU_MOD')then

		begin
			--Sentencia de la modificacion
			update siat.tfirma_usuario set
			id_usuario = v_parametros.id_usuario,
			password = v_parametros.password,
			url_firma = v_parametros.url_firma,
			extencion = v_parametros.extencion,
			fecha_mod = now(),
			id_usuario_mod = p_id_usuario,
			id_usuario_ai = v_parametros._id_usuario_ai,
			usuario_ai = v_parametros._nombre_usuario_ai
			where id_firma_usuario=v_parametros.id_firma_usuario;
               
			--Definicion de la respuesta
            v_resp = pxp.f_agrega_clave(v_resp,'mensaje','Firma Usuario modificado(a)'); 
            v_resp = pxp.f_agrega_clave(v_resp,'id_firma_usuario',v_parametros.id_firma_usuario::varchar);
               
            --Devuelve la respuesta
            return v_resp;
            
		end;

	/*********************************    
 	#TRANSACCION:  'GFD_FDU_ELI'
 	#DESCRIPCION:	Eliminacion de registros
 	#AUTOR:		miguel.mamani	
 	#FECHA:		23-01-2019 13:35:04
	***********************************/

	elsif(p_transaccion='GFD_FDU_ELI')then

		begin
			--Sentencia de la eliminacion
			delete from siat.tfirma_usuario
            where id_firma_usuario=v_parametros.id_firma_usuario;
               
            --Definicion de la respuesta
            v_resp = pxp.f_agrega_clave(v_resp,'mensaje','Firma Usuario eliminado(a)'); 
            v_resp = pxp.f_agrega_clave(v_resp,'id_firma_usuario',v_parametros.id_firma_usuario::varchar);
              
            --Devuelve la respuesta
            return v_resp;

		end;
         
	else
     
    	raise exception 'Transaccion inexistente: %',p_transaccion;

	end if;

EXCEPTION
				
	WHEN OTHERS THEN
		v_resp='';
		v_resp = pxp.f_agrega_clave(v_resp,'mensaje',SQLERRM);
		v_resp = pxp.f_agrega_clave(v_resp,'codigo_error',SQLSTATE);
		v_resp = pxp.f_agrega_clave(v_resp,'procedimientos',v_nombre_funcion);
		raise exception '%',v_resp;
				        
END;
$BODY$
LANGUAGE 'plpgsql' VOLATILE
COST 100;
ALTER FUNCTION "siat"."ft_firma_usuario_ime"(integer, integer, character varying, character varying) OWNER TO postgres;
