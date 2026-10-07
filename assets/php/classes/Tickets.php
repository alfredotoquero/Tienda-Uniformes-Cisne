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

            // Se arrastra el estado de la factura porque el ticket conserva idfactura aunque
            // la factura se cancele: sin el status no hay forma de distinguir un ticket ya
            // facturado de uno cuya factura quedó cancelada y se puede volver a facturar.
            $query = "
            select
                a.folio,
                a.idcuenta,
                a.idfactura,
                c.status as factura_status,
                b.idtienda,
                b.nombre as sucursal
            from
                ttickets a
            left join
                tsucursales b
            on
                b.idsucursal = a.idsucursal
            left join
                tfacturas c
            on
                c.idfactura = a.idfactura
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

    public function obtenerTicketsPorCorte($post) {
        try {
            $idcorte = mysqli_real_escape_string($this->con, $post["idcorte"]);

            $query = "
            SELECT
                a.*,
                b.serie AS factura_serie,
                b.folio  AS factura_folio,
                b.status AS factura_status,
                c.idtienda
            FROM
                ttickets a
            LEFT JOIN
                tfacturas b ON b.idfactura = a.idfactura
            LEFT JOIN
                tsucursales c ON c.idsucursal = a.idsucursal
            WHERE
                a.idcorte = '$idcorte'
            ORDER BY
                a.idticket DESC";

            $result = mysqli_query($this->con, $query);

            $tickets = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $tickets[] = $row;
            }

            $respuesta = [
                "respuesta" => "OK",
                "tickets"   => $tickets
            ];
        } catch (Exception $e) {
            $respuesta = [
                "respuesta" => "ERROR",
                "mensaje"   => $e->getMessage()
            ];
        } finally {
            return $respuesta;
        }
    }

    public function facturarTicket($post){
        try{
            $idticket = mysqli_real_escape_string($this->con,$post["idticket"]);
            $idvendedor = mysqli_real_escape_string($this->con,$post["idvendedor"]);
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

            // Un ticket solo puede tener una factura viva. Se revisa aquí y no solo en el
            // formulario porque el estado pudo cambiar desde que se abrió la pantalla, y
            // timbrar dos veces el mismo ticket obliga a cancelar ante el SAT.
            $query = "
            select
                b.idfactura,
                b.serie,
                b.folio,
                b.status
            from
                ttickets a
            join
                tfacturas b
            on
                b.idfactura = a.idfactura
            where
                a.idticket = '".$idticket."'";
            $facturaprevia = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            // status 3 = cancelada: esa sí se puede reemplazar. La 2 (cancelación pendiente
            // de aceptación) todavía no, porque el receptor puede rechazarla y seguiría viva.
            if($facturaprevia && $facturaprevia["status"] != 3){
                throw new Exception(($facturaprevia["status"] == 2)
                    ? "Este ticket tiene la factura ".$facturaprevia["serie"]."-".$facturaprevia["folio"]." en proceso de cancelación. Espera a que el SAT la confirme para volver a facturarlo."
                    : "Este ticket ya está facturado con la factura ".$facturaprevia["serie"]."-".$facturaprevia["folio"].".");
            }

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
                "Nombre" => utf8_decode(trim($tmp["razon_social"])),
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
                "Nombre" => utf8_decode(trim($tmp["razon_social"])),
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
                
                $valor_unitario = $tmp["precio"]/$tmp["cantidad"];

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
                "pruebas" => 0,
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
                // La factura de ticket la emite un vendedor, no un usuario del administrativo:
                // se guarda en idvendedor y idusuario queda en NULL. Mandar el id del vendedor
                // en idusuario rompe la llave foránea a tusuarios.
                $query = "
                insert
                into
                    tfacturas
                (
                    idvendedor,
                    idemisor,
                    razonsocial,
                    rfc,
                    codigo_postal,
                    regimenfiscal,
                    usocfdi,
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
                    '".$idvendedor."',
                    '".$idemisor."',
                    '".$razonsocial."',
                    '".$rfc."',
                    '".$codigo_postal."',
                    '".$regimenfiscal."',
                    '".$usocfdi."',
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

                include($_SERVER["DOCUMENT_ROOT"]."/assets/php/classes/Correos.php");
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

    // La tarjeta de regalo descuenta saldo de ttarjetasregalo contra un código, así que su
    // partida no se puede reasignar sin dejar ese saldo huérfano. Las formas con pesos != 1
    // (Efectivo USD) tampoco: su monto está en divisa y reasignarlas descuadraría el corte.
    const FORMAPAGO_TARJETAREGALO = 4;

    /**
     * Formas de pago que se pueden elegir como destino al reasignar una partida: solo las
     * que se capturan en pesos y no dependen de un saldo externo.
     */
    public function obtenerFormasPagoReasignables(){
        try{
            $query = "
            select
                *
            from
                tcatformaspago
            where
                pesos = 1 and
                idformapago != ".self::FORMAPAGO_TARJETAREGALO."
            order by
                idformapago";
            $result = mysqli_query($this->con,$query);

            $formaspago = array();
            while($row = mysqli_fetch_assoc($result)){
                $formaspago[] = $row;
            }

            $respuesta = array(
                "respuesta" => "OK",
                "formaspago" => $formaspago
            );

        }catch(Exception $e){
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    /**
     * Partidas de pago de un ticket. Se marca cada una con "editable" para que el formulario
     * pinte un select o un texto fijo sin tener que repetir la regla en la vista.
     */
    public function obtenerFormasPagoTicket($post){
        try{
            $idticket = mysqli_real_escape_string($this->con,$post["idticket"]);

            $query = "
            select
                a.idformapagoticket,
                a.idformapago,
                a.monto,
                a.archivo,
                b.nombre,
                b.pesos
            from
                tformaspagoticket a
            left join
                tcatformaspago b
            on
                b.idformapago = a.idformapago
            where
                a.idticket = '".$idticket."'
            order by
                a.idformapagoticket";
            $result = mysqli_query($this->con,$query);

            $partidas = array();
            while($row = mysqli_fetch_assoc($result)){
                $row["editable"] = ($row["pesos"]==1 && $row["idformapago"]!=self::FORMAPAGO_TARJETAREGALO);
                $partidas[] = $row;
            }

            $respuesta = array(
                "respuesta" => "OK",
                "partidas" => $partidas
            );

        }catch(Exception $e){
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    /**
     * Bitácora de reasignaciones de un ticket, de la más reciente a la más antigua.
     */
    public function obtenerBitacoraFormasPago($post){
        try{
            $idticket = mysqli_real_escape_string($this->con,$post["idticket"]);

            $query = "
            select
                a.monto,
                a.archivoeliminado,
                a.fecha,
                b.nombre as formapagoanterior,
                c.nombre as formapagonuevo,
                d.nombre as vendedor
            from
                tformaspagoticket_log a
            left join
                tcatformaspago b
            on
                b.idformapago = a.idformapagoanterior
            left join
                tcatformaspago c
            on
                c.idformapago = a.idformapagonuevo
            left join
                tvendedores d
            on
                d.idvendedor = a.idvendedor
            where
                a.idticket = '".$idticket."'
            order by
                a.idlog desc";
            $result = mysqli_query($this->con,$query);

            $movimientos = array();
            while($row = mysqli_fetch_assoc($result)){
                $movimientos[] = $row;
            }

            $respuesta = array(
                "respuesta" => "OK",
                "movimientos" => $movimientos
            );

        }catch(Exception $e){
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    /**
     * Reasigna la forma de pago de una o varias partidas de un ticket sin tocar los montos:
     * el total del ticket no cambia, solo se mueve de una columna del corte a otra.
     */
    public function cambiarFormaPagoTicket($post){
        try{
            $idticket = mysqli_real_escape_string($this->con,$post["idticket"]);
            $idusuario = mysqli_real_escape_string($this->con,$post["idusuario"]);
            $seleccion = (isset($post["formapago"]) && is_array($post["formapago"])) ? $post["formapago"] : array();

            // El estado pudo cambiar desde que se abrió el modal, así que se vuelve a validar
            // aquí: si mientras tanto se timbró una factura, el desglose ya viajó en el CFDI
            // y moverlo lo desincroniza.
            $query = "
            select
                a.idticket,
                a.idcorte,
                a.idfactura,
                b.status as factura_status,
                c.status as corte_status
            from
                ttickets a
            left join
                tfacturas b
            on
                b.idfactura = a.idfactura
            left join
                tcortessucursales c
            on
                c.idcorte = a.idcorte
            where
                a.idticket = '".$idticket."'";
            $result = mysqli_query($this->con,$query);

            if(mysqli_num_rows($result)==0){
                throw new Exception("No se pudo recuperar la información del ticket");
            }

            $ticket = mysqli_fetch_assoc($result);

            if(!empty($ticket["idfactura"]) && $ticket["factura_status"] != 3){
                throw new Exception("Este ticket tiene una factura vigente. Para cambiar la forma de pago primero debes cancelarla.");
            }

            $partidas = $this->obtenerFormasPagoTicket(array("idticket" => $idticket));
            if($partidas["respuesta"]!="OK"){
                throw new Exception($partidas["mensaje"]);
            }

            $destinos = array();
            foreach($this->obtenerFormasPagoReasignables()["formaspago"] as $formapago){
                $destinos[$formapago["idformapago"]] = $formapago["nombre"];
            }

            $afectadas = array();
            $cambios = 0;

            foreach($partidas["partidas"] as $partida){
                $idformapagoticket = $partida["idformapagoticket"];

                if(!isset($seleccion[$idformapagoticket])){
                    continue;
                }

                $idformapagonuevo = mysqli_real_escape_string($this->con,$seleccion[$idformapagoticket]);

                if($idformapagonuevo == $partida["idformapago"]){
                    continue;
                }

                if(!$partida["editable"]){
                    throw new Exception("La partida de ".$partida["nombre"]." no se puede reasignar");
                }

                if(!isset($destinos[$idformapagonuevo])){
                    throw new Exception("La forma de pago seleccionada no es válida para una reasignación");
                }

                // El comprobante pertenece a la forma de pago original (transferencia, cheque
                // o depósito); al reasignar la partida deja de respaldar nada, así que se borra
                // el archivo y se limpia la columna.
                $archivo = $partida["archivo"];

                $query = "
                update
                    tformaspagoticket
                set
                    idformapago = '".$idformapagonuevo."',
                    archivo = ''
                where
                    idformapagoticket = '".$idformapagoticket."'";
                mysqli_query($this->con,$query);

                if(!empty($archivo)){
                    $ruta = $_SERVER["DOCUMENT_ROOT"]."/imagenes/depositos/".$idticket."/".basename($archivo);
                    if(is_file($ruta)){
                        unlink($ruta);
                    }
                }

                $query = "
                insert
                into
                    tformaspagoticket_log
                (
                    idformapagoticket,
                    idticket,
                    idcorte,
                    idformapagoanterior,
                    idformapagonuevo,
                    monto,
                    archivoeliminado,
                    idvendedor,
                    fecha
                ) values (
                    '".$idformapagoticket."',
                    '".$idticket."',
                    '".$ticket["idcorte"]."',
                    '".$partida["idformapago"]."',
                    '".$idformapagonuevo."',
                    '".$partida["monto"]."',
                    '".mysqli_real_escape_string($this->con,$archivo)."',
                    '".$idusuario."',
                    '".date("Y-m-d H:i:s")."'
                )";
                mysqli_query($this->con,$query);

                $afectadas[$partida["idformapago"]] = true;
                $afectadas[$idformapagonuevo] = true;
                $cambios++;
            }

            if($cambios==0){
                throw new Exception("No seleccionaste ningún cambio de forma de pago");
            }

            // En un corte activo el desglose se calcula al vuelo, pero al cerrarlo se congela
            // en tcortesucursal_formaspago: si no se recalculan ahí las formas afectadas, el
            // listado de cortes y los PDFs siguen mostrando el desglose anterior.
            if($ticket["corte_status"]=="T"){
                $this->recalcularFormasPagoCorte($ticket["idcorte"], array_keys($afectadas));
            }

            $respuesta = array(
                "respuesta" => "OK",
                "tipo" => "mensajereload",
                "titulo" => "Forma de pago actualizada",
                "mensaje" => "Se ".(($cambios==1) ? "actualizó 1 partida" : "actualizaron ".$cambios." partidas")." del ticket"
            );

        }catch(Exception $e){
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje" => $e->getMessage()
            );
        }catch(Throwable $e){
            $respuesta = array(
                "respuesta" => "ERROR",
                "mensaje" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    /**
     * Rehace el total congelado de las formas de pago indicadas en un corte ya cerrado.
     * Suma monto sin convertir a pesos, igual que el cierre del corte, porque solo se
     * reasignan formas capturadas en pesos.
     */
    private function recalcularFormasPagoCorte($idcorte, $formaspago){
        $idcorte = mysqli_real_escape_string($this->con,$idcorte);

        foreach($formaspago as $idformapago){
            $idformapago = mysqli_real_escape_string($this->con,$idformapago);

            $query = "
            select
                sum(monto) as monto
            from
                tformaspagoticket
            where
                idticket in (select idticket from ttickets where idcorte = '".$idcorte."') and
                idformapago = '".$idformapago."'";
            $monto = mysqli_fetch_assoc(mysqli_query($this->con,$query))["monto"];
            $monto = ($monto > 0) ? $monto : 0;

            $query = "
            select
                idformapago
            from
                tcortesucursal_formaspago
            where
                idcorte = '".$idcorte."' and
                idformapago = '".$idformapago."'";

            if(mysqli_num_rows(mysqli_query($this->con,$query))>0){
                $query = "
                update
                    tcortesucursal_formaspago
                set
                    total = '".$monto."'
                where
                    idcorte = '".$idcorte."' and
                    idformapago = '".$idformapago."'";
            }else{
                $query = "
                insert
                into
                    tcortesucursal_formaspago
                (
                    idcorte,
                    idformapago,
                    total
                ) values (
                    '".$idcorte."',
                    '".$idformapago."',
                    '".$monto."'
                )";
            }
            mysqli_query($this->con,$query);
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
