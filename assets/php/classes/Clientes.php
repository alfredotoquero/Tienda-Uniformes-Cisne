<?
class Clientes{
    private $con;

    function __construct($conexion) {
        $this->con = $conexion;
    }

    /*
    Devuelve un listado de los clientes.

    @access public
    @param string $busqueda
    @return array
    */
    public function obtenerClientes($busqueda = ""){
        if($busqueda!=""){
            $where = "where 
            nombre like '%".$busqueda."%' or 
            rfc like '%".$busqueda."%' or 
            razonsocial like '%".$busqueda."%' or 
            correo like '%".$busqueda."%' or 
            telefono like '%".$busqueda."%'";
        }
        $query = "select
        *
        from
        tclientes
        ".$where."
        order by
        nombre";
        $resultados = array();
        $clientes = mysqli_query($this->con,$query);
        if(mysqli_num_rows($clientes)>0){
            while ($cliente = mysqli_fetch_assoc($clientes)) {
                $resultados[] = array("idcliente"=>$cliente["idcliente"],
                                    "nombre"=>$cliente["nombre"],
                                    "rfc"=>$cliente["rfc"],
                                    "razonsocial"=>$cliente["razonsocial"],
                                    "correo"=>$cliente["correo"],
                                    "telefono"=>$cliente["telefono"],
                                    "status"=>$cliente["status"]);
            }
            return array("respuesta"=>"OK","clientes"=>$resultados);
        }else{
            return array("respuesta"=>"ERROR",
                        "mensaje"=>"ERROR: No se pudieron recuperar los clientes.");
        }
    }

    /*
    Devuelve la información de un cliente.

    @access public
    @param int $idcliente
    @return array
    */
    public function obtenerCliente($idcliente){
        $query = "select 
        * 
        from 
        tclientes 
        where 
        idcliente = '".$idcliente."'";
        $resultados = array();
        $cliente = mysqli_fetch_assoc(mysqli_query($this->con,$query));
        if($cliente["idcliente"]>0){
            return $cliente;
        }else{
            return array("respuesta"=>"ERROR",
                        "mensaje"=>"ERROR: No se pudieron recuperar los datos del cliente.");
        }
    }

    /*
    Guarda la información de un cliente.

    @access public
    @param string $nombre
    @param string $rfc
    @param string $razonsocial
    @param string $correo
    @param string $telefono
    @return array
    */
    public function guardarCliente($nombre,$rfc,$razonsocial,$correo,$telefono){
        $query = "insert 
        into 
        tclientes 
        (nombre,
        rfc,
        razonsocial,
        correo,
        telefono) 
        values 
        ('".$nombre."',
        '".$rfc."',
        '".$razonsocial."',
        '".$correo."',
        '".$telefono."')";
        if(mysqli_query($this->con,$query)){
            $idcliente = mysqli_insert_id($this->con);
            return array("respuesta"=>"OK","idcliente"=>$idcliente);
        }else{
            return array("respuesta"=="ERROR");
        }
    }

    /*
    Edita la informacion de un cliente.

    @access public
    @param string $nombre
    @param string $rfc
    @param string $razonsocial
    @param string $correo
    @param string $telefono
    @return array
    */
    public function editarCliente($idcliente,$nombre,$rfc,$razonsocial,$correo,$telefono){
        $query = "update 
        tclientes 
        set 
        nombre = '".$nombre."',
        rfc = '".$rfc."',
        razonsocial = '".$razonsocial."',
        correo = '".$correo."',
        telefono = '".$telefono."' 
        where 
        idcliente = '".$idcliente."'";
        if(mysqli_query($this->con,$query)){
            return array("respuesta"=>"OK");
        }else{
            return array("respuesta"=="ERROR");
        }
    }

    /*
    Deshabilita un cliente especifico.

    @access public
    @param int $idcliente
    @return array
    */
    public function deshabilitarCliente($idcliente){
        $query = "update 
        tclientes 
        set 
        status = 'I'
        where 
        idcliente = '".$idcliente."'";
        if(mysqli_query($this->con,$query)){
            return array("respuesta"=>"OK");
        }else{
            return array("respuesta"=="ERROR");
        }
    }

