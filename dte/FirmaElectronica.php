<?php
/**
 *@package pXP
 *@file FirmaElectronica.php
 *@author  (miguel.mamani)
 *@date 24/01/2019
 *@description Clase que recibe los parametros enviados por la vista para mandar a la capa de Modelo
 */
namespace dte\firma;
class FirmaElectronica{
    private $config; ///< Configuración de la firma electrónica
    private $certs; ///< Certificados digitales de la firma
    private $data; ///< Datos del certificado digial

    public function __construct(array $config = []){
        if (!$config) {
            return $this->error('No fue posible leer los datos de la firma electrónica');
        }else{
            $this->config = array_merge([
                'file' => null,
                'pass' => null,
                'data' => null,
                'wordwrap' => 64,
            ], $config);
        }
        // cargar firma electrónica desde el contenido del archivo .p12 si no
        // se pasaron como datos del arreglo de configuración
        if (!$this->config['data'] and $this->config['file']) {

            if (is_readable($this->config['file'])) {
                $this->config['data'] = file_get_contents($this->config['file']);
            } else {
                return $this->error('Archivo de la firma electrónica '.basename($this->config['file']).' no puede ser leído');
            }
        }
        // leer datos de la firma electrónica
        if ($this->config['data'] and openssl_pkcs12_read($this->config['data'], $this->certs, $this->config['pass'])===false) {
            return $this->error('No fue posible leer los datos de la firma electrónica (verificar la contraseña)');
        }
        $this->data = openssl_x509_parse($this->certs['cert']);
        // quitar datos del contenido del archivo de la firma
        unset($this->config['data']);
    }
    /**
     * Método para generar un error usando una excepción de SowerPHP o terminar
     * el script si no se está usando el framework
     * @param msg Mensaje del error
     */
    private function error($msg){
        if (class_exists('\sasco\LibreDTE\Estado') and class_exists('\sasco\LibreDTE\Log')) {
            $msg = \dte\firma\Estado::get(\dte\firma\Estado::FIRMA_ERROR, $msg);
            \dte\firma\Log::write(\dte\firma\Estado::FIRMA_ERROR, $msg);
            return false;
        } else {
            throw new \Exception($msg);
        }
    }
    /**
     * Método que agrega el inicio y fin de un certificado (clave pública)
     * @param cert Certificado que se desea normalizar
     * @return Certificado con el inicio y fin correspondiente
     */
    private function normalizeCert($cert){
        if (strpos($cert, '-----BEGIN CERTIFICATE-----')===false) {
            $body = trim($cert);
            $cert = '-----BEGIN CERTIFICATE-----'."\n";
            $cert .= wordwrap($body, $this->config['wordwrap'], "\n", true)."\n";
            $cert .= '-----END CERTIFICATE-----'."\n";
        }
        return $cert;
    }
    /**
     * Método que entrega el RUN/RUT asociado al certificado
     * @return RUN/RUT asociado al certificado en formato: 11222333-4
     */
    public function getID(){
        if (isset($this->data['serialNumber'])) {
            return ltrim($this->data['serialNumber'], '0');
        }
        // no se encontró el RUN
        return $this->error('No fue posible obtener el ID de la firma');
    }
    /**
     * Método que entrega el CN del subject
     * @return CN del subject
     */
    public function getName(){
        if (isset($this->data['subject']['CN']))
            return $this->data['subject']['CN'];
        return $this->error('No fue posible obtener el Name (subject.CN) de la firma');
    }
    /**
     * Método que entrega el emailAddress del subject
     * @return emailAddress del subject
     */
    public function getEmail(){
        if (isset($this->data['subject']['emailAddress']))
            return $this->data['subject']['emailAddress'];
        return $this->error('No fue posible obtener el Email (subject.emailAddress) de la firma');
    }
    /**
     * Método que entrega desde cuando es válida la firma
     * @return validFrom_time_t
     */
    public function getFrom(){
        return date('Y-m-d H:i:s', $this->data['validFrom_time_t']);
    }
    /**
     * Método que entrega hasta cuando es válida la firma
     * @return validTo_time_t
     */
    public function getTo(){
        return date('Y-m-d H:i:s', $this->data['validTo_time_t']);
    }
    /**
     * Método que entrega el nombre del emisor de la firma
     * @return CN del issuer
     */
    public function getIssuer(){
        return $this->data['issuer']['CN'];
    }
    /**
     * Método que entrega los datos del certificado
     * @return Arreglo con todo los datos del certificado
     */
    public function getData(){
        return $this->data;
    }
    /**
     * Método que obtiene el módulo de la clave privada
     * @return Módulo en base64
     */
    public function getModulus(){
        $details = openssl_pkey_get_details(openssl_pkey_get_private($this->certs['pkey']));
        return wordwrap(base64_encode($details['rsa']['n']), $this->config['wordwrap'], "\n", true);
    }
    /**
     * Método que obtiene el exponente público de la clave privada
     * @return Exponente público en base64
     */
    public function getExponent(){
        $details = openssl_pkey_get_details(openssl_pkey_get_private($this->certs['pkey']));
        return wordwrap(base64_encode($details['rsa']['e']), $this->config['wordwrap'], "\n", true);
    }
    /**
     * Método que entrega el certificado de la firma
     * @return Contenido del certificado, clave pública del certificado digital, en base64
     */
    public function getCertificate($clean = false){
        if ($clean) {
            return trim(str_replace(
                ['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----'],
                '',
                $this->certs['cert']
            ));
        } else {
            return $this->certs['cert'];
        }
    }
    /**
     * Método que entrega la clave privada de la firma
     * @return Contenido de la clave privada del certificado digital en base64
     */
    public function getPrivateKey($clean = false){
        if ($clean) {
            return trim(str_replace(
                ['-----BEGIN PRIVATE KEY-----', '-----END PRIVATE KEY-----'],
                '',
                $this->certs['pkey']
            ));
        } else {
            return $this->certs['pkey'];
        }
    }
    /**
     * Método para realizar la firma de datos
     * @param data Datos que se desean firmar
     * @param signature_alg Algoritmo que se utilizará para firmar (por defect SHA1)
     * @return Firma digital de los datos en base64 o =false si no se pudo firmar
     */
    public function sign($data, $signature_alg = OPENSSL_ALGO_SHA1){
        $signature = null;
        if (openssl_sign($data, $signature, $this->certs['pkey'], $signature_alg)==false) {
            return $this->error('No fue posible firmar los datos');
        }
        return base64_encode($signature);
    }
    /**
     * Método que verifica la firma digital de datos
     * @param data Datos que se desean verificar
     * @param signature Firma digital de los datos en base64
     * @param pub_key Certificado digital, clave pública, de la firma
     * @param signature_alg Algoritmo que se usó para firmar (por defect SHA1)
     * @return =true si la firma está ok, =false si está mal o no se pudo determinar
     */
    public function verify($data, $signature, $pub_key = null, $signature_alg = OPENSSL_ALGO_SHA1){
        if ($pub_key === null)
            $pub_key = $this->certs['cert'];
        $pub_key = $this->normalizeCert($pub_key);
        return openssl_verify($data, base64_decode($signature), $pub_key, $signature_alg) == 1 ? true : false;
    }

