<?
class Pedidos{
    private $con;

    function __construct($conexion) {
        $this->con = $conexion;
    }

    /*
    Devuelve los datos de un pedido.

    @access public
    @param int $idpedido
    @return array
    */
    public function infoPedido($idpedido){
        $query = "select
        *
        from
        vpedidos
        where
        idpedido = '".$idpedido."'";
        $pedido = mysqli_fetch_assoc(mysqli_query($this->con,$query));
        if($pedido["idpedido"]>0){
            return array("respuesta"=>"OK",
                        "idpedido"=>$pedido["idpedido"],
                        "idcotizacion"=>$pedido["idcotizacion"],
                        "idusuario"=>$pedido["idusuario"],
                        "usuario"=>$pedido["usuario"],
                        "idcliente"=>$pedido["idcliente"],
                        "cliente"=>$pedido["cliente"],
                        "correocliente"=>$pedido["correocliente"],
                        "telefonocliente"=>$pedido["telefonocliente"],
                        "idcontacto"=>$pedido["idcontacto"],
                        "contacto"=>$pedido["contacto"],
                        "correocontacto"=>$pedido["correocontacto"],
                        "telefonocontacto"=>$pedido["telefonocontacto"],
                        "subtotal"=>$pedido["subtotal"],
                        "iva"=>$pedido["iva"],
                        "total"=>$pedido["total"],
                        "abonado"=>$pedido["abonado"],
                        "fecha"=>$pedido["fecha"],
                        "motivofinalizacion"=>$pedido["motivofinalizacion"],
                        "status"=>$pedido["status"],
                        "statusproduccion"=>$pedido["statusproduccion"],
                        "statuspago"=>$pedido["statuspago"]);
        }else{
            return array("respuesta"=>"ERROR",
                        "mensaje"=>"ERROR: No se pudo recuperar la información del pedido.");
        }
    }