    /*
    Habilita un cliente especifico.

    @access public
    @param int $idcliente
    @return array
    */
    public function habilitarCliente($idcliente){
        $query = "update 
        tclientes 
        set 
        status = 'A'
        where 
        idcliente = '".$idcliente."'";
        if(mysqli_query($this->con,$query)){
            return array("respuesta"=>"OK");
        }else{
            return array("respuesta"=="ERROR");
        }
    }

    /*
    Devuelve un listado de contactos de un cliente.

    @access public
    @param int $idcliente
    @param string $busqueda
    @return array
    */
    public function obtenerContactos($idcliente,$busqueda = ""){
        if($busqueda!=""){
            $where = "and
            (nombre like '%".$busqueda."%' or 
            correo like '%".$busqueda."%' or 
            telefono like '%".$busqueda."%')";
        }
        $query = "select
        *
        from
        tcontactos
        where
        idcliente = '".$idcliente."'
        ".$where."
        order by
        nombre";
        $resultados = array();
        $contactos = mysqli_query($this->con,$query);
        if(mysqli_num_rows($contactos)>0){
            while ($contacto = mysqli_fetch_assoc($contactos)) {
                $resultados[] = array("idcontacto"=>$contacto["idcontacto"],
                                    "idcliente"=>$contacto["idcliente"],
                                    "nombre"=>$contacto["nombre"],
                                    "correo"=>$contacto["correo"],
                                    "telefono"=>$contacto["telefono"],
                                    "status"=>$contacto["status"]);
            }
            return array("respuesta"=>"OK","contactos"=>$resultados);
        }else{
            return array("respuesta"=>"ERROR",
                        "mensaje"=>"ERROR: No se pudieron recuperar los contactos.");
        }
    }

    /*
    Devuelve los datos de un contacto especifico.

    @access public
    @param int $idcontacto
    @return array
    */
    public function obtenerContacto($idcontacto){
        $query = "select 
        * 
        from 
        tcontactos 
        where 
        idcontacto = '".$idcontacto."'";
        $resultados = array();
        $contacto = mysqli_fetch_assoc(mysqli_query($this->con,$query));
        if($contacto["idcontacto"]>0){
            return $contacto;
        }else{
            return array("respuesta"=>"ERROR",
                        "mensaje"=>"ERROR: No se pudieron recuperar los datos del contacto.");
        }
    }


    /*
    Guarda la información de un contacto.

    @access public
    @param int $idcliente
    @param string $nombre
    @param string $correo
    @param string $telefono
    @return array
    */
    public function guardarContacto($idcliente,$nombre,$correo,$telefono){
        $query = "insert 
        into 
        tcontactos 
        (idcliente,
        nombre,
        correo,
        telefono) 
        values 
        ('".$idcliente."',
        '".$nombre."',
        '".$correo."',
        '".$telefono."')";
        if(mysqli_query($this->con,$query)){
            return array("respuesta"=>"OK");
        }else{
            return array("respuesta"=="ERROR");
        }
    }

    /*
    Edita la información de un contacto.

    @access public
    @param int $idcontacto
    @param string $nombre
    @param string $correo
    @param string $telefono
    @return array
    */
    public function editarContacto($idcontacto,$nombre,$correo,$telefono){
        $query = "update
        tcontactos 
        set 
        nombre = '".$nombre."',
        correo = '".$correo."',
        telefono = '".$telefono."'
        where
        idcontacto = '".$idcontacto."'";
        if(mysqli_query($this->con,$query)){
            return array("respuesta"=>"OK");
        }else{
            return array("respuesta"=="ERROR");
        }
    }

    /*
    Deshabilita un contacto especifico.

    @access public
    @param int $idcontacto
    @return array
    */
    public function deshabilitarContacto($idcontacto){
        $query = "update 
        tcontactos 
        set 
        status = 'I'
        where 
        idcontacto = '".$idcontacto."'";
        if(mysqli_query($this->con,$query)){
            return array("respuesta"=>"OK");
        }else{
            return array("respuesta"=="ERROR");
        }
    }

    /*
    Habilita un contacto especifico.

    @access public
    @param int $idcontacto
    @return array
    */
    public function habilitarContacto($idcontacto){
        $query = "update 
        tcontactos 
        set 
        status = 'A'
        where 
        idcontacto = '".$idcontacto."'";
        if(mysqli_query($this->con,$query)){
            return array("respuesta"=>"OK");
        }else{
            return array("respuesta"=="ERROR");
        }
    }

}
?>