    /**
     * Método que firma un XML utilizando RSA y SHA1
     * Referencia: http://www.di-mgt.com.au/xmldsig2.html
     * @param xml Datos XML que se desean firmar
     * @param reference Referencia a la que hace la firma
     * @return XML firmado o =false si no se pudo fimar
     */
    public function signXML($xml, $reference = '', $tag = null, $xmlns_xsi = false){
        // normalizar 4to parámetro que puede ser boolean o array
        if (is_array($xmlns_xsi)) {
            $namespace = $xmlns_xsi;
            $xmlns_xsi = false;
        } else {
            $namespace = null;
        }
        // obtener objeto del XML que se va a firmar
        $doc = new XML();
        $doc->loadXML($xml);
        if (!$doc->documentElement) {
            return $this->error('No se pudo obtener el documentElement desde el XML a firmar (posible XML mal formado)');
        }
        // crear nodo para la firma
        $Signature = $doc->importNode((new XML())->generate([
            'Signature' => [
                '@attributes' => $namespace ? false : [
                    'xmlns' => 'http://www.w3.org/2000/09/xmldsig#',
                ],
                'SignedInfo' => [
                    '@attributes' => $namespace ? false : [
                        'xmlns' => 'http://www.w3.org/2000/09/xmldsig#',
                        'xmlns:xsi' => $xmlns_xsi ? 'http://www.w3.org/2001/XMLSchema-instance' : false,
                    ],
                    'CanonicalizationMethod' => [
                        '@attributes' => [
                            'Algorithm' => 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315',
                        ],
                    ],
                    'SignatureMethod' => [
                        '@attributes' => [
                            'Algorithm' => 'http://www.w3.org/2000/09/xmldsig#rsa-sha1',
                        ],
                    ],
                    'Reference' => [
                        '@attributes' => [
                            'URI' => $reference,
                        ],
                        'Transforms' => [
                            'Transform' => [
                                '@attributes' => [
                                    'Algorithm' => $namespace ? 'http://www.altova.com' : 'http://www.w3.org/2000/09/xmldsig#enveloped-signature',
                                ],
                            ],
                        ],
                        'DigestMethod' => [
                            '@attributes' => [
                                'Algorithm' => 'http://www.w3.org/2000/09/xmldsig#sha1',
                            ],
                        ],
                        'DigestValue' => null,
                    ],
                ],
                'SignatureValue' => null,
                'KeyInfo' => [
                    'KeyValue' => [
                        'RSAKeyValue' => [
                            'Modulus' => null,
                            'Exponent' => null,
                        ],
                    ],
                    'X509Data' => [
                        'X509Certificate' => null,
                    ],
                ],
            ],
        ], $namespace)->documentElement, true);
        // calcular DigestValue
        if ($tag) {
            $item = $doc->documentElement->getElementsByTagName($tag)->item(0);
            if (!$item) {
                return $this->error('No fue posible obtener el nodo con el tag '.$tag);
            }
            $digest = base64_encode(sha1($item->C14N(), true));
        } else {
            $digest = base64_encode(sha1($doc->C14N(), true));
        }
        $Signature->getElementsByTagName('DigestValue')->item(0)->nodeValue = $digest;
        // calcular SignatureValue
        $SignedInfo = $doc->saveHTML($Signature->getElementsByTagName('SignedInfo')->item(0));
        $firma = $this->sign($SignedInfo);
        if (!$firma)
            return false;
        $signature = wordwrap($firma, $this->config['wordwrap'], "\n", true);
        // reemplazar valores en la firma de
        $Signature->getElementsByTagName('SignatureValue')->item(0)->nodeValue = $signature;
        $Signature->getElementsByTagName('Modulus')->item(0)->nodeValue = $this->getModulus();
        $Signature->getElementsByTagName('Exponent')->item(0)->nodeValue = $this->getExponent();
        $Signature->getElementsByTagName('X509Certificate')->item(0)->nodeValue = $this->getCertificate(true);
        // agregar y entregar firma
        $doc->documentElement->appendChild($Signature);
        return $doc->saveXML();
    }
    /**
     * Método que verifica la validez de la firma de un XML utilizando RSA y SHA1
     * @param xml_data Archivo XML que se desea validar
     * @return =true si la firma del documento XML es válida o = false si no lo es
     */
    public function verifyXML($xml_data, $tag = null){
        $doc = new XML();
        $doc->loadXML($xml_data);
        // preparar datos que se verificarán
        $SignaturesElements = $doc->documentElement->getElementsByTagName('Signature');
        $Signature = $doc->documentElement->removeChild($SignaturesElements->item($SignaturesElements->length-1));
        $SignedInfo = $Signature->getElementsByTagName('SignedInfo')->item(0);
        $SignedInfo->setAttribute('xmlns', $Signature->getAttribute('xmlns'));
        $signed_info = $doc->saveHTML($SignedInfo);
        $signature = $Signature->getElementsByTagName('SignatureValue')->item(0)->nodeValue;
        $pub_key = $Signature->getElementsByTagName('X509Certificate')->item(0)->nodeValue;
        // verificar firma
        if (!$this->verify($signed_info, $signature, $pub_key))
            return false;
        // verificar digest
        $digest_original = $Signature->getElementsByTagName('DigestValue')->item(0)->nodeValue;
        if ($tag) {
            $digest_calculado = base64_encode(sha1($doc->documentElement->getElementsByTagName($tag)->item(0)->C14N(), true));
        } else {
            $digest_calculado = base64_encode(sha1($doc->C14N(), true));
        }
        return $digest_original == $digest_calculado;
    }
}
?>