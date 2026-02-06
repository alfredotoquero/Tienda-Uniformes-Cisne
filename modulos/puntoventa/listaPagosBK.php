<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

// Insertar el producto en la tabla temporal y mostrar los datos de la misma
// si el producto ya había sido insertado, su registro aumenta su cantidad en uno

if ($_POST["accion"]=="agregar") {
    if (mysqli_num_rows(mysqli_query($con,"select * from trcuentaproductostmp where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."' and idproducto='".$_POST["idproducto"]."' and idproducto in (select idproducto from tproductos where idproducto='".$_POST["idproducto"]."' and tipo='S')"))==0) {
        mysqli_query($con,"insert into trcuentaproductostmp (idvendedor,idproducto,idcolor,idtalla,cantidad,descuento) values ('".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."','".$_POST["idproducto"]."','".$_POST["idcolor"]."','".$_POST["idtalla"]."',1,'".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3descuento"]."')");
    } else {
        mysqli_query($con,"update trcuentaproductostmp set cantidad=cantidad+1 where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."' and idproducto='".$_POST["idproducto"]."'");
    }
    
}

if ($_POST["accion"]=="sumar") {
    mysqli_query($con,"update trcuentaproductostmp set cantidad=cantidad+1 where idtmp='".$_POST["idpartida"]."'");
}

if ($_POST["accion"]=="restar") {
    if (mysqli_num_rows(mysqli_query($con,"select * from trcuentaproductostmp where idtmp='".$_POST["idpartida"]."' and cantidad=1"))>0) {
        mysqli_query($con,"delete from trcuentaproductopersonalizadostmp where idcuentaproductotmp='".$_POST["idpartida"]."'");
        mysqli_query($con,"delete from trcuentaproductostmp where idtmp='".$_POST["idpartida"]."'");
    } else {
        mysqli_query($con,"update trcuentaproductostmp set cantidad=cantidad-1 where idtmp='".$_POST["idpartida"]."'");
    }
}

if ($_POST["accion"]=="eliminar") {
    if (mysqli_num_rows(mysqli_query($con,"select * from trcuentaproductostmp where idtmp='".$_POST["idpartida"]."' and idcuenta=0"))>0) {
        mysqli_query($con,"delete from trcuentaproductopersonalizadostmp where idcuentaproductotmp='".$_POST["idpartida"]."'");
        mysqli_query($con,"delete from trcuentaproductostmp where idtmp='".$_POST["idpartida"]."'");
    } else {
        mysqli_query($con,"update trcuentaproductostmp set borrar=1 where idtmp='".$_POST["idpartida"]."'");
    }
}

if ($_POST["accion"]=="aplicardescuento") {
    mysqli_query($con,"update trcuentaproductostmp set descuento='".$_POST["descuento"]."' where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'");
    // variable de sesion con descuento
    $_SESSION["v3nd3d0rpl4y3r4spvc1sn3descuento"] = $_POST["descuento"];
}

if ($_POST["accion"]=="aplicardescuentoindividual") {
    mysqli_query($con,"update trcuentaproductostmp set descuento='".$_POST["descuento"]."' where idtmp='".$_POST["idpartida"]."'");
}

if ($_POST["accion"]=="eliminarproductos") {
    mysqli_query($con,"delete from trcuentaproductopersonalizadostmp where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'");
    mysqli_query($con,"delete from trcuentaproductostmp where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'");
    $_SESSION["v3nd3d0rpl4y3r4spvc1sn3descuento"] = 0;
}

if ($_POST["accion"]=="agregarpersonalizacion") {
    mysqli_query($con,"insert into trcuentaproductopersonalizadostmp (idcuentaproductotmp,idvendedor,idpersonalizacion,personalizacion) values ('".$_POST["idpartida"]."','".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."',0,'')");
}

if ($_POST["accion"]=="eliminarpersonalizacion") {
    mysqli_query($con,"delete from trcuentaproductopersonalizadostmp where idtmp='".$_POST["idpersonalizaciontmp"]."'");
}

if ($_POST["accion"]=="actualizarselect") {
    mysqli_query($con,"update trcuentaproductopersonalizadostmp set idpersonalizacion='".$_POST["idpersonalizacion"]."' where idtmp='".$_POST["idpersonalizaciontmp"]."'");
}

if ($_POST["accion"]=="actualizartext") {
    mysqli_query($con,"update trcuentaproductopersonalizadostmp set personalizacion='".$_POST["personalizacion"]."' where idtmp='".$_POST["idpersonalizaciontmp"]."'");
}

$cuenta = mysqli_query($con,"select * from trcuentaproductostmp where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."' and idcuenta!=0");
if (mysqli_num_rows($cuenta)>0) {
    $idcuenta = mysqli_fetch_assoc($cuenta)["idcuenta"];
}else {
    $idcuenta = 0;
}

$partidas = mysqli_query($con,"select * from trcuentaproductostmp where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."' and borrar!=1");

if (mysqli_num_rows($partidas)>0) {
    ?>
    <div class="box-header">
        Lista de productos agregados
    </div>

    <div class="box-body" >
        <div class="table-responsive">
            <table class="table m-0">
                <thead>
                    <tr>
                        <th width="30">Cant.</th>
                        <th>Producto</th>
                        <th width="100">P.U.</th>
                        <th width="100">Descuento</th>
                        <th width="100">Total</th>
                        <th width="10"></th>
                        <th width="10"></th>
                        <th width="10"></th>
                        <th width="10"></th>
                    </tr>
                </thead>
                <tbody>
                    <?
                    $subtotal = 0;
                    while($partida = mysqli_fetch_assoc($partidas)){
                        // calcular y guardar subtotal, iva y total de cada partida
                        // se agrega costo adicional por cada personalizacion seleccionada
                        $preciopersonalizaciones = mysqli_fetch_assoc(mysqli_query($con,"select sum(tc.precio) as precio from tcatpersonalizaciones tc, trcuentaproductopersonalizadostmp tr where tc.idpersonalizacion in (select idpersonalizacion from trcuentaproductopersonalizadostmp where idcuentaproductotmp='".$partida["idtmp"]."') and tc.idpersonalizacion=tr.idpersonalizacion and tr.idcuentaproductotmp='".$partida["idtmp"]."'"))["precio"];

                        $preciovariante = 0;
                        $variante = mysqli_query($con,"select * from tproductovariantesprecio where idproducto='".$partida["idproducto"]."' and idtalla='".$partida["idtalla"]."'");
                        if (mysqli_num_rows($variante)) {
                            $preciovariante = mysqli_fetch_assoc($variante)["variante"];
                        }

                        $precio = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto='".$partida["idproducto"]."'"))["precio"] + $preciopersonalizaciones + $preciovariante;
                        
                        $cantidad = $partida["cantidad"];
                        $subtotalp = $precio*((100-$partida["descuento"])/100)*$cantidad/1.08;
                        $ivap = $subtotalp*.08;
                        $totalp = $subtotalp+$ivap;
                        mysqli_query($con,"update trcuentaproductostmp set subtotal='".$subtotalp."', iva='".$ivap."', total='".$totalp."' where idtmp='".$partida["idtmp"]."'");

                        $producto = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto = '".$partida["idproducto"]."'"));
                        $subtotal += (float)($partida["cantidad"]*($precio*((100-$partida["descuento"])/100)/1.08));

                        $nombrecolor = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatcolores where idcolor='".$partida["idcolor"]."'"))["nombre"];
                        $nombretalla = mysqli_fetch_assoc(mysqli_query($con,"select * from tcattallas where idtalla='".$partida["idtalla"]."'"))["nombre"];
                    ?>
                    <tr>
                        <td align="center"><? echo $partida["cantidad"]; ?></td>
                        <td>
                            <div class="row">
                                <div class="col">
                                    <? echo $producto["nombre"] . ($partida["idcolor"]!=0 ? "<br>Color: " . $nombrecolor . "<br>Talla: " . $nombretalla : ""); ?>
                                </div>
                            </div>
                            <?
                            // obtener todas las personalizaciones agregadas en esta partida
                            $personalizaciones = mysqli_query($con,"select * from trcuentaproductopersonalizadostmp where idcuentaproductotmp='".$partida["idtmp"]."'");
                            while($personalizacion = mysqli_fetch_assoc($personalizaciones)){
                                $i++;
                                ?>
                                <div class="row" id="divPersonalizacion<? echo $partida["idtmp"]; ?>" style="margin-top:15px;">
                                    <div class="col-4">
                                        <select name="slcTipoPersonalizado<? echo $partida["idtmp"]; ?>-<? echo $personalizacion["idtmp"]; ?>" id="slcTipoPersonalizado<? echo $partida["idtmp"]; ?>-<? echo $personalizacion["idtmp"]; ?>" class="form-control" onChange="actualizarSelect(<? echo $personalizacion["idtmp"]; ?>,this.value);">
                                            <option value="0">--Selecciona una personalizacion--</option>
                                            <?
                                            $categorias = mysqli_query($con,"select * from tcatpersonalizaciones");
                                            while($categoria = mysqli_fetch_assoc($categorias)){
                                                ?>
                                                <option value="<? echo $categoria["idpersonalizacion"]; ?>" <? if($personalizacion["idpersonalizacion"]==$categoria["idpersonalizacion"]){?> selected <?} ?>><? echo $categoria["nombre"]; ?></option>
                                                <?
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-7">
                                        <textarea name="txtPersonalizacion<? echo $partida["idtmp"]; ?>-<? echo $personalizacion["idtmp"]; ?>" id="txtPersonalizacion<? echo $partida["idtmp"]; ?>-<? echo $personalizacion["idtmp"]; ?>" rows="3" class="form-control" onChange="actualizarTextarea(<? echo $personalizacion["idtmp"]; ?>,this.value);"><? echo $personalizacion["personalizacion"]; ?></textarea>
                                    </div>
                                    <div class="col-1">
                                        <button type="button" class="btn btn-primary waves-effect waves-light" onClick="eliminarPersonalizacion(<? echo $personalizacion["idtmp"] ?>);"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                                <?
                            }
                            ?>
                        </td>
                        <td>$<? echo number_format($precio,2); ?></td>
                        
                        <td><a href="javascript:;" onClick="aplicarDescuentoIndividual(<? echo $partida["idtmp"]; ?>,<? echo $partida["descuento"]; ?>);">$<? echo number_format($producto["precio"]*($partida["descuento"]/100),2) ?></a></td>
                        <td>$<? echo number_format($totalp,2); ?></td>
                        <td align="right">
                            <?
                            if ($partida["idcuenta"]==0) {
                                ?>
                                <a href="javascript:;" onClick="restarProducto(<? echo $partida["idtmp"]; ?>);" class="btn btn-danger"><i class="fas fa-minus"></i></a>
                                <?
                            }
                            
                            ?>
                        </td>
                        <td align="right">
                            <?
                            if ($partida["idcuenta"]==0) {
                                ?>
                                <a href="javascript:;" onClick="sumarProducto(<? echo $partida["idtmp"]; ?>);" class="btn btn-primary"><i class="fas fa-plus"></i></a>
                                <?
                            }
                            
                            ?>
                        </td>
                        <td align="right">
                            <a href="javascript:;" onClick="eliminarProducto(<? echo $partida["idtmp"]; ?>);" class="btn btn-primary"><i class="fas fa-cog"></i></a>
                        </td>
                        <td align="right">
                        <?
                        // si lo que se esta agregando es un servicio, no se le pueden añadir personalizaciones
                        if ($producto["tipo"]!="S" or $idcuenta==0) {
                            ?>
                            <a href="javascript:;" onClick="inputsPersonalizacion(<? echo $partida["idtmp"]; ?>);" class="btn btn-primary"><i class="fas fa-cog"></i></a>
                            <?
                        }
                        ?>
                        </td>

                    </tr>
                    <?
                    }
                    $iva = ($subtotal) * 0.08;
                    $total = (float)($subtotal) + (float)$iva;
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <?
}else {
    ?>
    <div class="box-header">
    </div>
    <div class="box-body">
        <center><b><p>--No hay productos agregados--</p></b></center>
    </div>
    <?
}
?>

<script>
$("#lblTotal").html('$<? echo number_format($total,2); ?>');
$("#subtotal").val(<? echo $subtotal; ?>);
$("#iva").val(<? echo $iva; ?>);
$("#total").val(<? echo $total; ?>);
</script>

<?
if ($_POST["accion"]=="eliminarproductos") {
    ?>
    <script>
    $("#txtDescuento").val("");
    $("#txtProducto").focus();
    </script>
    <?
}
?>