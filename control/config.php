<?php
/**
 *@package pXP
 *@file config.php
 *@author  (miguel.mamani)
 *@date 23-01-2019 13:35:04
 *@description Clase que recibe los parametros enviados por la vista para mandar a la capa de Modelo
 */
$config = [
    'firma' => [
        'file' => dirname(__FILE__).'/../firma_digital/local.p12',
        'pass' => 'hyomin',
    ],
];
?>