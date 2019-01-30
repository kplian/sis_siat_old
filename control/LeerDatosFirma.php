<?php
// respuesta en texto plano
header('Content-type: text/plain');
// activar todos los errores
ini_set('display_errors', true);
error_reporting(E_ALL);

if (is_readable('config.php')){
    include 'config.php';
}else{
    die('Debes crear config.php' . "\n");
}
include(dirname(__FILE__)."/../dte/FirmaElectronica.php");
include(dirname(__FILE__)."/../dte/Log.php");

    $Firma = new \dte\firma\FirmaElectronica($config['firma']);
    // mostrar datos de la persona dueña de la firma
    echo 'RUN    : ',$Firma->getID(),"\n";
    echo 'Nombre : ',$Firma->getName(),"\n";
    echo 'Email  : ',$Firma->getEmail(),"\n";
    echo 'Desde  : ',$Firma->getFrom(),"\n";
    echo 'Hasta  : ',$Firma->getTo(),"\n";
    echo 'Emisor : ',$Firma->getIssuer(),"\n\n\n";

    print_r($Firma->getData());
    // si hubo errores mostrar
    foreach (\dte\firma\Log::readAll() as $error){
        echo $error,"\n";
    }

?>