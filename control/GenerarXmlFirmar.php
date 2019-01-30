<?php
if (is_readable('config.php')){
    include 'config.php';
}else{
    die('Debes crear config.php' . "\n");
}
header('Content-type: text/plain; charset=ISO-8859-1');
include(dirname(__FILE__)."/../dte/XML.php");
include(dirname(__FILE__)."/../dte/Log.php");
include(dirname(__FILE__)."/../dte/FirmaElectronica.php");
include(dirname(__FILE__)."/../dte/GenerarFirma.php");

$factura = ['Encabezado' => [
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

    $firma = new \dte\firma\FirmaElectronica($config['firma']);
    /*$xml = new \dte\firma\XML();
    $xml->generate($factura)->saveXML();
    $reporte = $xml->generate($factura)->saveXML();*/

    $GenerarFirma = new \dte\firma\Sii\GenerarFirma($factura);
    var_dump($GenerarFirma);exit;

    //file_put_contents('reporte_xml/facturaFirma.xml', $EnvioDTE->generar());
    // si hubo algún error se muestra
    foreach (\dte\firma\Log::readAll() as $log)
        echo $log,"\n";

?>