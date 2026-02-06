<?php
if(isset($_SERVER)){
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
}else{
    global $HTTP_SERVER_VARS;
    if(isset( $HTTP_SERVER_VARS)){
        $user_agent = $HTTP_SERVER_VARS['HTTP_USER_AGENT'];
    }else{
        global $HTTP_USER_AGENT;
        $user_agent = $HTTP_USER_AGENT;
    }
}

function obtenerSO() { 
    global $user_agent;
    $os_array =  array(
                    '/windows nt 10/i'      =>  'Windows 10',
                    '/windows nt 6.3/i'     =>  'Windows 8.1',
                    '/windows nt 6.2/i'     =>  'Windows 8',
                    '/windows nt 6.1/i'     =>  'Windows 7',
                    '/windows nt 6.0/i'     =>  'Windows Vista',
                    '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
                    '/windows nt 5.1/i'     =>  'Windows XP',
                    '/windows xp/i'         =>  'Windows XP',
                    '/windows nt 5.0/i'     =>  'Windows 2000',
                    '/windows me/i'         =>  'Windows ME',
                    '/win98/i'              =>  'Windows 98',
                    '/win95/i'              =>  'Windows 95',
                    '/win16/i'              =>  'Windows 3.11',
                    '/macintosh|mac os x/i' =>  'Mac OS X',
                    '/mac_powerpc/i'        =>  'Mac OS 9',
                    '/linux/i'              =>  'Linux',
                    '/ubuntu/i'             =>  'Ubuntu',
                    '/iphone/i'             =>  'iPhone',
                    '/ipod/i'               =>  'iPod',
                    '/ipad/i'               =>  'iPad',
                    '/android/i'            =>  'Android',
                    '/blackberry/i'         =>  'BlackBerry',
                    '/webos/i'              =>  'Mobile'
                  );
    //
    $os_platform = "Unknown OS Platform";
    foreach ($os_array as $regex => $value) { 
        if (preg_match($regex, $user_agent)) {
            $os_platform = $value;
        }
    }
    return $os_platform;
}

$so = obtenerSO();

if($_POST["accion"]=="actualizar"){

    $impresora = $_POST["slcImpresora"];

    if($so=="Windows"){
		if(get_magic_quotes_gpc()){
			$impresora = addslashes($impresora);
		}else{
			$impresora = str_replace("\\","\\\\",$impresora);
		}
    }
    
    mysqli_query($con,"update tvendedores set impresora = '".$impresora."' where idvendedor = '".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'");
}

$impresora = mysqli_fetch_assoc(mysqli_query($con,"select impresora from tvendedores where idvendedor = '".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'"))["impresora"];
?>
<script>
    function cargarImpresoras(){
        for(var i = 0;i<impresorassistema.length;i++){
            $("#slcImpresora").append("<option value=\"" + impresorassistema[i] + "\">" + impresorassistema[i] + "</option>");
        }

        <? if($impresora!=""){ ?>
        $("#slcImpresora").val("<? echo $impresora; ?>");
        <? } ?>
    }
</script>

<div class="content-header white  box-shadow-0" id="content-header">
	<div class="navbar navbar-expand-lg">
	  <!-- btn to toggle sidenav on small screen -->
	  <a class="d-lg-none mx-2" data-toggle="modal" data-target="#aside">
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512"><path d="M80 304h352v16H80zM80 248h352v16H80zM80 192h352v16H80z"/></svg>
	  </a>
	  <!-- Page title -->
	  <div class="navbar-text nav-title flex" id="pageTitle">Configuración</div>
	</div>
</div>

<div class="padding">
	<div class="box">
        <div class="box-header">
			<div class="p-2">
				<div class="row">
					<div class="col-sm-12">
						<a href="javascript:;" onClick="formConfiguracion.submit();" class="btn btn-primary waves-effect pull-right" >Guardar Cambios</a>
					</div>
				</div>
			</div>
		</div>
        <div class="box-body">
            <form name="formConfiguracion" id="formConfiguracion" action="?modulo1=configuracion" method="post">
                <input type="hidden" name="accion" value="actualizar">

                <div class="form-group">
                    <div class="row">
                        <div class="col-2" ><label for="">Impresora</label><span>*</span></div>
                        <div class="col-8">
                            <select name="slcImpresora" id="slcImpresora" class="form-control">
                                <option value="">--Selecciona una Impresora--</option>
                            </select>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>