<?
class Pagos{

    private $con;
    private $claseSAT;

    function __construct(){
        include_once($_SERVER["DOCUMENT_ROOT"]."/2cnytm029mp3r/cm293uc5904uh.php");
        include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/classes/SAT.php");
        $this->con = $con;
        $this->claseSAT = new SAT();
    }

    /**
     * Devuelve el SQL de la relación pedido -> factura, resolviéndola por las tres vías que
     * existen en la base: tpedidosfacturas (facturación parcial, la relación vigente),
     * tpedidos.idfactura (legado, solo conserva la última factura del pedido) y
     * ttickets.idfactura (facturación de tickets desde cortes). Se usa como tabla derivada.
     *
     * @return string SQL de la tabla derivada con las columnas idpedido e idfactura
     */
    private function relacionPedidoFactura(){
        return "
                (
                select
                    idpedido,
                    idfactura
                from
                    tpedidosfacturas
                union
                select
                    idpedido,
                    idfactura
                from
                    tpedidos
                where
                    idfactura > 0
                union
                select
                    idpedido,
                    idfactura
                from
                    ttickets
                where
                    idfactura > 0
                )";
    }

    /**
     * Obtiene las facturas PPD vigentes y con saldo pendiente de un pedido, ordenadas de la
     * más antigua a la más reciente (así se amortizan las parcialidades en ese orden).
     *
     * @param int $idpedido
     * @return array Facturas del pedido que requieren complemento de pago
     */
    private function getFacturasPPDPedido($idpedido){
        $idpedido = mysqli_real_escape_string($this->con,$idpedido);

        $query = "
        select distinct
            f.idfactura,
            f.serie,
            f.folio,
            f.uuid,
            f.saldo,
            f.idemisor,
            f.idrazonsocial,
            f.razonsocial,
            f.rfc,
            f.codigo_postal,
            f.regimenfiscal,
            round((f.iva/f.subtotal)*100,0) as impuesto
        from
            ".$this->relacionPedidoFactura()." pf
        join
            tfacturas f
        on
            f.idfactura = pf.idfactura
        where
            pf.idpedido = '".$idpedido."' and
            f.idmetodopago = 1 and
            (f.status is null or f.status = 1) and
            f.uuid is not null and
            f.uuid <> '' and
            f.saldo > 0
        order by
            f.idfactura";
        $result = mysqli_query($this->con,$query);

        return ($result) ? mysqli_fetch_all($result,MYSQLI_ASSOC) : array();
    }

    /**
     * Determina, del lado del servidor, si un pago debe generar complemento de pago. No se
     * confía en la bandera que manda el navegador: se revisa si alguno de los pedidos que
     * cubre el pago se asignó a facturas PPD vigentes con saldo pendiente.
     *
     * @param int $idpago
     * @return bool
     */
    private function pagoRequiereComplemento($idpago){
        $idpago = mysqli_real_escape_string($this->con,$idpago);

        $query = "
        select
            count(*) as total
        from
            tformaspagopedido a
        join
            tfacturas f
        on
            f.idfactura = a.idfactura
        where
            a.idpago = '".$idpago."' and
            f.idmetodopago = 1 and
            (f.status is null or f.status = 1) and
            f.uuid is not null and
            f.uuid <> '' and
            f.saldo > 0";
        $result = mysqli_query($this->con,$query);

        return ($result) ? (mysqli_fetch_assoc($result)["total"] > 0) : false;
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
            $idusuario = mysqli_real_escape_string($this->con, $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]);

            $query = "select idsucursal from tvendedores where idvendedor = '".$idusuario."'";
            $idsucursal = mysqli_fetch_assoc(mysqli_query($this->con,$query))["idsucursal"];

            if(empty($idsucursal)){
                throw new Exception("No se pudo recuperar la sucursal del vendedor");
            }

            $query = "
            select
                a.idpago,
                a.idcliente,
                coalesce(b.nombre, a.cliente) as cliente,
                a.total,
                a.idformapago,
                c.nombre as formapago,
                a.fecha,
                a.uuid,
                a.status,
                -- Los pagos capturados con el desglose por documento dicen a qué factura
                -- fue cada abono; los anteriores al desglose solo guardaron el pedido, así que
                -- para esos se sigue resolviendo por las facturas del pedido
                case when exists (
                    select 1
                    from tformaspagopedido fp
                    where fp.idpago = a.idpago and fp.idfactura is not null
                ) then exists (
                    select 1
                    from tformaspagopedido fp
                    join tfacturas f on f.idfactura = fp.idfactura
                    where fp.idpago = a.idpago
                      and f.idmetodopago = 1
                      and (f.status is null or f.status = 1)
                      and f.uuid is not null
                      and f.uuid <> ''
                      and f.saldo > 0
                ) else exists (
                    select 1
                    from tformaspagopedido fp
                    join ".$this->relacionPedidoFactura()." pf on pf.idpedido = fp.idpedido
                    join tfacturas f on f.idfactura = pf.idfactura
                    where fp.idpago = a.idpago
                      and f.idmetodopago = 1
                      and (f.status is null or f.status = 1)
                      and f.uuid is not null
                      and f.uuid <> ''
                      and f.saldo > 0
                ) end as tiene_factura
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
            join
                tvendedores v
            on
                a.idusuario = v.idvendedor
            where
                a.fecha between '".$fecha_i."' and '".$fecha_f."' and
                v.idsucursal = '".$idsucursal."'
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

    /**
     * Devuelve el SQL del monto de un pago que ya se asignó a una factura pero que todavía no
     * se refleja en su saldo. El saldo de tfacturas solo baja cuando se timbra el complemento
     * (y en las facturas PUE nunca baja, porque no llevan complemento), así que sin este
     * descuento un mismo saldo se podría cobrar dos veces.
     *
     * @param string $campofactura Expresión SQL con el idfactura contra el que se correlaciona
     * @return string Subconsulta escalar con el monto asignado pendiente de reflejarse
     */
    private function montoAsignadoPendiente($campofactura){
        return "
                coalesce((
                select
                    sum(fp.monto)
                from
                    tformaspagopedido fp
                left join
                    tpagos p
                on
                    p.idpago = fp.idpago
                where
                    fp.idfactura = ".$campofactura." and
                    (p.idpago is null or p.status <> 3) and
                    not exists (
                        select
                            1
                        from
                            tpagosfacturas pgf
                        where
                            pgf.idpago = fp.idpago and
                            pgf.idfactura = fp.idfactura
                    )
                ),0)";
    }

