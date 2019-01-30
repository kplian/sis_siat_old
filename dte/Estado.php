<?php
/**
 *@package pXP
 *@file FirmaElectronica.php
 *@author  (miguel.mamani)
 *@date 24/01/2019
 *@description Clase con códigos y glosas de estados (generalmente errores) de LibreDTE
 */
namespace dte\firma;

class Estado{
    // códigos de error para \dte\firma\FirmaElectronica
    const FIRMA_ERROR = 101;
    // códigos de error para \sasco\LibreDTE\Sii\Dte
    const DTE_ERROR_GETDATOS = 401;
    const DTE_ERROR_TIPO = 402;
    const DTE_ERROR_RANGO_FOLIO = 403;
    const DTE_FALTA_FCHEMIS = 404;
    const DTE_FALTA_MNTTOTAL = 405;
    const DTE_ERROR_TIMBRE = 406;
    const DTE_ERROR_FIRMA = 407;
    const DTE_ERROR_LOADXML = 408;



    // glosas de los estados
    private static $glosas = [
        // códigos de error para \sasco\LibreDTE\FirmaElectronica
        self::FIRMA_ERROR => '%s',
        // códigos de error para \sasco\LibreDTE\Sii\Dte
        self::DTE_ERROR_GETDATOS => 'No fue posible convertir el XML a arreglo para extraer los datos del DTE',
        self::DTE_ERROR_TIPO => 'No existe la definición del tipo de documento para el código %d',
        self::DTE_ERROR_RANGO_FOLIO => 'Folio del DTE %s está fuera de rango',
        self::DTE_FALTA_FCHEMIS => 'Falta FchEmis del DTE %s',
        self::DTE_FALTA_MNTTOTAL => 'Falta MntTotal del DTE %s',
        self::DTE_ERROR_TIMBRE => 'No se pudo generar el timbre del DTE %s',
        self::DTE_ERROR_FIRMA => 'No se pudo generar la firma del DTE %s',
        self::DTE_ERROR_LOADXML => 'No fue posible cargar el XML del DTE',

    ];

    /**
     * Método que recupera la glosa del estado
     * @param codigo Código del error que se desea recuperar
     * @param args Argumentos que se usarán para reemplazar "máscaras" en glosa
     * @return Glosa del estado si existe o bien el mismo código del estado si no hay glosa
     */
    public static function get($codigo, $args = null){
        // si no hay glosa asociada al código se entrega el mismo código
        if (!isset(self::$glosas[(int)$codigo]))
            return (int)$codigo;
        // si los argumentos no son un arreglo se obtiene arreglo a partir
        // de los argumentos pasados a la función
        if (!is_array($args))
            $args = array_slice(func_get_args(), 1);
        // entregar glosa
        return vsprintf(I18n::translate(self::$glosas[(int)$codigo], 'estados'), $args);
    }

}
