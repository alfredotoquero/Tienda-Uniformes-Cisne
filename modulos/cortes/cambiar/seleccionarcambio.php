<?
unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));

// mysqli_query($con,"delete from trcambioproductostmp where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'");

?>

<script>
function formatMoney(n, c, d, t) {
  var c = isNaN(c = Math.abs(c)) ? 2 : c,
    d = d == undefined ? "." : d,
    t = t == undefined ? "," : t,
    s = n < 0 ? "-" : "",
    i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))),
    j = (j = i.length) > 3 ? j % 3 : 0;

  return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
}

function validate(evt) {
    var theEvent = evt || window.event;

    // Handle paste
    if (theEvent.type === 'paste') {
        key = event.clipboardData.getData('text/plain');
    } else {
    // Handle key press
        var key = theEvent.keyCode || theEvent.which;
        key = String.fromCharCode(key);
    }
    var regex = /[0-9]|\./;
    if( !regex.test(key) ) {
        theEvent.returnValue = false;
        if(theEvent.preventDefault) theEvent.preventDefault();
    }

}

function validarExistencias(idtalla,idcolor,idproducto,idpartida,cantidad){
    $.ajax({
        type: "POST",
        url: "/modulos/cortes/cambiar/validarexistencias.php",
        data: "idcolor=" + idcolor + "&idtalla=" + idtalla + "&idproducto=" + idproducto + "&idpartida=" + idpartida + "&cantidad=" + cantidad,
        async: false,
        success: function (data){
            if (data!="OK") {
                alert("ATENCION: No hay existencias suficientes para este color y talla.");
            }
            console.log(data);
        }
    });
}

function continuar(){
    $("#formCambiar").submit();
    // alert("cambiar");
}



$(document).ready(function (e) {
    $('#btnCancelar').on('click', function(e){
        e.preventDefault();
        window.history.back();
    });
});


</script>

<!-- Header -->
<div class="content-header white  box-shadow-0" id="content-header">
	<div class="navbar navbar-expand-lg">
	  <!-- btn to toggle sidenav on small screen -->
	  <a class="d-lg-none mx-2" data-toggle="modal" data-target="#aside">
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512"><path d="M80 304h352v16H80zM80 248h352v16H80zM80 192h352v16H80z"/></svg>
	  </a>
	  <!-- Page title -->
	  <div class="navbar-text nav-title flex" id="pageTitle">Cambiar Productos</div>
	</div>
</div>

<div class="padding">
    <form name="formCambiar" id="formCambiar" method="post" action="?modulo1=cortes&modulo2=cambiarproductos&idcorte=<? echo $_GET["idcorte"]; ?>&idcuenta=<? echo $_GET["idcuenta"]; ?>&idticket=<? echo $_GET["idticket"]; ?>" enctype="multipart/form-data">
        <div class="box">
            <!-- <div class="box-header">

            </div> -->

            <div class="box-body">
                <div class="row">
                    <div class="col-sm-12">
                        <button type="button" class="btn btn-primary waves-effect waves-light pull-right" onClick="continuar();" name="btnContinuar" id="btnContinuar">Continuar</button>
                        <button type="button" class="btn btn-danger waves-effect waves-light pull-right mr-2" name="btnCancelar" id="btnCancelar">Cancelar</button>

                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <input type="hidden" name="accion" id="accion" value="cambiar">
                    <input type="hidden" name="sd2581ak042" value="<? echo $_SESSION["authToken"]; ?>">
                    
                </div>
            </div>
            
        </div>
        
        <div class="box" id="listaProductos">
            <div class="box-header">
                Lista de productos a cambiar
            </div>
            <div class="box-body" >
                <div class="table-responsive">
                    <table class="table m-0">
                        <thead>
                            <tr>
                                <th width="30">Cant.</th>
                                <th>Producto</th>
                                <th width="150">Talla</th>
                                <th width="300">Color</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?

                        foreach ($_POST["txtCantidad"] as $i => $cantidad) {
                            if ($cantidad=="") {
                                continue;
                            }
                            
                            ?>
                
                                <?
                                $partida = mysqli_fetch_assoc(mysqli_query($con,"select * from trcuentaproductos where idcuentaproducto='".$_POST["partida"][$i]."'"));                                
                                
                                $producto = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto = '".$partida["idproducto"]."'"));

                                // $color = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatcolores where idcolor='".$partida["idcolor"]."'"));
                                // $talla = mysqli_fetch_assoc(mysqli_query($con,"select * from tcattallas where idtalla='".$partida["idtalla"]."'"));
                                
                                
                                ?>
                                <tr id="trpartida<? echo $partida["idcuentaproducto"]; ?>">
                                    <input type="hidden" name="partida[]" id="partida" value="<? echo $partida["idcuentaproducto"]; ?>">
                                    <input type="hidden" name="cantidad[]" id="cantidad" value="<? echo $cantidad; ?>">

                                    <td align="center"><? echo $cantidad; ?></td>
                                    
                                    <td>
                                        <div class="row">
                                            <div class="col">
                                                <? echo $producto["nombre"]; ?>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <select name="slcTalla[]" id="slcTalla-<? echo $partida["idcuentaproducto"]; ?>" class="form-control" onChange="validarExistencias($(this).val(),$('#slcColor-<? echo $partida["idcuentaproducto"]; ?>').val(),<? echo $partida["idproducto"]; ?>,<? echo $partida["idcuentaproducto"]; ?>,<? echo $cantidad; ?>);">
                                            <?
                                            $tallas = mysqli_query($con,"select * from tcattallas where idtalla in (select idtalla from tproductoexistencias where idproducto='".$partida["idproducto"]."' and existencia>0) and idtalla not in (select idtalla from tproductovariantesprecio where idproducto='".$partida["idproducto"]."') order by posicion");
                                            while($talla = mysqli_fetch_assoc($tallas)){
                                                ?>
                                                <option value="<? echo $talla["idtalla"]; ?>" <? if($talla["idtalla"]==$partida["idtalla"]){?> selected <?} ?>><? echo $talla["nombre"]; ?></option>
                                                <?
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="slcColor[]" id="slcColor-<? echo $partida["idcuentaproducto"]; ?>" class="form-control" onChange="validarExistencias($('#slcTalla-<? echo $partida["idcuentaproducto"]; ?>').val(),$(this).val(),<? echo $partida["idproducto"]; ?>,<? echo $partida["idcuentaproducto"]; ?>,<? echo $cantidad; ?>);">
                                            <?
                                            $colores = mysqli_query($con,"select * from tcatcolores where idcolor in (select idcolor from tproductoexistencias where idproducto='".$partida["idproducto"]."' and existencia>0)");
                                            while($color = mysqli_fetch_assoc($colores)){
                                                ?>
                                                <option value="<? echo $color["idcolor"]; ?>" <? if($color["idcolor"]==$partida["idcolor"]){?> selected <?} ?>><? echo $color["nombre"]; ?></option>
                                                <?
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <?
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>