    /**
     * Arma los renglones que se capturan al agregar un pago. Ya no es un renglón por pedido:
     * cada documento por cobrar es su propio renglón, de modo que el vendedor decida a qué
     * factura va cada peso en vez de que el sistema lo reparta solo.
     *
     * Por cada pedido con saldo del cliente se devuelve:
     *   - un renglón por cada factura vigente del pedido (PPD o PUE) que aún tenga restante
     *   - un renglón "Sin factura" por la parte del pedido que todavía no se ha facturado
     *
     * El restante de la parte no facturada se deduce del pedido (total - abonado) menos lo que
     * siguen debiendo sus facturas, así que los renglones de un pedido nunca suman más de lo
     * que el pedido debe.
     *
     * @param array $post   Contiene el cliente seleccionado y el vendedor
     * @return array        Renglones por cobrar o un mensaje de error
     */
    public function getRenglonesPago($post){
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
                v.idpedido,
                v.fecha,
                v.total,
                v.abonado
            from
                vpedidos v
            where
                v.idsucursal = '".$idsucursal."' and
                v.total > 0 and
                v.statuspago = 0 and
                v.status = 'A' and
                v.cliente = '".$cliente."'
            order by
                v.idpedido";
            $result = mysqli_query($this->con,$query);

            if(!$result || mysqli_num_rows($result)==0){
                throw new Exception("No se encontraron resultados");
            }

            $pedidos = mysqli_fetch_all($result,MYSQLI_ASSOC);

            $idspedidos = array_map(function($pedido){
                return (int)$pedido["idpedido"];
            },$pedidos);

            // Facturas vigentes de esos pedidos con su restante por cobrar
            $query = "
            select distinct
                pf.idpedido,
                f.idfactura,
                f.serie,
                f.folio,
                f.idmetodopago,
                f.uuid,
                f.total,
                round(f.saldo - ".$this->montoAsignadoPendiente("f.idfactura").",2) as restante
            from
                ".$this->relacionPedidoFactura()." pf
            join
                tfacturas f
            on
                f.idfactura = pf.idfactura
            where
                pf.idpedido in (".implode(",",$idspedidos).") and
                (f.status is null or f.status = 1)
            order by
                pf.idpedido,
                f.idfactura";
            $result = mysqli_query($this->con,$query);

            $facturas = array();
            foreach(($result) ? mysqli_fetch_all($result,MYSQLI_ASSOC) : array() as $factura){
                $facturas[$factura["idpedido"]][] = $factura;
            }

            $renglones = array();

            foreach($pedidos as $pedido){
                $idpedido = $pedido["idpedido"];
                $restantepedido = round(floatval($pedido["total"]) - floatval($pedido["abonado"]),2);

                if($restantepedido < 0.01){
                    continue;
                }

                $restantefacturas = 0;
                $renglonespedido = array();

                foreach((isset($facturas[$idpedido]) ? $facturas[$idpedido] : array()) as $factura){
                    $restante = round(floatval($factura["restante"]),2);

                    if($restante < 0.01){
                        continue;
                    }

                    $restantefacturas = round($restantefacturas + $restante,2);

                    // Solo las PPD vigentes con saldo obligan a timbrar complemento de pago
                    $ppd = ($factura["idmetodopago"] == 1 && !empty($factura["uuid"]));

                    $renglonespedido[] = array(
                        "idpedido" => $idpedido,
                        "idfactura" => $factura["idfactura"],
                        "documento" => trim($factura["serie"]."-".$factura["folio"]," -"),
                        "ppd" => ($ppd) ? 1 : 0,
                        "totaldocumento" => round(floatval($factura["total"]),2),
                        "restante" => $restante,
                        "fecha" => $pedido["fecha"],
                        "restantepedido" => $restantepedido
                    );
                }

                // Lo que el pedido debe por encima de lo que deben sus facturas es la parte que
                // todavía no está facturada
                $sinfacturar = round($restantepedido - $restantefacturas,2);

                if($sinfacturar >= 0.01){
                    $renglonespedido[] = array(
                        "idpedido" => $idpedido,
                        "idfactura" => 0,
                        "documento" => "Sin factura",
                        "ppd" => 0,
                        "totaldocumento" => $sinfacturar,
                        "restante" => $sinfacturar,
                        "fecha" => $pedido["fecha"],
                        "restantepedido" => $restantepedido
                    );
                }

                $renglones = array_merge($renglones,$renglonespedido);
            }

            if(empty($renglones)){
                throw new Exception("No se encontraron resultados");
            }

            $respuesta = array(
                "result" => "success",
                "renglones" => $renglones
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
     * Si alguno de los pedidos tiene facturas PPD vigentes con saldo, genera el complemento
     * de pago. Esa condición se calcula aquí, en el servidor: no se confía en ninguna bandera
     * enviada por el navegador.
     *
     * @param array $post Contiene los datos del pago:
     *  - string "cliente"      ID y nombre del cliente en formato "ID-NOMBRE"
     *  - string "idformapago"  ID de la forma de pago
     *  - string "fecha"        Fecha del pago (formato yyyy-mm-dd)
     *  - array  "pedidos"      Arreglo de renglones capturados, cada uno con:
     *      - string "idpedido"   ID del pedido
     *      - string "idfactura"  ID de la factura a la que se aplica el abono, 0 si se aplica
     *                            a la parte del pedido que aún no está facturada
     *      - float  "monto"      Monto del pago para ese documento
     * @return array Respuesta con:
     *  - bool   "success"              Indica si el pago se registró correctamente
     *  - string "message"              Mensaje descriptivo del resultado
     *  - array  "tickets"              Arreglo de tickets generados (idticket y copias)
     *  - bool   "complementopendiente" true si el pago requería complemento y no se pudo timbrar
     */
    public function agregarPago($post){
        try{
            $idcliente = mysqli_real_escape_string($this->con, strstr($post["cliente"], "-", true));
            $nombreCliente = mysqli_real_escape_string($this->con, substr(strstr($post["cliente"], "-"), 1));
            $idformapago = mysqli_real_escape_string($this->con,$post["idformapago"]);
            $fecha = mysqli_real_escape_string($this->con,$post["fecha"]);

            $idcliente_sql = ($idcliente == 0) ? "NULL" : "'".$idcliente."'";
            $cliente_sql = ($idcliente == 0) ? "'".$nombreCliente."'" : "NULL";

            // Cada renglón capturado es un documento por cobrar: una factura del pedido o
            // (idfactura = 0) la parte del pedido que todavía no se ha facturado
            $renglones = [];
            if(isset($post["pedidos"]) && is_array($post["pedidos"])){
                foreach($post["pedidos"] as $renglon){
                    $monto = round(floatval($renglon["monto"]),2);
                    if($monto > 0){
                        $renglones[] = array(
                            "idpedido" => (int)$renglon["idpedido"],
                            "idfactura" => (int)(isset($renglon["idfactura"]) ? $renglon["idfactura"] : 0),
                            "monto" => $monto
                        );
                    }
                }
            }

            if(empty($renglones)){
                throw new Exception("No se recibieron pedidos con monto mayor a 0");
            }

            // Se revalida contra la base lo que mandó el navegador: que los documentos sigan
            // siendo del cliente y que ningún monto rebase lo que ese documento debe
            $disponibles = $this->getRenglonesPago(array(
                "idvendedor" => $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"],
                "cliente" => $post["cliente"]
            ));

            if($disponibles["result"] != "success"){
                throw new Exception($disponibles["mensaje"]);
            }

            $restantes = array();
            $restantespedido = array();
            foreach($disponibles["renglones"] as $disponible){
                $restantes[$disponible["idpedido"]."-".$disponible["idfactura"]] = $disponible["restante"];
                $restantespedido[$disponible["idpedido"]] = $disponible["restantepedido"];
            }

            // Los efectos sobre el pedido (ticket, abonado, statuspago) siguen siendo uno por
            // pedido, aunque el abono se haya desglosado en varios documentos
            $pedidos = array();
            $capturadodocumento = array();
            $total = 0;

            foreach($renglones as $renglon){
                $idpedido = $renglon["idpedido"];
                $clave = $idpedido."-".$renglon["idfactura"];

                if(!isset($restantes[$clave])){
                    throw new Exception("El documento capturado del pedido ".$idpedido." ya no está disponible, vuelve a seleccionar el cliente");
                }

                $capturadodocumento[$clave] = round((isset($capturadodocumento[$clave]) ? $capturadodocumento[$clave] : 0) + $renglon["monto"],2);

                if($capturadodocumento[$clave] > $restantes[$clave] + 0.01){
                    throw new Exception("El monto capturado para el pedido ".$idpedido." rebasa el restante del documento ($".number_format($restantes[$clave],2).")");
                }

                if(!isset($pedidos[$idpedido])){
                    $pedidos[$idpedido] = array(
                        "idpedido" => $idpedido,
                        "monto" => 0,
                        "documentos" => array()
                    );
                }

                $pedidos[$idpedido]["monto"] = round($pedidos[$idpedido]["monto"] + $renglon["monto"],2);
                $pedidos[$idpedido]["documentos"][] = $renglon;

                // Red de seguridad: la suma de los documentos de un pedido no puede rebasar lo
                // que resta del pedido, aunque cada documento por separado cuadre
                if($pedidos[$idpedido]["monto"] > $restantespedido[$idpedido] + 0.01){
                    throw new Exception("El monto capturado para el pedido ".$idpedido." rebasa lo que resta del pedido ($".number_format($restantespedido[$idpedido],2).")");
                }

                $total = round($total + $renglon["monto"],2);
            }

            $pedidos = array_values($pedidos);
            $total = mysqli_real_escape_string($this->con,sprintf("%.2f",$total));

            // Iniciar transacción
            mysqli_begin_transaction($this->con);

            // Insertar registro en tpagos sin serie, folio, uuid ni timbrado
            $idusuario = $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"];
            $query = "
            insert
            into
                tpagos
            (
                idusuario,
                idcliente,
                cliente,
                total,
                idformapago,
                fecha,
                status
            ) values (
                '".$idusuario."',
                ".$idcliente_sql.",
                ".$cliente_sql.",
                '".$total."',
                '".$idformapago."',
                '".$fecha."',
                1
            )";

            if(!mysqli_query($this->con, $query)){
                throw new Exception("Error al insertar el registro de pago");
            }
            $idpago = mysqli_insert_id($this->con);

            // Obtener datos del vendedor
            $idvendedor = $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"];
            $query = "
            select
                *
            from
                tvendedores
            where
                idvendedor = '".$idvendedor."'";
            $vendedor = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if(empty($vendedor)){
                throw new Exception("No se pudo recuperar la información del vendedor");
            }

            // Obtener corte activo
            $query = "
            select
                *
            from
                tcortessucursales
            where
                idsucursal = '".$vendedor["idsucursal"]."' and
                status = 'A'";
            $corte = mysqli_fetch_assoc(mysqli_query($this->con,$query));

            if(empty($corte)){
                throw new Exception("No hay un corte activo para la sucursal");
            }

            // Procesar cada pedido
            $tickets = [];
            foreach($pedidos as $pedido){
                $idpedido = $pedido["idpedido"];
                $monto = $pedido["monto"];

                // Obtener folio actual
                $query = "
                select
                    *
                from
                    tsucursales
                where
                    idsucursal = '".$vendedor["idsucursal"]."'";
                $folio = mysqli_fetch_assoc(mysqli_query($this->con,$query))["folio"];

                // Insertar ticket
                $query = "
                insert
                into
                    ttickets
                (
                    idpedido,
                    idpago,
                    idsucursal,
                    idcorte,
                    idvendedor,
                    folio,
                    total,
                    fecha,
                    status,
                    notas
                ) values (
                    '".$idpedido."',
                    '".$idpago."',
                    '".$vendedor["idsucursal"]."',
                    '".$corte["idcorte"]."',
                    '".$vendedor["idvendedor"]."',
                    '".$folio."',
                    '".$monto."',
                    '".$fecha."',
                    'A',
                    ''
                )";
                if(!mysqli_query($this->con,$query)){
                    throw new Exception("Error al insertar ticket para el pedido ".$idpedido);
                }

                $idticket = mysqli_insert_id($this->con);

                // Incrementar folio
                $query = "
                update
                    tsucursales
                set
                    folio = folio + 1
                where
                    idsucursal = '".$vendedor["idsucursal"]."'";
                mysqli_query($this->con,$query);

                // Insertar en tformaspagoticket
                $query = "
                insert
                into
                    tformaspagoticket
                (
                    idticket,
                    idvendedor,
                    idformapago,
                    monto,
                    montorecibido
                ) values (
                    '".$idticket."',
                    '".$vendedor["idvendedor"]."',
                    '".$idformapago."',
                    '".$monto."',
                    '".$monto."'
                )";
                if(!mysqli_query($this->con,$query)){
                    throw new Exception("Error al registrar la forma de pago del ticket para el pedido ".$idpedido);
                }

                // Insertar en tformaspagopedido un renglón por documento cobrado. Aquí queda
                // guardada la asignación que capturó el vendedor, que es la que después usa el
                // complemento de pago para relacionar las facturas
                foreach($pedido["documentos"] as $documento){
                    $montodocumento = sprintf("%.2f",$documento["monto"]);
                    $idfactura_sql = ($documento["idfactura"] > 0) ? "'".$documento["idfactura"]."'" : "NULL";

                    $query = "
                    insert
                    into
                        tformaspagopedido
                    (
                        idpedido,
                        idfactura,
                        idpago,
                        idvendedor,
                        idformapago,
                        monto,
                        montorecibido,
                        fecha
                    ) values (
                        '".$idpedido."',
                        ".$idfactura_sql.",
                        '".$idpago."',
                        '".$vendedor["idvendedor"]."',
                        '".$idformapago."',
                        '".$montodocumento."',
                        '".$montodocumento."',
                        '".$fecha."'
                    )";
                    if(!mysqli_query($this->con,$query)){
                        throw new Exception("Error al registrar la forma de pago del pedido ".$idpedido);
                    }
                }

                // Actualizar abonado en tpedidos
                $query = "
                update
                    tpedidos
                set
                    abonado = abonado + ".$monto."
                where
                    idpedido = '".$idpedido."'";
                if(!mysqli_query($this->con,$query)){
                    throw new Exception("Error al actualizar el abonado del pedido ".$idpedido);
                }

                // Activar pedido si pendiente=0
                $query = "
                select
                    *
                from
                    tpedidos
                where
                    pendiente = 0 and
                    idpedido = '".$idpedido."'";
                if(mysqli_num_rows(mysqli_query($this->con,$query)) > 0){
                    $query = "
                    update
                        tpedidos
                    set
                        pendiente = 1
                    where
                        idpedido = '".$idpedido."'";
                    if(!mysqli_query($this->con,$query)){
                        throw new Exception("Error al activar el pedido ".$idpedido);
                    }
                }

                // Si total == abonado, marcar como pagado
                $copiasticket = 2;
                $query = "
                select
                    *
                from
                    tpedidos
                where
                    idpedido = '".$idpedido."' and
                    total = abonado";
                if(mysqli_num_rows(mysqli_query($this->con,$query)) > 0){
                    $query = "
                    update
                        tpedidos
                    set
                        statuspago = 1
                    where
                        idpedido = '".$idpedido."'";
                    if(!mysqli_query($this->con,$query)){
                        throw new Exception("Error al marcar como pagado el pedido ".$idpedido);
                    }
                    $copiasticket = 1;
                }

                // Insertar en tticketspedidos
                $query = "
                insert
                into
                    tticketspedidos
                (
                    idvendedor,
                    idpedido,
                    idpago,
                    total,
                    fecha,
                    status
                ) values (
                    '".$vendedor["idvendedor"]."',
                    '".$idpedido."',
                    '".$idpago."',
                    '".$monto."',
                    '".$fecha."',
                    'A'
                )";
                if(!mysqli_query($this->con,$query)){
                    throw new Exception("Error al registrar el ticket del pedido ".$idpedido);
                }

                $tickets[] = array(
                    "idticket" => $idticket,
                    "copias" => $copiasticket
                );
            }

            // Confirmar transacción
            mysqli_commit($this->con);

            // Se revisa contra la base (no contra lo que mandó el navegador) si el pago recae
            // sobre facturas PPD vigentes con saldo: esas son las que exigen complemento
            $complemento = $this->pagoRequiereComplemento($idpago);

            if($complemento){
                // Mandamos llamar la función para generar complemento de pago
                $resultadoComplemento = $this->generarComplementoPago(array(
                    "idpago" => $idpago
                ));
            }

            $mensaje = "Pago registrado correctamente";
            $complementopendiente = false;

            if($complemento && isset($resultadoComplemento)){
                $mensaje .= ". " . $resultadoComplemento["message"];
                $complementopendiente = !$resultadoComplemento["success"];
            }

            $respuesta = array(
                "success" => true,
                "message" => $mensaje,
                "tickets" => $tickets,
                "complementopendiente" => $complementopendiente
            );

        }catch(Exception $e){
            // Revertir todos los cambios si algo falló
            mysqli_rollback($this->con);

            $respuesta = array(
                "success" => false,
                "message" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    /**
     * Obtiene una factura PPD vigente y con saldo pendiente por su ID, con los datos fiscales
     * que necesita el complemento de pago. Devuelve null si la factura no aplica para
     * complemento (no es PPD, está cancelada, no está timbrada o ya no tiene saldo).
     *
     * @param int $idfactura
     * @return array|null
     */
    private function getFacturaPPD($idfactura){
        $idfactura = mysqli_real_escape_string($this->con,$idfactura);

        $query = "
        select
            f.idfactura,
            f.serie,
            f.folio,
            f.uuid,
            f.saldo,
            f.idemisor,
            f.idrazonsocial,
            f.razonsocial,
            f.rfc,
            f.codigo_postal,
            f.regimenfiscal,
            round((f.iva/f.subtotal)*100,0) as impuesto
        from
            tfacturas f
        where
            f.idfactura = '".$idfactura."' and
            f.idmetodopago = 1 and
            (f.status is null or f.status = 1) and
            f.uuid is not null and
            f.uuid <> '' and
            f.saldo > 0";
        $result = mysqli_query($this->con,$query);

        return ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;
    }

    /**
     * Número de parcialidad que le toca a un complemento sobre una factura: cuántos
     * complementos vigentes la han amortizado antes, más uno. Se cuenta por factura, no por
     * pedido, porque así lo pide el CFDI de pagos.
     *
     * @param int $idfactura
     * @param int $idpago       Pago que se está timbrando, se excluye del conteo
     * @return int
     */
    private function parcialidadFactura($idfactura,$idpago){
        $idfactura = mysqli_real_escape_string($this->con,$idfactura);
        $idpago = mysqli_real_escape_string($this->con,$idpago);

        $query = "
        select
            count(*) + 1 as parcialidad
        from
            tpagosfacturas a
        join
            tpagos b
        on
            b.idpago = a.idpago
        where
            a.idfactura = '".$idfactura."' and
            a.idpago <> '".$idpago."' and
            b.status <> 3";

        return mysqli_fetch_assoc(mysqli_query($this->con, $query))["parcialidad"];
    }

    /**
     * Resuelve qué facturas amortiza un pago y con cuánto cada una, junto con la tienda del
     * pedido (para el logo del correo).
     *
     * La asignación la captura el vendedor documento por documento al registrar el pago y vive
     * en tformaspagopedido.idfactura, así que aquí solo se lee: el sistema ya no decide a qué
     * factura va el dinero. Los pagos registrados antes del desglose por documento no traen
     * esa asignación; para esos, y solo para esos, se conserva el reparto automático de la
     * factura más antigua a la más reciente.
     *
     * @param int $idpago
     * @return array array("facturas" => array, "idtienda" => int)
     */
    private function facturasComplemento($idpago){
        $idpago = mysqli_real_escape_string($this->con,$idpago);

        $query = "
        select
            a.idpedido,
            a.idfactura,
            sum(a.monto) as monto
        from
            tformaspagopedido a
        where
            a.idpago = '".$idpago."'
        group by
            a.idpedido,
            a.idfactura
        order by
            a.idpedido,
            a.idfactura";
        $result = mysqli_query($this->con,$query);

        if(!$result || mysqli_num_rows($result)==0){
            throw new Exception("No se pudo recuperar la información de los pagos");
        }

        $aplicaciones = mysqli_fetch_all($result,MYSQLI_ASSOC);

        $query = "
        select
            idtienda
        from
            vpedidos
        where
            idpedido = '".$aplicaciones[0]["idpedido"]."'";
        $idtienda = mysqli_fetch_assoc(mysqli_query($this->con, $query))["idtienda"];

        $asignadas = array_filter($aplicaciones,function($aplicacion){
            return $aplicacion["idfactura"] > 0;
        });

        $facturas = (!empty($asignadas))
            ? $this->facturasAsignadas($idpago,$asignadas)
            : $this->facturasReparto($idpago,$aplicaciones);

        return array(
            "facturas" => $facturas,
            "idtienda" => $idtienda
        );
    }

    /**
     * Arma los documentos relacionados del complemento a partir de la asignación que capturó
     * el vendedor. Los renglones que no correspondan a una factura PPD vigente con saldo (los
     * abonos a la parte no facturada del pedido o a facturas PUE) simplemente no se relacionan.
     *
     * @param int   $idpago
     * @param array $asignadas   Renglones de tformaspagopedido con idfactura
     * @return array
     */
    private function facturasAsignadas($idpago,$asignadas){
        $facturas = array();

        foreach($asignadas as $aplicacion){
            $idfactura = $aplicacion["idfactura"];
            $monto = round(floatval($aplicacion["monto"]), 2);

            if($monto < 0.01){
                continue;
            }

            // Si dos pedidos del mismo pago abonaron a la misma factura, se acumulan en un
            // solo documento relacionado
            if(isset($facturas[$idfactura])){
                $facturas[$idfactura]["monto"] = round($facturas[$idfactura]["monto"] + $monto, 2);
                continue;
            }

            $factura = $this->getFacturaPPD($idfactura);

            if($factura === null){
                continue;
            }

            $facturas[$idfactura] = array(
                "idfactura" => $idfactura,
                "monto" => $monto,
                "saldo" => round(floatval($factura["saldo"]), 2),
                "uuid" => $factura["uuid"],
                "serie" => $factura["serie"],
                "folio" => $factura["folio"],
                "idemisor" => $factura["idemisor"],
                "idrazonsocial" => $factura["idrazonsocial"],
                "razonsocial" => $factura["razonsocial"],
                "rfc" => $factura["rfc"],
                "codigo_postal" => $factura["codigo_postal"],
                "regimenfiscal" => $factura["regimenfiscal"],
                "parcialidad" => $this->parcialidadFactura($idfactura,$idpago),
                "impuesto" => $factura["impuesto"]
            );
        }

        // Nunca se puede amortizar más de lo que la factura debe, aunque la captura lo diga
        foreach($facturas as $idfactura => $factura){
            if($factura["monto"] > $factura["saldo"]){
                $facturas[$idfactura]["monto"] = $factura["saldo"];
            }
        }

        return array_values($facturas);
    }

    /**
     * Reparto automático del abono entre las facturas PPD del pedido, de la más antigua a la
     * más reciente. Solo se usa con los pagos anteriores al desglose por documento, que no
     * guardaron a qué factura iba cada peso.
     *
     * @param int   $idpago
     * @param array $aplicaciones   Renglones de tformaspagopedido agrupados por pedido
     * @return array
     */
    private function facturasReparto($idpago,$aplicaciones){
        $facturas = array();
        $saldosdisponibles = array();
        $montospedido = array();

        foreach($aplicaciones as $aplicacion){
            $idpedido = $aplicacion["idpedido"];
            $montospedido[$idpedido] = round((isset($montospedido[$idpedido]) ? $montospedido[$idpedido] : 0) + floatval($aplicacion["monto"]), 2);
        }

        foreach($montospedido as $idpedido => $porAplicar){
            foreach($this->getFacturasPPDPedido($idpedido) as $factura){
                if($porAplicar < 0.01){
                    break;
                }

                $idfactura = $factura["idfactura"];
                $saldo = round(floatval($factura["saldo"]), 2);

                // El saldo disponible se lleva en memoria para el caso (raro) de que dos
                // pedidos del mismo pago compartan factura: así no se relaciona más de lo
                // que la factura debe
                $disponible = isset($saldosdisponibles[$idfactura]) ? $saldosdisponibles[$idfactura] : $saldo;
                $aplicado = round(min($porAplicar, $disponible), 2);

                if($aplicado < 0.01){
                    continue;
                }

                $porAplicar = round($porAplicar - $aplicado, 2);
                $saldosdisponibles[$idfactura] = round($disponible - $aplicado, 2);

                if(isset($facturas[$idfactura])){
                    $facturas[$idfactura]["monto"] = round($facturas[$idfactura]["monto"] + $aplicado, 2);
                    continue;
                }

                $facturas[$idfactura] = array(
                    "idfactura" => $idfactura,
                    "monto" => $aplicado,
                    "saldo" => $saldo,
                    "uuid" => $factura["uuid"],
                    "serie" => $factura["serie"],
                    "folio" => $factura["folio"],
                    "idemisor" => $factura["idemisor"],
                    "idrazonsocial" => $factura["idrazonsocial"],
                    "razonsocial" => $factura["razonsocial"],
                    "rfc" => $factura["rfc"],
                    "codigo_postal" => $factura["codigo_postal"],
                    "regimenfiscal" => $factura["regimenfiscal"],
                    "parcialidad" => $this->parcialidadFactura($idfactura,$idpago),
                    "impuesto" => $factura["impuesto"]
                );
            }
        }

        return array_values($facturas);
    }

    public function generarComplementoPago($post){
        try{
            $idpago = mysqli_real_escape_string($this->con, $post["idpago"]);

            // Recuperamos la información principal del pago
            $query = "
            select
                idcliente,
                idformapago,
                fecha,
                total,
                uuid,
                status
            from
                tpagos
            where
                idpago = '".$idpago."'";
            $result = mysqli_query($this->con,$query);

            if(mysqli_num_rows($result)==0){
                throw new Exception("No se pudo recuperar la información del pago");
            }

            $pago = mysqli_fetch_assoc($result);

            // Un pago timbrado no se puede volver a timbrar: se duplicaría el CFDI y se
            // descontaría dos veces el saldo de las facturas
            if(!empty($pago["uuid"])){
                throw new Exception("Este pago ya tiene un complemento timbrado");
            }

            if($pago["status"] != 1){
                throw new Exception("Solo se puede timbrar el complemento de un pago activo");
            }

            $idcliente = $pago["idcliente"];
            $idformapago = $pago["idformapago"];
            $fecha = substr($pago["fecha"], 0, 10);
            $totalpago = floatval($pago["total"]);

            // La asignación factura por factura la capturó el vendedor al registrar el pago;
            // aquí solo se lee para armar los documentos relacionados del complemento
            $resolucion = $this->facturasComplemento($idpago);
            $facturas = $resolucion["facturas"];
            $idtienda = $resolucion["idtienda"];

            if(empty($facturas)){
                throw new Exception("El pago no corresponde a facturas PPD con saldo pendiente, por lo que no requiere complemento");
            }

            // Obtener idemisor e idrazonsocial de la primera factura
            $idemisor = $facturas[0]["idemisor"];
            $idrazonsocial = $facturas[0]["idrazonsocial"];

            // Las facturas de pedidos sin cliente no tienen razón social relacionada: sus datos
            // fiscales viven en texto dentro de tfacturas
            $sinrazonsocial = !($idrazonsocial > 0);

            // Un CFDI de pago lleva un solo emisor y un solo receptor, así que no se puede
            // timbrar un pago que amortice facturas de emisores o razones sociales distintas.
            // Sin idrazonsocial se compara el RFC en texto, porque de otro modo dos facturas
            // con receptores distintos pasarían la validación (ambas con idrazonsocial vacío)
            foreach($facturas as $factura){
                $mismoreceptor = ($sinrazonsocial)
                    ? strcasecmp(trim($factura["rfc"]), trim($facturas[0]["rfc"])) == 0 && !($factura["idrazonsocial"] > 0)
                    : $factura["idrazonsocial"] == $idrazonsocial;

                if($factura["idemisor"] != $idemisor || !$mismoreceptor){
                    throw new Exception("El pago amortiza facturas de distinto emisor o razón social; hay que registrarlo por separado");
                }
            }

            // Obtener datos del emisor
            $query = "
            select
                *
            from
                temisores
            where
                idemisor = '".$idemisor."'";
            $infoEmisor = mysqli_fetch_assoc(mysqli_query($this->con, $query));

            // Obtener datos de la razón social del cliente. Si la factura no tiene razón social
            // relacionada (pedido sin cliente) se usan los datos fiscales en texto que quedaron
            // guardados en tfacturas al timbrarla
            if($sinrazonsocial){
                $razonsocial = array(
                    "rfc" => $facturas[0]["rfc"],
                    "razon_social" => $facturas[0]["razonsocial"],
                    "codigo_postal" => $facturas[0]["codigo_postal"],
                    "regimenfiscal" => $facturas[0]["regimenfiscal"]
                );
            }else{
                $query = "
                select
                    *
                from
                    tclienterazonessociales
                where
                    idrazonsocial = '".$idrazonsocial."'";
                $razonsocial = mysqli_fetch_assoc(mysqli_query($this->con, $query));
            }

            if(empty($razonsocial["rfc"]) || empty($razonsocial["razon_social"])){
                throw new Exception("La factura no tiene datos fiscales del receptor; no se puede timbrar el complemento");
            }

            // Calcular total del pago
            $total = array_sum(array_column($facturas, "monto"));

            // Actualizar registro en tpagos con emisor y razon social. Sin razón social
            // relacionada la columna se deja nula en vez de guardar un 0 que no existe
            $query = "
            update
                tpagos
            set
                idemisor = '".$idemisor."',
                idrazonsocial = ".(($sinrazonsocial) ? "NULL" : "'".$idrazonsocial."'")."
            where
                idpago = '".$idpago."'";

            if(!mysqli_query($this->con, $query)){
                throw new Exception("Error al actualizar el registro de pago");
            }

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
                "Nombre" => utf8_decode(trim($infoEmisor["razon_social"])),
                "RegimenFiscal" => $regimen_fiscal,
                "LugarExpedicion" => $infoEmisor["codigo_postal"]
            );

            $receptor = array(
                "Rfc" => $razonsocial["rfc"],
                "Nombre" => utf8_decode(trim($razonsocial["razon_social"])),
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
                "monto" => sprintf("%.2f", $total),
                "tipocambio" => 1
            );

            // Construir documentos relacionados
            $pagos = array();
            foreach($facturas as $fac){
                $pagos[] = array(
                    "Folio" => $fac["folio"],
                    "IdDocumento" => $fac["uuid"],
                    "ImpPagado" => sprintf("%.2f", $fac["monto"]),
                    "ImpSaldoAnt" => sprintf("%.2f", $fac["saldo"]),
                    "ImpSaldoInsoluto" => sprintf("%.2f", $fac["saldo"] - $fac["monto"]),
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
                "pruebas" => 0,
                "numero_certificado" => $numero_certificado,
                "certificado" => $certificado,
                "keypem" => $archivo_keypem,
                "colortxt" => "000000",
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

            if($response === false){
                throw new Exception("Error de conexión con el timbrador: " . curl_error($curl));
            }

            $response = json_decode($response, true);

            if($response === null){
                throw new Exception("Respuesta inválida del timbrador");
            }

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

                // Actualizar saldo en tfacturas por cada pago parcial y dejar registrado qué
                // factura amortizó el complemento y con cuánto: de ahí salen la parcialidad de
                // los complementos siguientes y la reversión del saldo si se cancela el pago
                $ok3 = true;
                foreach($facturas as $fac){
                    $monto = sprintf("%.2f", $fac["monto"]);

                    $query = "
                    update
                        tfacturas
                    set
                        saldo = saldo - ".$monto."
                    where
                        idfactura = '".$fac["idfactura"]."'";
                    if(!mysqli_query($this->con, $query)){
                        $ok3 = false;
                        break;
                    }

                    $query = "
                    insert
                    into
                        tpagosfacturas
                    (
                        idpago,
                        idfactura,
                        monto,
                        parcialidad
                    ) values (
                        '".$idpago."',
                        '".$fac["idfactura"]."',
                        '".$monto."',
                        '".$fac["parcialidad"]."'
                    )";
                    if(!mysqli_query($this->con, $query)){
                        $ok3 = false;
                        break;
                    }
                }

                if($ok1 && $ok2 && $ok3){
                    mysqli_commit($this->con);

                    // Enviar complemento por correo
                    $mensajeCorreo = "Complemento de pago timbrado correctamente";

                    // El complemento solo puede relacionar la parte facturada del abono; si
                    // sobró monto (pedido facturado parcialmente) hay que decirlo
                    if(round($totalpago - $total, 2) > 0){
                        $mensajeCorreo .= ". Se relacionaron $".number_format($total,2)." de los $".number_format($totalpago,2)." del pago, el resto se aplicó a documentos que no llevan complemento (facturas PUE o la parte del pedido que aún no está facturada)";
                    }

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

                        $logo = $ruta_server."/imagenes/tiendas/".$idtienda."_logo.png";
                        $logo = "data:image/png;base64,".((file_exists($logo)) ? base64_encode(file_get_contents($logo)) : base64_encode(file_get_contents($ruta_server."/assets/images/logo-uniformes-trazo.png")));

                        include($ruta_server."/assets/plantillas/correo/envioComplemento.php");
                        include($ruta_server."/assets/plantillas/correo/base.php");

                        $respuesta = $claseCorreos->enviarCorreo(array(
                            "idtienda" => $idtienda,
                            "asunto" => "Envío de complemento de pago",
                            "mensaje" => $cuerpo,
                            "correos" => array(
                                $correoCliente
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
                "success" => false,
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

    public function getPago($post){
        try{
            $idpago = mysqli_real_escape_string($this->con,$post["idpago"]);

            $query = "
            select
                a.idpago,
                a.idcliente,
                coalesce(b.nombre, a.cliente) as cliente,
                a.idrazonsocial,
                -- Los complementos de facturas sin cliente no tienen razón social relacionada:
                -- el receptor se recupera de los datos en texto de las facturas que amortizó
                case when a.idrazonsocial > 0 then c.rfc else (
                    select
                        f.rfc
                    from
                        tpagosfacturas pf
                    join
                        tfacturas f
                    on
                        f.idfactura = pf.idfactura
                    where
                        pf.idpago = a.idpago and
                        f.rfc is not null and
                        f.rfc <> ''
                    limit 1
                ) end as cliente_rfc,
                a.idemisor,
                d.rfc as emisor_rfc,
                a.total,
                a.uuid,
                a.status,
                a.fecha,
                a.serie,
                a.folio
            from
                tpagos a
            left join
                tclientes b
            on
                b.idcliente = a.idcliente
            left join
                tclienterazonessociales c
            on
                c.idrazonsocial = a.idrazonsocial
            left join
                temisores d
            on
                d.idemisor = a.idemisor
            where
                a.idpago = '".$idpago."'";
            $result = mysqli_query($this->con,$query);

            if(mysqli_num_rows($result)==0){
                throw new Exception("No se pudo recuperar la información del pago");
            }

            $respuesta = array(
                "result" => "success",
                "pago" => mysqli_fetch_assoc($result)
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

    public function cancelarPago($post){
        try{
            $idpago = mysqli_real_escape_string($this->con,$post["idpago"]);

            $pago = $this->getPago(array(
                "idpago" => $idpago
            ));

            if($pago["result"]!="success"){
                throw new Exception($pago["mensaje"]);
            }

            $pago = $pago["pago"];

            if($pago["status"] == 2){
                throw new Exception("La cancelación del complemento sigue pendiente de aceptación ante el SAT. Verifica su estatus antes de cancelar el pago.");
            }

            if($pago["status"] == 3){
                throw new Exception("Este pago ya se encuentra cancelado.");
            }

            if(empty($pago["uuid"]) || $pago["status"] == 4){
                // Sin CFDI que cancelar ante el SAT: o el pago nunca se timbró, o su
                // complemento ya se canceló y solo falta cerrar el pago. En ambos casos se
                // revierte su efecto sobre el pedido/factura y se marca cancelado.
                // Se recuperan antes de la transacción porque la reversión los borra: de aquí
                // sale el aviso de qué se eliminó de caja, o de que no había nada que eliminar
                $tickets = $this->getTicketsPago($idpago);

                mysqli_begin_transaction($this->con);

                $query = "
                update
                    tpagos
                set
                    status = 3
                where
                    idpago = '".$idpago."'";
                $ok = mysqli_query($this->con,$query);
                $ok = $ok && $this->revertirEfectoPago($idpago);

                if($ok){
                    mysqli_commit($this->con);
                    $respuesta = array(
                        "success" => true,
                        "message" => $this->mensajeTicketsCancelados($tickets)
                    );
                }else{
                    mysqli_rollback($this->con);
                    throw new Exception("No se pudo cancelar el pago.");
                }
            }else{
                // El SAT exige el RFC del receptor para cancelar; sin él la petición se rechaza
                if(empty($pago["cliente_rfc"])){
                    throw new Exception("No se pudo determinar el RFC del receptor del complemento; contacta a soporte.");
                }

                $idmotivocancelacion = mysqli_real_escape_string($this->con,$post["slcMotivoCancelacion"]);
                $uuidsustitucion = mysqli_real_escape_string($this->con,$post["txtUUID"]);

                $query = "
                select
                    *
                from
                    sat_tcatmotivoscancelacion
                where
                    idmotivo = '".$idmotivocancelacion."'";
                $motivo_cancelacion = mysqli_fetch_assoc(mysqli_query($this->con,$query));

                if($motivo_cancelacion["requiere_uuid"]==1 && !$this->esUUIDValido($uuidsustitucion)){
                    throw new Exception("El formato del UUID de sustitución no es válido");
                }

                $ruta_server = $_SERVER["DOCUMENT_ROOT"]."/../1.uniformescisne.mx";

                $cerpath = $ruta_server."/emisores/".$pago["emisor_rfc"]."/sat/certificado.cer";
                $cerpem = $cerpath.".pem";
                exec("openssl x509 -in $cerpath -inform DER -out $cerpem");

                $keypem = $ruta_server."/emisores/".$pago["emisor_rfc"]."/sat/llave.key.pem";

                $pfx = $ruta_server."/emisores/".$pago["emisor_rfc"]."/sat/pfx.pfx";
                $pwdPfx = uniqid();

                if($this->generarPfx($keypem,$cerpem,$pfx,$pwdPfx)){
                    // Se manda a cancelar el complemento de pago al SAT
                    $datos = array(
                        "api_key" => "tek_npzimyh2ajjxpj3p3j2ofozt7c6deej9uu",
                        "pruebas" => 0,
                        "tipoComprobante" => "C2",
                        "pfx" => base64_encode(file_get_contents($pfx)),
                        "pfx_pwd" => $pwdPfx,
                        "uuid" => $pago["uuid"],
                        "rfc_emisor" => $pago["emisor_rfc"],
                        "rfc_receptor" => $pago["cliente_rfc"],
                        "total" => $pago["total"],
                        "cve_motivo_cancelacion" => $motivo_cancelacion["clave"],
                        "uuid_sustitucion" => $uuidsustitucion
                    );

                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://api.xptk.app/timbrador/index.php',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => http_build_query($datos),
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/x-www-form-urlencoded'
                        )
                    ));

                    $response = curl_exec($curl);
                    $response = json_decode($response,true);
                    curl_close($curl);

                    if($response["status"]=="success"){
                        // statusCFDI 201 = "cancelado con aceptación pendiente": el receptor tiene
                        // 3 días para aceptar/rechazar ante el SAT, así que el complemento queda
                        // en status 2 hasta que verificarEstatusSAT o el cronjob lo resuelvan.
                        //
                        // Cuando la cancelación sí es definitiva el pago queda en status 4:
                        // el complemento ya no existe ante el SAT, pero revertir el efecto del
                        // pago sobre pedidos y saldos es una decisión del usuario, que la toma
                        // con "Cancelar pago". Mismo criterio que la intranet, que comparte
                        // esta tabla.
                        $esDefinitiva = ($response["statusCFDI"] != "201");

                        $query = "
                        update
                            tpagos
                        set
                            status = '".($esDefinitiva ? "4" : "2")."'
                        where
                            idpago = '".$idpago."'";

                        if(mysqli_query($this->con,$query)){
                            $respuesta = array(
                                "success" => true,
                                "message" => $esDefinitiva
                                    ? "El complemento de pago se ha cancelado correctamente. Ahora puedes cancelar el pago."
                                    : "La cancelación se envió al SAT y quedó pendiente de aceptación por el receptor. Podrás cancelar el pago hasta que se confirme la cancelación definitiva."
                            );
                        }else{
                            throw new Exception("El CFDI se canceló ante el SAT, pero no se pudo actualizar el registro del pago. Contacta a soporte para corregir el registro manualmente.");
                        }
                    }else{
                        throw new Exception($response["text"]);
                    }
                }else{
                    throw new Exception("Ocurrió un error en la generación de los archivos para cancelación");
                }
            }
        }catch(Exception $e){
            $respuesta = array(
                "success" => false,
                "message" => $e->getMessage()
            );
        }finally{
            return $respuesta;
        }
    }

    /**
     * Reconsulta ante el SAT un complemento de pago que quedó en proceso de cancelación y
     * aplica el desenlace: lo marca como cancelado (status 4, dejando el pago vivo para que
     * el usuario decida cancelarlo), lo regresa a activo si el receptor rechazó la solicitud,
     * o lo deja igual si la solicitud sigue viva.
     *
     * El barrido masivo de documentos en proceso de cancelación vive en la intranet
     * (cronjobs/verificarStatusCanceladas.php), que trabaja sobre esta misma base; aquí solo
     * se expone la consulta individual desde el listado.
     *
     * @access public
     * @param array $post   Debe contener idpago
     * @return array
     */
    public function verificarEstatusSAT($post){
        try{
            $idpago = mysqli_real_escape_string($this->con,$post["idpago"]);

            $pago = $this->getPago(array(
                "idpago" => $idpago
            ));

            if($pago["result"] != "success"){
                throw new Exception($pago["mensaje"]);
            }

            $pago = $pago["pago"];

            if($pago["status"] != 2){
                throw new Exception("El complemento de este pago no se encuentra en proceso de cancelación.");
            }

            if(empty($pago["uuid"])){
                throw new Exception("Este pago no tiene un complemento timbrado; no hay nada que consultar ante el SAT.");
            }

            $ruta_server = $_SERVER["DOCUMENT_ROOT"]."/../1.uniformescisne.mx";

            $consulta = $this->claseSAT->consultarEstatusCFDI(
                $this->claseSAT->rutaXMLComprobante($ruta_server,$pago["emisor_rfc"],"pagos",$pago["uuid"]),
                $pago["emisor_rfc"],
                $pago["cliente_rfc"]
            );

            if($consulta["respuesta"] != "OK"){
                throw new Exception($consulta["mensaje"]);
            }

            $mensaje = $consulta["mensaje"];

            if($consulta["resultado"] == "cancelado" || $consulta["resultado"] == "activo"){
                $nuevostatus = ($consulta["resultado"] == "cancelado") ? "4" : "1";

                $query = "
                update
                    tpagos
                set
                    status = '".$nuevostatus."'
                where
                    idpago = '".$idpago."'";

                if(!mysqli_query($this->con,$query)){
                    throw new Exception("El SAT resolvió la solicitud de cancelación, pero no se pudo actualizar el registro del pago. Contacta a soporte.");
                }

                if($consulta["resultado"] == "cancelado"){
                    $mensaje .= " Ahora puedes cancelar el pago.";
                }
            }

            $respuesta = array(
                "success" => true,
                "message" => $mensaje,
                "resultado" => $consulta["resultado"],
                "estado" => $consulta["estado"],
                "estatus_cancelacion" => $consulta["estatus_cancelacion"]
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

    // Revierte el efecto de un pago sobre los pedidos que cubrió (abonado/statuspago) y le
    // regresa a cada factura el saldo que su complemento amortizó, según lo registrado en
    // tpagosfacturas. Se usa tanto para pagos timbrados (tras cancelar el CFDI) como para
    // pagos que nunca llegaron a timbrarse; en ese segundo caso no hay saldo que devolver,
    // porque el saldo de la factura solo se descuenta al timbrar el complemento.
    private function revertirEfectoPago($idpago){
        $query = "
        select
            a.idpedido,
            sum(a.monto) as monto,
            b.total,
            b.abonado
        from
            tformaspagopedido a
        join
            tpedidos b
        on
            b.idpedido = a.idpedido
        where
            a.idpago = '".$idpago."'
        group by
            a.idpedido,
            b.total,
            b.abonado";
        $aplicaciones = mysqli_fetch_all(mysqli_query($this->con,$query),MYSQLI_ASSOC);

        $ok = true;
        foreach($aplicaciones as $aplicacion){
            $idpedido = (int)$aplicacion["idpedido"];
            $monto = sprintf("%.2f", floatval($aplicacion["monto"]));
            $nuevoabonado = floatval($aplicacion["abonado"]) - floatval($aplicacion["monto"]);
            $statuspago = ($nuevoabonado >= floatval($aplicacion["total"])) ? 1 : 0;

            $query = "
            update
                tpedidos
            set
                abonado = abonado - ".$monto.",
                statuspago = ".$statuspago."
            where
                idpedido = '".$idpedido."'";
            $ok = $ok && mysqli_query($this->con,$query);
        }

        $query = "
        select
            idfactura,
            monto
        from
            tpagosfacturas
        where
            idpago = '".$idpago."'";
        $amortizaciones = mysqli_fetch_all(mysqli_query($this->con,$query),MYSQLI_ASSOC);

        foreach($amortizaciones as $amortizacion){
            $query = "
            update
                tfacturas
            set
                saldo = saldo + ".sprintf("%.2f", floatval($amortizacion["monto"]))."
            where
                idfactura = '".(int)$amortizacion["idfactura"]."'";
            $ok = $ok && mysqli_query($this->con,$query);
        }

        $query = "
        delete
        from
            tpagosfacturas
        where
            idpago = '".$idpago."'";
        $ok = $ok && mysqli_query($this->con,$query);

        $query = "
        delete
        from
            tformaspagopedido
        where
            idpago = '".$idpago."'";
        $ok = $ok && mysqli_query($this->con,$query);

        $ok = $ok && $this->borrarTicketsPago($idpago);

        return $ok;
    }

    /**
     * Tickets de caja que generó un pago: uno por cada pedido cobrado. Se usan para avisar en
     * pantalla qué se va a eliminar antes de cancelar, y para armar el mensaje de resultado.
     *
     * @param int $idpago
     * @return array Tickets con su corte y sucursal (vacío si el pago no tiene tickets ligados)
     */
    public function getTicketsPago($idpago){
        $idpago = (int)$idpago;

        $query = "
        select
            a.idticket,
            a.idpedido,
            a.folio,
            a.total,
            a.fecha,
            a.idcorte,
            b.nombre as sucursal,
            c.status as statuscorte
        from
            ttickets a
        left join
            tsucursales b
        on
            b.idsucursal = a.idsucursal
        left join
            tcortessucursales c
        on
            c.idcorte = a.idcorte
        where
            a.idpago = '".$idpago."'
        order by
            a.idticket";

        return mysqli_fetch_all(mysqli_query($this->con,$query),MYSQLI_ASSOC);
    }

    /**
     * Borra el rastro del pago en caja: el ticket de cada pedido cobrado, sus formas de pago y
     * el renglón de tticketspedidos. Al desaparecer el ticket deja de sumar en el corte y en
     * los reportes de ventas; los cortes ya cerrados conservan los totales que se congelaron al
     * cerrarlos (tcortessucursales.ventas y tcortesucursal_formaspago), así que solo cambian
     * los reportes que se recalculan al vuelo desde ttickets.
     *
     * Los pagos anteriores a la migración que el backfill no pudo ligar no tienen tickets
     * asociados: en ese caso no hay nada que borrar y la cancelación continúa avisándolo.
     *
     * @param int $idpago
     * @return bool
     */
    private function borrarTicketsPago($idpago){
        $idpago = (int)$idpago;

        $query = "
        select
            idticket
        from
            ttickets
        where
            idpago = '".$idpago."'";
        $tickets = mysqli_fetch_all(mysqli_query($this->con,$query),MYSQLI_ASSOC);

        $ok = true;

        if(!empty($tickets)){
            $idtickets = array();
            foreach($tickets as $ticket){
                $idtickets[] = (int)$ticket["idticket"];
            }

            $query = "
            delete
            from
                tformaspagoticket
            where
                idticket in (".implode(",",$idtickets).")";
            $ok = $ok && mysqli_query($this->con,$query);

            $query = "
            delete
            from
                ttickets
            where
                idpago = '".$idpago."'";
            $ok = $ok && mysqli_query($this->con,$query);
        }

        $query = "
        delete
        from
            tticketspedidos
        where
            idpago = '".$idpago."'";
        $ok = $ok && mysqli_query($this->con,$query);

        return $ok;
    }

    /**
     * Mensaje de resultado de la cancelación. Cuando no había tickets ligados se avisa
     * explícitamente para que nadie asuma que la caja quedó ajustada.
     *
     * @param array $tickets Tickets recuperados con getTicketsPago() antes de la reversión
     * @return string
     */
    private function mensajeTicketsCancelados($tickets){
        if(empty($tickets)){
            return "El pago se ha cancelado correctamente. No se encontró el ticket de caja asociado; verifícalo manualmente.";
        }

        $folios = array();
        foreach($tickets as $ticket){
            $folios[] = "#".$ticket["folio"];
        }

        return "El pago se ha cancelado correctamente. Se eliminó de caja ".((count($folios) == 1) ? "el ticket " : "los tickets ").implode(", ",$folios).".";
    }

    private function esUUIDValido($uuid){
        return preg_match(
            '/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/i',
            $uuid
        ) === 1;
    }

    private function generarPfx($keypem, $cerpem, $pfx, $pwd){
        // -legacy fuerza algoritmos RC2/3DES compatibles con librerías antiguas como Chilkat 9.5.x
        exec("openssl pkcs12 -export -legacy -inkey $keypem -in $cerpem -passout pass:'$pwd' -out $pfx");
        return file_exists($pfx) && filesize($pfx) > 0;
    }

}
?>
