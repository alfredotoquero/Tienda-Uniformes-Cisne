<?
class Pagos{

    private $con;

    function __construct(){
        include_once($_SERVER["DOCUMENT_ROOT"]."/2cnytm029mp3r/cm293uc5904uh.php");
        $this->con = $con;
    }

    /**
     * Obtiene los pagos registrados en un determinado periodo de tiempo
     * 
     * @param array $post       Contiene las fechas entre las cuales se buscarán los pagos
     * @return array            Devuelve los pagos en caso de que se hayan encontrado algunos, en caso contrario devuelve un mensaje de error.
     */
    public function getPagos($post){
        try{
            $fecha_i = mysqli_real_escape_string($this->con,$post["txtFechaInicial"]);
            $fecha_f = mysqli_real_escape_string($this->con,$post["txtFechaFinal"]);

            $query = "
            select
                a.idpago,
                a.idcliente,
                b.nombre as cliente,
                a.total,
                a.idformapago,
                c.nombre as formapago,
                a.fecha,
                a.uuid
            from
                tpagos a
            left join
                tclientes b
            on
                a.idcliente = b.idcliente
            left join
                tcatformaspago c
            on
                a.idformapago = c.idformapago
            where
                a.fecha between '".$fecha_i."' and '".$fecha_f."'
            order by
                a.idpago desc";
            $result = mysqli_query($this->con,$query);

            if(mysqli_num_rows($result)==0){
                throw new Exception("No se encontraron resultados");
            }

            $respuesta = array(
                "result" => "success",
                "pagos" => mysqli_fetch_all($result,MYSQLI_ASSOC)
            );
        }catch(Exception $e){
            $respuesta = array(
                "result" => "error",
                "mensaje" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    public function getPedidosCliente($post){
        try{
            $idvendedor = mysqli_real_escape_string($this->con,$post["idvendedor"]);
            $cliente = mysqli_real_escape_string($this->con, substr($post["cliente"], strpos($post["cliente"], "-") + 1));

            $query = "
            select
                idsucursal
            from
                tvendedores
            where
                idvendedor = '".$idvendedor."'";
            $idsucursal = mysqli_fetch_assoc(mysqli_query($this->con,$query))["idsucursal"];

            if(empty($idsucursal)){
                throw new Exception("No se pudo recuperar la sucursal del vendedor");
            }

            $query = "
            select
                *
            from
                vpedidos
            where
                idsucursal = '".$idsucursal."' and
                total > 0 and
                statuspago = 0 and
                status = 'A' and
                cliente = '".$cliente."'
            order by
                idpedido";
            $result = mysqli_query($this->con,$query);

            if(mysqli_num_rows($result)==0){
                throw new Exception("No se encontraron resultados");
            }

            $respuesta = array(
                "result" => "success",
                "pedidos" => mysqli_fetch_all($result,MYSQLI_ASSOC)
            );
        }catch(Exception $e){
            $respuesta = array(
                "result" => "error",
                "mensaje" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    public function getClientesPedidos($post){
        try{
            $idvendedor = mysqli_real_escape_string($this->con,$post["idvendedor"]);

            $query = "
            select
                idsucursal
            from
                tvendedores
            where
                idvendedor = '".$idvendedor."'";
            $idsucursal = mysqli_fetch_assoc(mysqli_query($this->con,$query))["idsucursal"];

            if(empty($idsucursal)){
                throw new Exception("No se pudo recuperar la sucursal del vendedor");
            }

            $query = "
            select
                a.idcliente,
                a.cliente
            from
                vpedidos a
            where
                a.idsucursal = '".$idsucursal."' and
                a.total > 0 and
                a.statuspago = 0 and
                a.status = 'A'
            group by
                a.idcliente,
                a.cliente
            order by
                a.cliente";
            $result = mysqli_query($this->con,$query);

            if(mysqli_num_rows($result)==0){
                throw new Exception("No se encontraron resultados");
            }

            $respuesta = array(
                "result" => "success",
                "clientes" => mysqli_fetch_all($result,MYSQLI_ASSOC)
            );
        }catch(Exception $e){
            $respuesta = array(
                "result" => "error",
                "mensaje" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    public function getFormasPago(){
        try{
            $query = "
            select
                *
            from
                tcatformaspago";
            $result = mysqli_query($this->con,$query);

            if(mysqli_num_rows($result)==0){
                throw new Exception("No se encontraron resultados");
            }

            $respuesta = array(
                "result" => "success",
                "formaspago" => mysqli_fetch_all($result,MYSQLI_ASSOC)
            );
        }catch(Exception $e){
            $respuesta = array(
                "result" => "error",
                "mensaje" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    /**
     * Registra un pago para uno o varios pedidos de un cliente.
     * Genera tickets, actualiza abonos y estatus de pago en los pedidos.
     * Si los pedidos son facturados (complemento = 1), invoca la generación del complemento de pago.
     *
     * @param array $post Contiene los datos del pago:
     *  - string "cliente"      ID y nombre del cliente en formato "ID-NOMBRE"
     *  - string "idformapago"  ID de la forma de pago
     *  - string "fecha"        Fecha del pago (formato yyyy-mm-dd)
     *  - int    "complemento"  1 si los pedidos son facturados, 0 si no
     *  - array  "pedidos"      Arreglo de pedidos, cada uno con:
     *      - string "idpedido"   ID del pedido
     *      - string "idfactura"  ID de la factura (0 si no tiene)
     *      - float  "monto"      Monto del pago para el pedido
     * @return array Respuesta con:
     *  - bool   "success" Indica si el pago se registró correctamente
     *  - string "message" Mensaje descriptivo del resultado
     *  - array  "tickets" Arreglo de tickets generados (idticket y copias)
     */
    public function agregarPago($post){
        try{
            $idcliente = mysqli_real_escape_string($this->con, strstr($post["cliente"], "-", true));
            $idformapago = mysqli_real_escape_string($this->con,$post["idformapago"]);
            $fecha = mysqli_real_escape_string($this->con,$post["fecha"]);
            $complemento = mysqli_real_escape_string($this->con,$post["complemento"]);

            $pedidos = [];
            if(isset($post["pedidos"]) && is_array($post["pedidos"])){
                foreach($post["pedidos"] as $pedido){
                    $monto = floatval($pedido["monto"]);
                    if($monto > 0){
                        $pedidos[] = array(
                            "idpedido" => mysqli_real_escape_string($this->con,$pedido["idpedido"]),
                            "idfactura" => mysqli_real_escape_string($this->con,$pedido["idfactura"]),
                            "monto" => $monto
                        );
                    }
                }
            }

            if(empty($pedidos)){
                throw new Exception("No se recibieron pedidos con monto mayor a 0");
            }

            // // Obtener datos del vendedor
            // $idvendedor = $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"];
            // $query = "
            // select
            //     *
            // from
            //     tvendedores
            // where
            //     idvendedor = '".$idvendedor."'";
            // $vendedor = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            // if(empty($vendedor)){
            //     throw new Exception("No se pudo recuperar la información del vendedor");
            // }

            // // Obtener corte activo
            // $query = "
            // select
            //     *
            // from
            //     tcortessucursales
            // where
            //     idsucursal = '".$vendedor["idsucursal"]."' and
            //     status = 'A'";
            // $corte = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            // if(empty($corte)){
            //     throw new Exception("No hay un corte activo para la sucursal");
            // }

            // // Iniciar transacción
            // mysqli_begin_transaction($this->con);

            // // Procesar cada pedido
            // $tickets = [];
            // foreach($pedidos as $pedido){
            //     $idpedido = $pedido["idpedido"];
            //     $monto = $pedido["monto"];

            //     // Obtener folio actual
            //     $query = "
            //     select
            //         *
            //     from
            //         tsucursales
            //     where
            //         idsucursal = '".$vendedor["idsucursal"]."'";
            //     $folio = mysqli_fetch_assoc(mysqli_query($this->con,$query))["folio"];

            //     // Insertar ticket
            //     $query = "
            //     insert
            //     into
            //         ttickets
            //     (
            //         idpedido,
            //         idsucursal,
            //         idcorte,
            //         idvendedor,
            //         folio,
            //         total,
            //         fecha,
            //         status,
            //         notas
            //     ) values (
            //         '".$idpedido."',
            //         '".$vendedor["idsucursal"]."',
            //         '".$corte["idcorte"]."',
            //         '".$vendedor["idvendedor"]."',
            //         '".$folio."',
            //         '".$monto."',
            //         '".$fecha."',
            //         'A',
            //         ''
            //     )";
            //     if(!mysqli_query($this->con,$query)){
            //         throw new Exception("Error al insertar ticket para el pedido ".$idpedido);
            //     }

            //     $idticket = mysqli_insert_id($this->con);

            //     // Incrementar folio
            //     $query = "
            //     update
            //         tsucursales
            //     set
            //         folio = folio + 1
            //     where
            //         idsucursal = '".$vendedor["idsucursal"]."'";
            //     mysqli_query($this->con,$query);

            //     // Insertar en tformaspagoticket
            //     $query = "
            //     insert
            //     into
            //         tformaspagoticket
            //     (
            //         idticket,
            //         idvendedor,
            //         idformapago,
            //         monto,
            //         montorecibido
            //     ) values (
            //         '".$idticket."',
            //         '".$vendedor["idvendedor"]."',
            //         '".$idformapago."',
            //         '".$monto."',
            //         '".$monto."'
            //     )";
            //     mysqli_query($this->con,$query);

            //     // Insertar en tformaspagopedido
            //     $query = "
            //     insert
            //     into
            //         tformaspagopedido
            //     (
            //         idpedido,
            //         idvendedor,
            //         idformapago,
            //         monto,
            //         montorecibido,
            //         fecha
            //     ) values (
            //         '".$idpedido."',
            //         '".$vendedor["idvendedor"]."',
            //         '".$idformapago."',
            //         '".$monto."',
            //         '".$monto."',
            //         '".$fecha."'
            //     )";
            //     mysqli_query($this->con,$query);

            //     // Actualizar abonado en tpedidos
            //     $query = "
            //     update
            //         tpedidos
            //     set
            //         abonado = abonado + ".$monto."
            //     where
            //         idpedido = '".$idpedido."'";
            //     mysqli_query($this->con,$query);

            //     // Activar pedido si pendiente=0
            //     $query = "
            //     select
            //         *
            //     from
            //         tpedidos
            //     where
            //         pendiente = 0 and
            //         idpedido = '".$idpedido."'";
            //     if(mysqli_num_rows(mysqli_query($this->con,$query)) > 0){
            //         $query = "
            //         update
            //             tpedidos
            //         set
            //             pendiente = 1
            //         where
            //             idpedido = '".$idpedido."'";
            //         mysqli_query($this->con,$query);
            //     }

            //     // Si total == abonado, marcar como pagado
            //     $copiasticket = 2;
            //     $query = "
            //     select
            //         *
            //     from
            //         tpedidos
            //     where
            //         idpedido = '".$idpedido."' and
            //         total = abonado";
            //     if(mysqli_num_rows(mysqli_query($this->con,$query)) > 0){
            //         $query = "
            //         update
            //             tpedidos
            //         set
            //             statuspago = 1
            //         where
            //             idpedido = '".$idpedido."'";
            //         mysqli_query($this->con,$query);
            //         $copiasticket = 1;
            //     }

            //     // Insertar en tticketspedidos
            //     $query = "
            //     insert
            //     into
            //         tticketspedidos
            //     (
            //         idvendedor,
            //         idpedido,
            //         total,
            //         fecha,
            //         status
            //     ) values (
            //         '".$vendedor["idvendedor"]."',
            //         '".$idpedido."',
            //         '".$monto."',
            //         '".$fecha."',
            //         'A'
            //     )";
            //     mysqli_query($this->con,$query);

            //     $tickets[] = array(
            //         "idticket" => $idticket,
            //         "copias" => $copiasticket
            //     );
            // }

            // // Confirmar transacción
            // mysqli_commit($this->con);

            // Inician los preparativos para generar el complemento de pago en caso de que se requiera complemento
            if($complemento==1){
                // Mandamos llamar la función para generar complemento de pago
                $resultadoComplemento = $this->generarComplementoPago(array(
                    "idcliente" => $idcliente,
                    "idformapago" => $idformapago,
                    "fecha" => $fecha,
                    "facturas" => $pedidos
                ));
            }

            $mensaje = "Pago registrado correctamente";

            if($complemento==1 && isset($resultadoComplemento)){
                $mensaje .= ". " . $resultadoComplemento["message"];
            }

            $respuesta = array(
                "success" => true,
                "message" => $mensaje,
                // "tickets" => $tickets
            );

        }catch(Exception $e){
            // Revertir todos los cambios si algo falló
            // mysqli_rollback($this->con);

            $respuesta = array(
                "success" => false,
                "message" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    public function generarComplementoPago($post){
        try{
            $idcliente = mysqli_real_escape_string($this->con, $post["idcliente"]);
            $idformapago = mysqli_real_escape_string($this->con, $post["idformapago"]);
            $fecha = mysqli_real_escape_string($this->con, $post["fecha"]);

            $facturas = [];
            if(isset($post["facturas"]) && is_array($post["facturas"])){
                foreach($post["facturas"] as $factura){
                    $idfactura = mysqli_real_escape_string($this->con, $factura["idfactura"]);
                    $monto = floatval($factura["monto"]);

                    $query = "
                    select
                        a.saldo,
                        a.uuid,
                        a.serie,
                        a.folio,
                        a.idemisor,
                        a.idrazonsocial,
                        (
                        	select
                        		count(*)
                        	from
                        		tformaspagopedido
                        	where
                        		idpedido = b.idpedido
                        ) + 1 as parcialidad,
                        round((a.iva/a.subtotal)*100,0) as impuesto,
                        b.idtienda
                    from
                        tfacturas a
                    left join
                    	vpedidos b
                    on
                    	b.idfactura = a.idfactura
                    where
                        a.idfactura = '".$idfactura."'";
                    $datosFactura = mysqli_fetch_assoc(mysqli_query($this->con, $query));

                    $facturas[] = array(
                        "idfactura" => $idfactura,
                        "idtienda" => $datosFactura["idtienda"],
                        "monto" => $monto,
                        "saldo" => floatval($datosFactura["saldo"]),
                        "uuid" => $datosFactura["uuid"],
                        "serie" => $datosFactura["serie"],
                        "folio" => $datosFactura["folio"],
                        "idemisor" => $datosFactura["idemisor"],
                        "idrazonsocial" => $datosFactura["idrazonsocial"],
                        "parcialidad" => $datosFactura["parcialidad"],
                        "impuesto" => $datosFactura["impuesto"]
                    );
                }
            }

            // Obtener idemisor e idrazonsocial de la primera factura
            $idemisor = $facturas[0]["idemisor"];
            $idrazonsocial = $facturas[0]["idrazonsocial"];
            $idtienda = $facturas[0]["idtienda"];

            // Obtener datos del emisor
            $query = "
            select
                *
            from
                temisores
            where
                idemisor = '".$idemisor."'";
            $infoEmisor = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            // Obtener datos de la razón social del cliente
            $query = "
            select
                *
            from
                tclienterazonessociales
            where
                idrazonsocial = '".$idrazonsocial."'";
            $razonsocial = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            // Calcular total del pago
            $total = array_sum(array_column($facturas, "monto"));

            // Insertar registro en tpagos sin serie, folio, uuid ni timbrado
            $idusuario = $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"];
            $query = "
            insert
            into
                tpagos
            (
                idusuario,
                idemisor,
                idcliente,
                idrazonsocial,
                total,
                idformapago,
                fecha,
                status
            ) values (
                '".$idusuario."',
                '".$idemisor."',
                '".$idcliente."',
                '".$idrazonsocial."',
                '".$total."',
                '".$idformapago."',
                '".$fecha."',
                1
            )";
            if(!mysqli_query($this->con, $query)){
                throw new Exception("Error al insertar el registro de pago");
            }
            $idpago = mysqli_insert_id($this->con);

            // Obtener régimen fiscal del emisor
            $query = "
            select
                regimenfiscal
            from
                sat_tcatregimenfiscal
            where
                idregimenfiscal = '".$infoEmisor["idregimenfiscal"]."'";
            $regimen_fiscal = mysqli_fetch_assoc(mysqli_query($this->con, $query))["regimenfiscal"];

            $emisor = array(
                "Rfc" => $infoEmisor["rfc"],
                "Nombre" => $infoEmisor["razon_social"],
                "RegimenFiscal" => $regimen_fiscal,
                "LugarExpedicion" => $infoEmisor["codigo_postal"]
            );

            $receptor = array(
                "Rfc" => $razonsocial["rfc"],
                "Nombre" => utf8_decode($razonsocial["razon_social"]),
                "UsoCFDI" => "CP01",
                "DomicilioFiscalReceptor" => $razonsocial["codigo_postal"],
                "RegimenFiscalReceptor" => $razonsocial["regimenfiscal"]
            );

            // Obtenemos los datos de la forma de pago interna
            $query = "
            select
                idformapago_sat
            from
                tcatformaspago
            where
                idformapago = '".$idformapago."'";
            $idformapago = mysqli_fetch_assoc(mysqli_query($this->con,$query))["idformapago_sat"];

            // Obtener clave de forma de pago SAT
            $query = "
            select
                formapago
            from
                sat_tcatformaspago
            where
                idformapago = '".$idformapago."'";
            $formapago = mysqli_fetch_assoc(mysqli_query($this->con, $query))["formapago"];

            $pago = array(
                "fecha" => $fecha . " 12:00:00",
                "FormaPago" => $formapago,
                "moneda" => "MXN",
                "monto" => $total,
                "tipocambio" => 1
            );

            // Construir documentos relacionados
            $pagos = array();
            foreach($facturas as $fac){
                $pagos[] = array(
                    "Folio" => $fac["folio"],
                    "IdDocumento" => $fac["uuid"],
                    "ImpPagado" => $fac["monto"],
                    "ImpSaldoAnt" => $fac["saldo"],
                    "ImpSaldoInsoluto" => $fac["saldo"] - $fac["monto"],
                    "MonedaDR" => "MXN",
                    "equivalencia" => 1,
                    "NumParcialidad" => $fac["parcialidad"],
                    "Serie" => $fac["serie"],
                    "ObjetoImpDR" => "02",
                    "base_iva_trasladado_" . $fac["impuesto"] => sprintf("%.6f", $fac["monto"] / (1 + ($fac["impuesto"] / 100)))
                );
            }

            // Obtener numero de certificado, certificado y archivo keypem
            $ruta_server = $_SERVER["DOCUMENT_ROOT"] . "/../1.uniformescisne.mx";
            $ruta = $ruta_server."/emisores/" . str_replace("&", "_", $infoEmisor['rfc']);
            $numero_certificado = $this->obtenerNumeroCertificado($ruta."/sat/"."certificado.cer");
            $certificado = $this->obtenerContenidoCertificado($ruta."/sat/"."certificado.cer");
            $archivo_keypem = file_get_contents($ruta."/sat/"."llave.key.pem");

            // Preparar datos para el timbrador
            $datos = array(
                "api_key" => "tek_npzimyh2ajjxpj3p3j2ofozt7c6deej9uu",
                "Version" => "4.0",
                "new" => 1,
                "pruebas" => 1,
                "numero_certificado" => $numero_certificado,
                "certificado" => $certificado,
                "keypem" => $archivo_keypem,
                "tipoComprobante" => "P",
                "serie" => $infoEmisor["serie_pagos"],
                "folio" => $infoEmisor["folio_pagos"],
                "emisor" => $emisor,
                "receptor" => $receptor,
                "pago" => $pago,
                "pagos" => $pagos
            );

            // Enviar al timbrador
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

            if ($response["response"] == true) {
                // Timbrado exitoso: actualizar tpagos e incrementar folio en una transacción
                mysqli_begin_transaction($this->con);

                $query = "
                update
                    tpagos
                set
                    serie = '".$infoEmisor["serie_pagos"]."',
                    folio = '".$infoEmisor["folio_pagos"]."',
                    uuid = '".$response["uuid"]."',
                    timbrado = NOW()
                where
                    idpago = '".$idpago."'";
                $ok1 = mysqli_query($this->con, $query);

                $query = "
                update
                    temisores
                set
                    folio_pagos = folio_pagos + 1
                where
                    idemisor = '".$idemisor."'";
                $ok2 = mysqli_query($this->con, $query);

                if($ok1 && $ok2){
                    mysqli_commit($this->con);

                    // Enviar complemento por correo
                    $mensajeCorreo = "Complemento de pago timbrado correctamente";

                    // Se guardan los documentos en la carpeta específica
                    file_put_contents($ruta."/pagos/".$response["uuid"].".xml",base64_decode($response["xml"]));
                    file_put_contents($ruta."/pagos/".$response["uuid"].".pdf",base64_decode($response["pdf"]));

                    // Obtener correo del cliente
                    $query = "
                    select
                        correo
                    from
                        tclientes
                    where
                        idcliente = '".$idcliente."'";
                    $correoCliente = mysqli_fetch_assoc(mysqli_query($this->con, $query))["correo"];

                    if(!empty($correoCliente)){
                        // Enviar correo
                        include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/classes/Correos.php");
                        $claseCorreos = new Correos();

                        //Se envia la factura por correo
                        $folio = $infoEmisor["serie_pagos"]."-".$infoEmisor["folio_pagos"];
                        $fecha = date("Y-m-d");

                        include($ruta_server."/assets/plantillas/correo/envioComplemento.php");
                        include($ruta_server."/assets/plantillas/correo/base.php");

                        $respuesta = $claseCorreos->enviarCorreo(array(
                            "idtienda" => $idtienda,
                            "asunto" => "Envío de complemento de pago",
                            "mensaje" => $cuerpo,
                            "correos" => array(
                                $correo
                            ),
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

                        if($respuesta["result"] == "success"){
                            $mensajeCorreo .= ". El complemento fue enviado por correo electrónico a ".$correoCliente;
                        }else{
                            $mensajeCorreo .= ". Sin embargo, no se pudo enviar por correo: " . $respuesta["mensaje"];
                        }
                    }else{
                        $mensajeCorreo .= ". No se envió por correo porque el cliente no tiene correo electrónico registrado";
                    }

                    $respuesta = array(
                        "success" => true,
                        "message" => $mensajeCorreo
                    );
                }else{
                    mysqli_rollback($this->con);
                    $respuesta = array(
                        "success" => false,
                        "message" => "El timbrado fue exitoso pero ocurrió un error al actualizar los datos"
                    );
                }
            } else {
                $respuesta = array(
                    "success" => false,
                    "message" => "El pago se registró pero el complemento no pudo ser timbrado (" . $response["mensaje"] . ")"
                );
            }

        }catch(Exception $e){
            $respuesta = array(
                "success" => true,
                "message" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    public function fecha_formateada($fecha,$salto_linea = true){
        $fecha = explode(" ", $fecha);
        $hora = $fecha[1];
        $fecha = $fecha[0];

        $fecha = explode("-", $fecha);
        $dia = $fecha[2];
        $mes = $fecha[1];
        $ano = $fecha[0];

        $fecha = $dia . "/";

        switch ($mes) {
            case "01":
                $fecha .= "Ene";
                break;
            case "02":
                $fecha .= "Feb";
                break;
            case "03":
                $fecha .= "Mar";
                break;
            case "04":
                $fecha .= "Abr";
                break;
            case "05":
                $fecha .= "May";
                break;
            case "06":
                $fecha .= "Jun";
                break;
            case "07":
                $fecha .= "Jul";
                break;
            case "08":
                $fecha .= "Ago";
                break;
            case "09":
                $fecha .= "Sep";
                break;
            case "10":
                $fecha .= "Oct";
                break;
            case "11":
                $fecha .= "Nov";
                break;
            case "12":
                $fecha .= "Dic";
                break;
        }

        $fecha .= "/" . $ano;

        if ($hora != "") {
            $fecha .= (($salto_linea) ? "<br>" : " ") . date("h:i a", strtotime($hora));
        }

        return $fecha;
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

    public function verPDF($post){
        try{
            $idpago = mysqli_real_escape_string($this->con,$post["idpago"]);

            $query = "
            select
                a.uuid,
                b.rfc as emisor_rfc
            from
                tpagos a
            left join
            	temisores b
            on
            	b.idemisor = a.idemisor
            where
                a.idpago = '".$idpago."'";
            $result = mysqli_query($this->con,$query);

            if(mysqli_num_rows($result)==0){
                throw new Exception("No se encontró información del pago");
            }

            $pago = mysqli_fetch_assoc($result);

            $ruta = $_SERVER["DOCUMENT_ROOT"]."/../1.uniformescisne.mx/emisores/".$pago["emisor_rfc"]."/pagos/".$pago["uuid"].".pdf";

            if(!file_exists($ruta)){
                throw new Exception("No se encontró el archivo seleccionado");
            }

            $respuesta = array(
                "success" => true,
                "pdf" => base64_encode(file_get_contents($ruta))
            );
        }catch(Exception $e){
            $respuesta = array(
                "success" => false,
                "message" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    public function descargarPago($post){
        try{
            $idpago = mysqli_real_escape_string($this->con,$post["idpago"]);

            $query = "
            select
                a.uuid,
                b.rfc as emisor_rfc
            from
                tpagos a
            left join
                temisores b
            on
                b.idemisor = a.idemisor
            where
                a.idpago = '".$idpago."'";
            $result = mysqli_query($this->con,$query);

            if(mysqli_num_rows($result)==0){
                throw new Exception("No se encontró información del pago");
            }

            $pago = mysqli_fetch_assoc($result);
            $ruta_base = $_SERVER["DOCUMENT_ROOT"]."/../1.uniformescisne.mx/emisores/".$pago["emisor_rfc"]."/pagos/".$pago["uuid"];

            if(!file_exists($ruta_base.".xml")){
                throw new Exception("No se encontró el archivo XML del pago");
            }

            if(!file_exists($ruta_base.".pdf")){
                throw new Exception("No se encontró el archivo PDF del pago");
            }

            $respuesta = array(
                "success" => true,
                "uuid" => $pago["uuid"],
                "xml" => base64_encode(file_get_contents($ruta_base.".xml")),
                "pdf" => base64_encode(file_get_contents($ruta_base.".pdf"))
            );
        }catch(Exception $e){
            $respuesta = array(
                "success" => false,
                "message" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    public function verXML($post){
        try{
            $idpago = mysqli_real_escape_string($this->con,$post["idpago"]);

            $query = "
            select
                a.uuid,
                b.rfc as emisor_rfc
            from
                tpagos a
            left join
            	temisores b
            on
            	b.idemisor = a.idemisor
            where
                a.idpago = '".$idpago."'";
            $result = mysqli_query($this->con,$query);

            if(mysqli_num_rows($result)==0){
                throw new Exception("No se encontró información del pago");
            }

            $pago = mysqli_fetch_assoc($result);

            $ruta = $_SERVER["DOCUMENT_ROOT"]."/../1.uniformescisne.mx/emisores/".$pago["emisor_rfc"]."/pagos/".$pago["uuid"].".xml";

            if(!file_exists($ruta)){
                throw new Exception("No se encontró el archivo seleccionado");
            }

            $respuesta = array(
                "success" => true,
                "xml" => base64_encode(file_get_contents($ruta)),
                "uuid" => $pago["uuid"]
            );
        }catch(Exception $e){
            $respuesta = array(
                "success" => false,
                "message" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

}
?>