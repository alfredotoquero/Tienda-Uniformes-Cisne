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
                *
            from
                tpagos
            where
                fecha between '".$fecha_i."' and '".$fecha_f."'";
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
            $cliente = mysqli_real_escape_string($this->con,$post["cliente"]);

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
                cliente
            from
                vpedidos
            where
                idsucursal = '".$idsucursal."' and
                total > 0 and
                statuspago = 0 and
                status = 'A'
            group by
                idcliente,
                cliente
            order by
                cliente";
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

}
?>