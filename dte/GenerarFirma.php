<?php
/**
 *@package pXP
 *@file Firmar.php
 *@author  (miguel.mamani)
 *@date 23-01-2019 13:35:04
 *@description Clase que recibe los parametros enviados por la vista para mandar a la capa de Modelo
 */
namespace dte\firma\Sii;
include "/XML.php";
include "/Log.php";
include "/Estado.php";
include "/FirmaElectronica.php";
class GenerarFirma{
    private $xml; ///< Objeto XML que representa el DTE
    private $timestamp; ///< Timestamp del DTE
    private $tipo; ///< Identificador del tipo de DTE: 33 (factura electrónica)
    private $folio; ///< Folio del documento
    private $id; ///< Identificador único del DTE
    private $tipo_general; ///< Tipo general de DTE: Documento, Liquidacion o Exportaciones
    public function __construct($datos, $normalizar = true){
        if (is_array($datos)) {
            $this->setDatos($datos, $normalizar);
        }
    }
    private function setDatos(array $datos, $normalizar = true){
        if (!empty($datos)) {
            $this->tipo = $datos['Encabezado']['IdDoc']['TipoDTE'];
            $this->folio = $datos['Encabezado']['IdDoc']['Folio'];
            $this->id = 'LibreDTE_T'.$this->tipo.'F'.$this->folio;
            if ($normalizar) {
                $this->normalizar($datos);
                $method = 'normalizar_'.$this->tipo;
                if (method_exists($this, $method))
                    $this->$method($datos);
                $this->normalizar_final($datos);
            }
            $this->tipo_general = $this->getTipoGeneral($this->tipo);
            $this->xml = (new \dte\firma\XML())->generate([
                'DTE' => [
                    '@attributes' => [
                        'version' => '1.0',
                    ],
                    $this->tipo_general => [
                        '@attributes' => [
                            'ID' => $this->id
                        ],
                    ]
                ]
            ]);
            $parent = $this->xml->getElementsByTagName($this->tipo_general)->item(0);
            $this->xml->generate($datos + ['TED' => null], null, $parent);
            $this->datos = $datos;
            if ($normalizar and !$this->verificarDatos()) {
                return false;
            }
            return $this->schemaValidate();
        }

        return false;
    }
    /**
     * Método que normaliza los datos de un documento tributario electrónico
     * @param datos Arreglo con los datos del documento que se desean normalizar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-06-25
     */
    private function normalizar(array &$datos){

        $datos = $this->mergeRecursiveDistinct([
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => false,
                    'Folio' => false,
                    'FchEmis' => date('Y-m-d'),
                    'IndNoRebaja' => false,
                    'TipoDespacho' => false,
                    'IndTraslado' => false,
                    'TpoImpresion' => false,
                    'IndServicio' =>  false,
                    'MntBruto' => false,
                    'TpoTranCompra' => false,
                    'TpoTranVenta' => false,
                    'FmaPago' => false,
                    'FmaPagExp' => false,
                    'MntCancel' => false,
                    'SaldoInsol' => false,
                    'FchCancel' => false,
                    'MntPagos' => false,
                    'PeriodoDesde' => false,
                    'PeriodoHasta' => false,
                    'MedioPago' => false,
                    'TpoCtaPago' => false,
                    'NumCtaPago' => false,
                    'BcoPago' => false,
                    'TermPagoCdg' => false,
                    'TermPagoGlosa' => false,
                    'TermPagoDias' => false,
                    'FchVenc' => false,
                ],
                'Emisor' => [
                    'RUTEmisor' => false,
                    'RznSoc' => false,
                    'GiroEmis' => false,
                    'Telefono' => false,
                    'CorreoEmisor' => false,
                    'Acteco' => false,
                    'Sucursal' => false,
                    'CdgSIISucur' => false,
                    'DirOrigen' => false,
                    'CmnaOrigen' => false,
                    'CiudadOrigen' => false,
                    'CdgVendedor' => false,
                ],
                'Receptor' => [
                    'RUTRecep' => false,
                    'CdgIntRecep' => false,
                    'RznSocRecep' => false,
                    'Extranjero' => false,
                    'GiroRecep' => false,
                    'Contacto' => false,
                    'CorreoRecep' => false,
                    'DirRecep' => false,
                    'CmnaRecep' => false,
                    'CiudadRecep' => false,
                    'DirPostal' => false,
                    'CmnaPostal' => false,
                    'CiudadPostal' => false,
                ],
                'Totales' => [
                    'TpoMoneda' => false,
                ],
            ],
            'Detalle' => false,
            'SubTotInfo' => false,
            'DscRcgGlobal' => false,
            'Referencia' => false,
            'Comisiones' => false,
        ], $datos);
        // corregir algunos datos que podrían venir malos para no caer por schema
        $datos['Encabezado']['Emisor']['RUTEmisor'] = strtoupper(trim(str_replace('.', '', $datos['Encabezado']['Emisor']['RUTEmisor'])));
        $datos['Encabezado']['Receptor']['RUTRecep'] = strtoupper(trim(str_replace('.', '', $datos['Encabezado']['Receptor']['RUTRecep'])));
        $datos['Encabezado']['Receptor']['RznSocRecep'] = mb_substr($datos['Encabezado']['Receptor']['RznSocRecep'], 0, 100);
        if (!empty($datos['Encabezado']['Receptor']['GiroRecep'])) {
            $datos['Encabezado']['Receptor']['GiroRecep'] = mb_substr($datos['Encabezado']['Receptor']['GiroRecep'], 0, 40);
        }
        if (!empty($datos['Encabezado']['Receptor']['Contacto'])) {
            $datos['Encabezado']['Receptor']['Contacto'] = mb_substr($datos['Encabezado']['Receptor']['Contacto'], 0, 80);
        }
        if (!empty($datos['Encabezado']['Receptor']['CorreoRecep'])) {
            $datos['Encabezado']['Receptor']['CorreoRecep'] = mb_substr($datos['Encabezado']['Receptor']['CorreoRecep'], 0, 80);
        }
        if (!empty($datos['Encabezado']['Receptor']['DirRecep'])) {
            $datos['Encabezado']['Receptor']['DirRecep'] = mb_substr($datos['Encabezado']['Receptor']['DirRecep'], 0, 70);
        }
        if (!empty($datos['Encabezado']['Receptor']['CmnaRecep'])) {
            $datos['Encabezado']['Receptor']['CmnaRecep'] = mb_substr($datos['Encabezado']['Receptor']['CmnaRecep'], 0, 20);
        }
        if (!empty($datos['Encabezado']['Emisor']['Acteco'])) {
            if (strlen((string)$datos['Encabezado']['Emisor']['Acteco'])==5) {
                $datos['Encabezado']['Emisor']['Acteco'] = '0'.$datos['Encabezado']['Emisor']['Acteco'];
            }
        }
        // si existe descuento o recargo global se normalizan
        if (!empty($datos['DscRcgGlobal'])) {
            if (!isset($datos['DscRcgGlobal'][0]))
                $datos['DscRcgGlobal'] = [$datos['DscRcgGlobal']];
            $NroLinDR = 1;
            foreach ($datos['DscRcgGlobal'] as &$dr) {
                $dr = array_merge([
                    'NroLinDR' => $NroLinDR++,
                ], $dr);
            }
        }
        // si existe una o más referencias se normalizan
        if (!empty($datos['Referencia'])) {
            if (!isset($datos['Referencia'][0])) {
                $datos['Referencia'] = [$datos['Referencia']];
            }
            $NroLinRef = 1;
            foreach ($datos['Referencia'] as &$r) {
                $r = array_merge([
                    'NroLinRef' => $NroLinRef++,
                    'TpoDocRef' => false,
                    'IndGlobal' => false,
                    'FolioRef' => false,
                    'RUTOtr' => false,
                    'FchRef' => date('Y-m-d'),
                    'CodRef' => false,
                    'RazonRef' => false,
                ], $r);
            }
        }
        // verificar que exista TpoTranVenta
        if (!in_array($datos['Encabezado']['IdDoc']['TipoDTE'], [39, 41, 110, 111, 112]) and empty($datos['Encabezado']['IdDoc']['TpoTranVenta'])) {
            $datos['Encabezado']['IdDoc']['TpoTranVenta'] = 1; // ventas del giro
        }
    }
    public static function mergeRecursiveDistinct(array $array1, array $array2){
        $merged = $array1;
        foreach ( $array2 as $key => &$value ) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged [$key] = self::mergeRecursiveDistinct(
                    $merged [$key],
                    $value
                );
            } else {
                $merged [$key] = $value;
            }
        }
        return $merged;
    }
    /**
     * Método que realiza la normalización final de los datos de un documento
     * tributario electrónico. Esto se aplica todos los documentos una vez que
     * ya se aplicaron las normalizaciones por tipo
     * @param datos Arreglo con los datos del documento que se desean normalizar
     */
    private function normalizar_final(array &$datos){
        // normalizar montos de pagos programados
        if (is_array($datos['Encabezado']['IdDoc']['MntPagos'])) {
            $montos = 0;
            if (!isset($datos['Encabezado']['IdDoc']['MntPagos'][0])) {
                $datos['Encabezado']['IdDoc']['MntPagos'] = [$datos['Encabezado']['IdDoc']['MntPagos']];
            }
            foreach ($datos['Encabezado']['IdDoc']['MntPagos'] as &$MntPagos) {
                $MntPagos = array_merge([
                    'FchPago' => null,
                    'MntPago' => null,
                    'GlosaPagos' => false,
                ], $MntPagos);
                if ($MntPagos['MntPago']===null) {
                    $MntPagos['MntPago'] = $datos['Encabezado']['Totales']['MntTotal'];
                }
            }
        }
        // si existe OtraMoneda se verifican los tipos de cambio y totales
        if (!empty($datos['Encabezado']['OtraMoneda'])) {
            if (!isset($datos['Encabezado']['OtraMoneda'][0])) {
                $datos['Encabezado']['OtraMoneda'] = [$datos['Encabezado']['OtraMoneda']];
            }
            foreach ($datos['Encabezado']['OtraMoneda'] as &$OtraMoneda) {
                // colocar campos por defecto
                $OtraMoneda = array_merge([
                    'TpoMoneda' => false,
                    'TpoCambio' => false,
                    'MntNetoOtrMnda' => false,
                    'MntExeOtrMnda' => false,
                    'MntFaeCarneOtrMnda' => false,
                    'MntMargComOtrMnda' => false,
                    'IVAOtrMnda' => false,
                    'ImpRetOtrMnda' => false,
                    'IVANoRetOtrMnda' => false,
                    'MntTotOtrMnda' => false,
                ], $OtraMoneda);
                // si no hay tipo de cambio no seguir
                if (!isset($OtraMoneda['TpoCambio'])) {
                    continue;
                }
                // buscar si los valores están asignados, si no lo están asignar
                // usando el tipo de cambio que existe para la moneda
                foreach (['MntNeto', 'MntExe', 'IVA', 'IVANoRet'] as $monto) {
                    if (empty($OtraMoneda[$monto.'OtrMnda']) and !empty($datos['Encabezado']['Totales'][$monto])) {
                        $OtraMoneda[$monto.'OtrMnda'] = round($datos['Encabezado']['Totales'][$monto] * $OtraMoneda['TpoCambio'], 4);
                    }
                }
                // calcular MntFaeCarneOtrMnda, MntMargComOtrMnda, ImpRetOtrMnda
                if (empty($OtraMoneda['MntFaeCarneOtrMnda'])) {
                    $OtraMoneda['MntFaeCarneOtrMnda'] = false; // TODO
                }
                if (empty($OtraMoneda['MntMargComOtrMnda'])) {
                    $OtraMoneda['MntMargComOtrMnda'] = false; // TODO
                }
                if (empty($OtraMoneda['ImpRetOtrMnda'])) {
                    $OtraMoneda['ImpRetOtrMnda'] = false; // TODO
                }
                // calcular monto total
                if (empty($OtraMoneda['MntTotOtrMnda'])) {
                    $OtraMoneda['MntTotOtrMnda'] = 0;
                    $cols = ['MntNetoOtrMnda', 'MntExeOtrMnda', 'MntFaeCarneOtrMnda', 'MntMargComOtrMnda', 'IVAOtrMnda', 'IVANoRetOtrMnda'];
                    foreach ($cols as $monto) {
                        if (!empty($OtraMoneda[$monto])) {
                            $OtraMoneda['MntTotOtrMnda'] += $OtraMoneda[$monto];
                        }
                    }
                    // agregar total de impuesto retenido otra moneda
                    if (!empty($OtraMoneda['ImpRetOtrMnda'])) {
                        // TODO
                    }
                    // aproximar el total si es en pesos chilenos
                    if ($OtraMoneda['TpoMoneda']=='PESO CL') {
                        $OtraMoneda['MntTotOtrMnda'] = round($OtraMoneda['MntTotOtrMnda']);
                    }
                }
                // si el tipo de cambio es 0, se quita
                if ($OtraMoneda['TpoCambio']==0) {
                    $OtraMoneda['TpoCambio'] = false;
                }
            }
        }
    }
    /**
     * Método que entrega el tipo general de documento, de acuerdo a
     * $this->tipos
     * @param dte Tipo númerico de DTE, ejemplo: 33 (factura electrónica)
     * @return String con el tipo general: Documento, Liquidacion o Exportaciones

     */
    private function getTipoGeneral($dte){
        foreach ($this->tipos as $tipo => $codigos)
            if (in_array($dte, $codigos))
                return $tipo;
            \dte\firma\Log::write(
            \dte\firma\Estado::DTE_ERROR_TIPO,
            \dte\firma\Estado::get(\sasco\LibreDTE\Estado::DTE_ERROR_TIPO, $dte)
        );
        return false;
    }
    /**
     * Método que valida los datos del DTE
     * @return =true si no hay errores de validación, =false si se encontraron errores al validar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-11-04
     */
    public function verificarDatos(){
        return true;
    }
    /**
     * Método para asignar la caratula
     * @param Firma Objeto con la firma electrónica
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-14
     */
    public function setFirma(\dte\firma\FirmaElectronica $Firma)
    {
        $this->Firma = $Firma;
    }

    /**
     * Método que genera el XML para el envío del DTE al SII
     * @return XML con el envio del DTE firmado o =false si no se pudo generar o firmar el envío
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-06
     */
    public function generar()
    {
        // si ya se había generado se entrega directamente
        if ($this->xml_data)
            return $this->xml_data;
        // si no hay DTEs para generar entregar falso
        if (!isset($this->dtes[0])) {
            \dte\firma\Log::write(
                \dte\firma\Estado::ENVIODTE_FALTA_DTE,
                \dte\firma\Estado::get(\sasco\LibreDTE\Estado::ENVIODTE_FALTA_DTE)
            );
            return false;
        }
        // genear XML del envío
        $xmlEnvio = (new \dte\firma\XML())->generate([
            $this->config['tipos'][$this->tipo] => [
                '@attributes' => [
                    'xmlns' => 'http://www.sii.cl/SiiDte',
                    'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                    'xsi:schemaLocation' => 'http://www.sii.cl/SiiDte '.$this->config['schemas'][$this->tipo].'.xsd',
                    'version' => '1.0'
                ],
                'SetDTE' => [
                    '@attributes' => [
                        'ID' => 'LibreDTE_SetDoc'
                    ],
                    'Caratula' => $this->caratula,
                    'DTE' => null,
                ]
            ]
        ])->saveXML();
        // generar XML de los DTE que se deberán incorporar
        $DTEs = [];
        foreach ($this->dtes as &$DTE) {
            $DTEs[] = trim(str_replace(['<?xml version="1.0" encoding="ISO-8859-1"?>', '<?xml version="1.0"?>'], '', $DTE->saveXML()));
        }
        // firmar XML del envío y entregar
        $xml = str_replace('<DTE/>', implode("\n", $DTEs), $xmlEnvio);
        $this->xml_data = $this->Firma ? $this->Firma->signXML($xml, '#LibreDTE_SetDoc', 'SetDTE', true) : $xml;
        return $this->xml_data;
    }

}
?>