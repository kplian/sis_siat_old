<?php
// respuesta en texto plano
header('Content-type: text/plain; charset=ISO-8859-1');
// incluir archivos php de la biblioteca y configuraciones
include(dirname(__FILE__)."/../dte/XML.php");
include(dirname(__FILE__)."/../dte/Log.php");
include(dirname(__FILE__)."/../../lib/lib_general/Mensaje.php");

$factura = [
    'Encabezado' => [
        'IdDoc' => [
            'TipoDTE' => 33,
            'Folio' => 1,
        ],
        'Emisor' => [
            'RUTEmisor' => '1234',
            'RznSoc' => 'Kplian',
            'GiroEmis' => 'Servicios integrales de informática',
            'DirOrigen' => 'Cochabamba',
            'CmnaOrigen' => 'La Paz',
        ],
        'Receptor' => [
            'RUTRecep' => '99999-K',
            'RznSocRecep' => 'Impuestoa Nacionales',
            'GiroRecep' => 'Gobierno',
            'DirRecep' => 'Simon Lopez',
            'CmnaRecep' => 'Cochabamba',
        ],
    ],
    'Detalle' => [
        [
            'Nombre' => 'Impresoras',
            'Cantidad' => 1,
            'Precio' => 100,
        ],
        [
            'Nombre' => 'Monitor',
            'Cantidad' => 1,
            'Precio' => 100,
        ],
    ],
];

    $xml = new \dte\firma\XML();
    $xml->generate($factura)->saveXML();
    $reporte = $xml->generate($factura)->saveXML();
    echo $reporte;
    // si hubo errores mostrar
    foreach (\dte\firma\Log::readAll() as $error)
        echo $error,"\n";

    /*$titulo = 'facturas';
    $nombreArchivo = uniqid(md5(session_id()) . $titulo);
    $nombreArchivo .= '.xml';
    file_put_contents('reporte_xml/'.$nombreArchivo,$reporte);
    foreach (\dte\firma\Log::readAll() as $error){
        echo $error,"\n";
    }
    if(is_readable('reporte_xml/'.$nombreArchivo)){
        die('Se genero el reporte' . "\n");
    }else{
        die('No se genero el reporte' . "\n");
    }*/

?>