<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

// cargar todos los almacenes excepto el cargado en el almacen origen
$almacenes = mysqli_query($con,"select * from talmacenes where idalmacen!='".$_POST["idalmacen"]."'");

?>
<div class="row">
    <div class="col-2" ><label for="">Almacen Secundario</label><span>*</span></div>
    <div class="col-10">
        <input type="hidden" name="almacens" id="almacens" value="">
        <select name="slcAlmacenS" id="slcAlmacenS" class="form-control" onchange="$('#almacens').val(this.value);">
            <option value="0">--Selecciona un almacen--</option>
            <?
            while($almacen = mysqli_fetch_assoc($almacenes)){
                ?>
                <option value="<? echo $almacen["idalmacen"]; ?>"><? echo $almacen["nombre"]; ?></option>
                <?
            }
            ?>
            <!-- poner opciones una vez que se haya seleccionado el primer almacen -->
        </select>
    </div>
</div>
<?
echo "|OK";
?>