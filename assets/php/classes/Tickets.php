<?
class Tickets{
    private $con;

    public function __construct() {
        include($_SERVER["DOCUMENT_ROOT"] . "/2cnytm029mp3r/cm293uc5904uh.php");
        $this->con = $con;
    }

    public function obtenerTicket($post){
        try{
            $idticket = mysqli_real_escape_string($this->con,$post["idticket"]);

            $query = "
            select
                a.folio,
                a.idcuenta,
                b.idtienda,
                b.nombre as sucursal
            from
                ttickets a
            left join
                tsucursales b
            on
                b.idsucursal = a.idsucursal
            where
                a.idticket = '".$idticket."'";
            $result = mysqli_query($this->con,$query);

            if(mysqli_num_rows($result)==0){
                throw new Exception("No se pudo recuperar la información del ticket");
            }

            $respuesta = array(
                "respuesta" => "OK",
                "ticket" => mysqli_fetch_assoc($result)
            );
            
        }catch(Exception $e){
            $respuesta = array(
                "respuesta"=>"ERROR",
                "mensaje"=>"ERROR: ".$e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    public function facturarTicket($post){
        try{
            $idticket = mysqli_real_escape_string($this->con,$post["idticket"]);
            $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
            $razonsocial = mysqli_real_escape_string($this->con,$post["txtRazonSocial"]);
            $rfc = mysqli_real_escape_string($this->con,$post["txtRFC"]);
            $codigo_postal = mysqli_real_escape_string($this->con,$post["txtCodigoPostal"]);
            $idregimenfiscal = mysqli_real_escape_string($this->con,$post["slcRegimenFiscal"]);
            $idusocfdi = mysqli_real_escape_string($this->con,$post["slcUsoCFDI"]);
            $idemisor = mysqli_real_escape_string($this->con,$post["slcEmisor"]);
            $comentarios = mysqli_real_escape_string($this->con,$post["txtComentarios"]);
            $idmetodopago = mysqli_real_escape_string($this->con,$post["slcMetodoPago"]);
            $idformapago = ($idmetodopago==1) ? 21 : mysqli_real_escape_string($this->con,$post["slcFormaPago"]);
            $correo = mysqli_real_escape_string($this->con,$post["txtCorreo"]);

            $query = "
            select
                usocfdi
            from
                sat_tcatusoscfdi
            where
                idusocfdi = '".$idusocfdi."'";
            $usocfdi = mysqli_fetch_assoc(mysqli_query($this->con,$query))["usocfdi"];

            $query = "
            select
                regimenfiscal
            from
                sat_tcatregimenfiscal
            where
                idregimenfiscal = '".$idregimenfiscal."'";
            $regimenfiscal = mysqli_fetch_assoc(mysqli_query($this->con,$query))["regimenfiscal"];

            $tmp["rfc"] = $rfc;
            $tmp["razon_social"] = $razonsocial;
            $tmp["usocfdi"] = $usocfdi;
            $tmp["regimenfiscal"] = $regimenfiscal;
            $tmp["codigo_postal"] = $codigo_postal;

            $receptor = array(
                "Rfc" => $tmp["rfc"],
                "Nombre" => $tmp["razon_social"],
                "UsoCFDI" => $tmp["usocfdi"],
                "DomicilioFiscalReceptor" => $tmp["codigo_postal"],
                "RegimenFiscalReceptor" => $tmp["regimenfiscal"]
            );

            // Obtener serie y folio
            $query = "
            select
                a.rfc,
                a.razon_social,
                a.codigo_postal,
                b.regimenfiscal,
                a.serie,
                a.folio
            from
                temisores a
            left join
                sat_tcatregimenfiscal b
            on
                b.idregimenfiscal = a.idregimenfiscal
            where
                a.idemisor = '".$idemisor."'";
            $tmp = mysqli_fetch_assoc(mysqli_query($this->con,$query));
            $serie = $tmp["serie"];
            $folio = $tmp["folio"];

            $emisor = array(
                "Rfc" => $tmp["rfc"],
                "Nombre" => $tmp["razon_social"],
                "RegimenFiscal" => $tmp["regimenfiscal"],
                "LugarExpedicion" => $tmp["codigo_postal"]
            );

            // Obtener numero de certificado, certificado y archivo keypem
            $ruta_server = $_SERVER["DOCUMENT_ROOT"] . "/../1.uniformescisne.mx";
            $ruta = $ruta_server . "/emisores/" . str_replace("&", "_", $tmp['rfc']);
            $numero_certificado = $this->obtenerNumeroCertificado($ruta."/sat/"."certificado.cer");
            $certificado = $this->obtenerContenidoCertificado($ruta."/sat/"."certificado.cer");
            $archivo_keypem = file_get_contents($ruta."/sat/"."llave.key.pem");

            $ticket = $this->obtenerTicket(array("idticket" => $idticket))["ticket"];

            // Conceptos
            $query = "
            select
                cantidad,
                producto,
                subtotal as precio,
                cve_unidadmedida,
                cve_productoservicio
            from
                vrcuentaproductos
            where
                idcuenta = '".$ticket["idcuenta"]."'";
            $result = mysqli_query($this->con,$query);

            $subtotal = 0;
            $conceptos_factura = array();
            while($tmp = mysqli_fetch_assoc($result)){
                $cadena_utf8 = mb_convert_encoding($tmp["producto"], 'UTF-8', 'auto');

                $cadena_sin_acentos = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $cadena_utf8);
                
                $valor_unitario = $tmp["precio"];

                $conceptos_factura[] = [
                    "Cantidad" => $tmp["cantidad"],
                    "Descripcion" => $cadena_sin_acentos,
                    "ValorUnitario" => sprintf("%.6f", $valor_unitario),
                    "Importe" =>  sprintf("%.6f", $valor_unitario*$tmp["cantidad"]),
                    "ClaveUnidad" => $tmp["cve_unidadmedida"],
                    "ClaveProdServ" => $tmp["cve_productoservicio"],
                    "ObjetoImp" => '02'
                ];

                $subtotal += $valor_unitario*$tmp["cantidad"];
            }

            //Metodo y forma de pago
            $query = "
            select
                metodopago
            from
                sat_tcatmetodospago
            where
                idmetodopago = '".$idmetodopago."'";
            $metodopago = mysqli_fetch_assoc(mysqli_query($this->con,$query))["metodopago"];

            $query = "
            select
                formapago
            from
                sat_tcatformaspago
            where
                idformapago = '".$idformapago."'";
            $formapago = mysqli_fetch_assoc(mysqli_query($this->con,$query))["formapago"];

            //Declaramos el logo en base64, en caso de que no exista uno para el emisor entonces tomamos el logo de la app
            $logo = $ruta_server."/imagenes/tiendas/".$ticket["idtienda"]."_logo.png";
            $logo = "data:image/png;base64,".((file_exists($logo)) ? base64_encode(file_get_contents($logo)) : base64_encode(file_get_contents($ruta_server."/assets/images/logo-uniformes-trazo.png")));

            $datos = array(
                "api_key" => "tek_npzimyh2ajjxpj3p3j2ofozt7c6deej9uu",
                "Version" => "4.0",
                "pruebas" => 1,
                "numero_certificado" => $numero_certificado,
                "certificado" => $certificado,
                "keypem" => $archivo_keypem,
                "colortxt" => "000000",
                "logo" => $logo,
                "tipoComprobante" => "I",
                "serie" => $serie,
                "folio" => $folio,
                "emisor" => $emisor,
                "receptor" => $receptor,
                "conceptos" => $conceptos_factura,
                "subtotal" => $subtotal,
                "iva_trasladado" => 8,
                "metodopago" => $metodopago,
                "formapago" => $formapago,
                "moneda" => "MXN",
                "tipo_cambio" => "1",
                "carta_porte" => false,
                "comentarios" => $comentarios
            );

            $url = "https://api.xptk.app/timbrador/index.php";

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => http_build_query($datos),
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => array(
                    'Authorization: CH60NP5HQZYUPZEQ'
                ),
            ));

            $response = curl_exec($curl);
            $response = json_decode($response, true);
            curl_close($curl);

            file_put_contents($ruta_server."/txts/facturarPedido.txt",print_r($datos,true)."\n\n".print_r($response,true));

            if ($response["response"] == true) {
                $query = "
                insert
                into
                    tfacturas
                (
                    idusuario,
                    idemisor,
                    idmetodopago,
                    idformapago,
                    serie,
                    folio,
                    subtotal,
                    iva,
                    total,
                    saldo,
                    uuid,
                    timbrado
                ) values (
                    '".$idusuario."',
                    '".$idemisor."',
                    '".$idmetodopago."',
                    '".$idformapago."',
                    '".$serie."',
                    '".$folio."',
                    '".$response["subtotal"]."',
                    '".($response["total"]-$response["subtotal"])."',
                    '".$response["total"]."',
                    '".$response["total"]."',
                    '".$response["uuid"]."',
                    '".$response["fechaTimbrado"]."'
                )";
                mysqli_query($this->con,$query);

                $idfactura = mysqli_insert_id($this->con);

                file_put_contents($ruta."/facturas/".$response["uuid"].".xml",base64_decode($response["xml"]));
                file_put_contents($ruta."/facturas/".$response["uuid"].".pdf",base64_decode($response["pdf"]));

                $query = "
                update
                    temisores
                set
                    folio = folio + 1
                where
                    idemisor = '".$idemisor."'";
                mysqli_query($this->con,$query);

                $query = "
                update
                    ttickets
                set
                    idfactura = '".$idfactura."'
                where
                    idticket = '".$idticket."'";
                mysqli_query($this->con,$query);

                //Se envia la factura por correo
                $folio = $serie."-".$folio;
                $fecha = date("Y-m-d");
                $total = $response["total"];

                include($ruta_server."/assets/plantillas/correo/envioFactura.php");
                include($ruta_server."/assets/plantillas/correo/base.php");

                $claseCorreos = new Correos();

                $correos = array_filter(array_map('trim', explode(',', $correo)));

                $respuesta = $claseCorreos->enviarCorreo(array(
                    "idtienda" => $ticket["idtienda"],
                    "asunto" => "Envío de factura",
                    "mensaje" => $cuerpo,
                    "correos" => array_values($correos),
                    "adjuntos" => array(
                        array(
                            "nombre" => $response["uuid"].".xml",
                            "archivo" => $response["xml"]
                        ),
                        array(
                            "nombre" => $response["uuid"].".pdf",
                            "archivo" => $response["pdf"]
                        )
                    )
                ));

                $respuesta = array(
                    "respuesta" => "OK",
                    "tipo" => "mensajecargar",
                    "titulo" => "Factura generada",
                    "mensaje" => "Se ha generado la factura correctamente".(($respuesta["result"]=="success") ? " y se ha enviado por correo" : " pero no se pudo enviar por correo (".$respuesta["mensaje"].")"),
                    "formulario" => "formBusqueda"
                );
            }else{
                throw new Exception($response["mensaje"]);
            }

        }catch(Exception $e){
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje" => "Código 111 ".$e->getMessage()
            );
        }catch(Throwable $e){
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje" => "Código 111 ".$e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    /**
     * getNumCer
     * Obtener el numero de certificado de un archivo .cer
     * @param  string Path del archivo .cer
     * @return string Numero de certificado
     */
    public function obtenerNumeroCertificado($certificado)
    {
        $numero = FALSE;
        //si funciona retorna un array como: Array ( [0] => "serial=323030303130303030303032303030303032393"
        //local
        //exec(".\openssl\openssl.exe x509 -inform DER -in $certificado -serial 2>&1", $datacer);
        //web
        exec("openssl x509 -inform DER -in $certificado -serial", $datacer);
        //Reemplazamos el texto que no nos interesa(str_replace) y convertimos el string a array(str_split)
        $serialnumbers = str_split(str_replace("serial=", "", $datacer[0]));
        //Para despues obtener los numeros en posiciones impares
        for ($i = 0; $i < count($serialnumbers); $i++) {
            if ($i % 2 != 0) {
                $numero .= $serialnumbers[$i];
            }
        }
        return $numero;
    }

    /**
     * getCer
     * Obtener el contenido del certificado
     * @param  string $pathcer Path de certificado
     * @return cadena          Retorna el contenido del certificado
     */
    public function obtenerContenidoCertificado($certificado)
    {
        //locla
        //exec(".\openssl\openssl.exe x509 -inform DER -in $certificado",$cer); //Local
        //web
        exec("openssl x509 -inform DER -in $certificado", $cer);  //VPS
        array_pop($cer);                                                    //elimino el ultimo elemento
        array_shift($cer);                                                  //y el primero
        $contenido = implode($cer);                                         //despues convierto a string
        return $contenido;
    }

}