    /*
    Devuelve las especificaciones que cumplan con las condiciones del query, así como sus desgloses (productos).

    @access public
    @param string $query
    @return array
    */
    private function getEspecificaciones($query){
        $resultados = array();
        $especificaciones = mysqli_query($this->con,$query);
        if(mysqli_num_rows($especificaciones)>0){
            while ($especificacion = mysqli_fetch_assoc($especificaciones)) {
                $queryDesglose = "select
                *
                from
                vespecificacionesproductos
                where
                idespecificacion = '".$especificacion["idespecificacion"]."'";
                $resultadosDesglose = array();
                $desgloses = mysqli_query($this->con,$queryDesglose);
                while ($desglose = mysqli_fetch_assoc($desgloses)) {
                    $resultadosDesglose[] = array("idespecificacionproducto"=>$desglose["idespecificacionproducto"],
                                                "idespecificacion"=>$desglose["idespecificacion"],
                                                "idpedido"=>$desglose["idpedido"],
                                                "idpedidoproducto"=>$desglose["idpedidoproducto"],
                                                "cantidad"=>$desglose["cantidad"],
                                                "idproducto"=>$desglose["idproducto"],
                                                "producto"=>$desglose["producto"],
                                                "idcolor"=>$desglose["idcolor"],
                                                "color"=>$desglose["color"],
                                                "idtalla"=>$desglose["idtalla"],
                                                "talla"=>$desglose["talla"],
                                                "idcotizacion"=>$desglose["idcotizacion"],
                                                "idcotizacionproducto"=>$desglose["idcotizacionproducto"],
                                                "idcliente"=>$desglose["idcliente"],
                                                "cliente"=>$desglose["cliente"],
                                                "cantidad_surtida"=>$desglose["cantidad_surtida"],
                                                "fecha_inicio_produccion"=>$desglose["fecha_inicio_produccion"],
                                                "fecha_fin_produccion"=>$desglose["fecha_fin_produccion"],
                                                "status"=>$desglose["status"]);
                }
                $resultados[] = array("idespecificacion"=>$especificacion["idespecificacion"],
                                    "idusuario"=>$especificacion["idusuario"],
                                    "usuario"=>$especificacion["usuario"],
                                    "idpedido"=>$especificacion["idpedido"],
                                    "idcliente"=>$especificacion["idcliente"],
                                    "cliente"=>$especificacion["cliente"],
                                    "nombrediseno"=>$especificacion["nombrediseno"],
                                    "fechaentrega"=>$especificacion["fechaentrega"],
                                    "serigrafia"=>$especificacion["serigrafia"],
                                    "digital"=>$especificacion["digital"],
                                    "bordado"=>$especificacion["bordado"],
                                    "especificaciones"=>$especificacion["especificaciones"],
                                    "imagen1"=>$especificacion["imagen1"],
                                    "imagen2"=>$especificacion["imagen2"],
                                    "imagen3"=>$especificacion["imagen3"],
                                    "imagen4"=>$especificacion["imagen4"],
                                    "imagen5"=>$especificacion["imagen5"],
                                    "archivo1"=>$especificacion["archivo1"],
                                    "archivo2"=>$especificacion["archivo2"],
                                    "archivo3"=>$especificacion["archivo3"],
                                    "archivo4"=>$especificacion["archivo4"],
                                    "archivo5"=>$especificacion["archivo5"],
                                    "statusalmacen"=>$especificacion["statusalmacen"],
                                    "fechastatusalmacen"=>$especificacion["fechastatusalmacen"],
                                    "statusdiseno"=>$especificacion["statusdiseno"],
                                    "fechastatusdiseno"=>$especificacion["fechastatusdiseno"],
                                    "statusaprobacion"=>$especificacion["statusaprobacion"],
                                    "fechastatusaprobacion"=>$especificacion["fechastatusaprobacion"],
                                    "statusproduccion"=>$especificacion["statusproduccion"],
                                    "fechastatusproduccion"=>$especificacion["fechastatusproduccion"],
                                    "status"=>$especificacion["status"],
                                    "autorizaciondiseno"=>$especificacion["autorizaciondiseno"],
                                    "rdiseno"=>$especificacion["rdiseno"],
                                    "raprobaciondiseno"=>$especificacion["raprobaciondiseno"],
                                    "cantidadsurtida"=>$especificacion["cantidadsurtida"],
                                    "cantidadrequerida"=>$especificacion["cantidadrequerida"],
                                    "desgloses"=>$resultadosDesglose);
            }
            return array("respuesta"=>"OK",
                        "especificaciones"=>$resultados);
        }else{
            return array("respuesta"=>"ERROR",
                        "mensaje"=>"ERROR: No se pudieron recuperar las especificaciones.");
        }
    }

    /*
    Devuelve un listado de especificaciones de un pedido.

    @access public
    @param int $idpedido
    @return array
    */
    public function obtenerEspecificacionesPedido($idpedido,$filtro = ""){
        $query = "select
        *
        from
        vespecificaciones
        where
        idpedido = '".$idpedido."'
        ".$filtro."
        order by
        fechaentrega,
        idespecificacion";

        return $this->getEspecificaciones($query);
        
    }

    /*
    Devuelve un listado de las especificaciones de un cliente.

    @access public
    @param int $idcliente
    @param string $filtro
    @return array
    */
    public function obtenerEspecificacionesCliente($idcliente,$filtro){
        $query = "select
        *
        from
        vespecificaciones
        where
        statusproduccion!=2
        ".$filtro." and
        idcliente = '".$idcliente."'
        order by
        fechaentrega,
        idespecificacion";
        return $this->getEspecificaciones($query);
    }

    /*
    Devuelve un listado de las especificaciones de un cliente no registrado.

    @access public
    @param int $cliente
    @param string $filtro
    @return array
    */
    public function obtenerEspecificacionesSinCliente($cliente,$filtro){
        $query = "select
        *
        from
        vespecificaciones
        where
        statusproduccion!=2
        ".$filtro." and
        idpedido in
        (
        select
            idpedido
        from
            vpedidos
        where
            idcliente = 0 and
            cliente = '".$cliente."'
        )
        order by
        fechaentrega,
        idespecificacion";
        return $this->getEspecificaciones($query);
    }
}
?>