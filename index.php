<?
include("vm39845um223u/c91ktn24g7if5u.php");
include("2cnytm029mp3r/cm293uc5904uh.php");

if($_POST["accion"]=="login"){
	if($_POST["cm2893um20gjer"]==$_SESSION["authToken"]){
		$usuario = mysqli_real_escape_string($con,$_POST["txtUsuario"]);
		$password = mysqli_real_escape_string($con,$_POST["txtPassword"]);
		$vendedor = mysqli_query($con,"select * from tvendedores where usuario = '$usuario' and password = '$password' and status = 'A' limit 1");
		if(mysqli_num_rows($vendedor)>0){
      $vendedor = mysqli_fetch_assoc($vendedor);
      $almacen = mysqli_fetch_assoc(mysqli_query($con,"select * from talmacenes where idalmacen in (select idalmacen from tsucursales where idsucursal='".$vendedor["idsucursal"]."')"));
			$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] = $vendedor["idvendedor"];
			$_SESSION["v3nd3d0rpl4y3r4spvc1sn3sucursal"] = $vendedor["idsucursal"];
			$_SESSION["v3nd3d0rpl4y3r4spvc1sn3almacen"] = $almacen["idalmacen"];
			$_SESSION["v3nd3d0rpl4y3r4spvc1sn3Fecha"] = date("Y-n-j H:i:s");
			header("location: home.php");
		}else{
			$error = true;
		}
	}else{
		$error = true;
	}
}else{
	if(isset($_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"])){
		header("location: home.php");
	}
}

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Punto de Venta</title>
  <meta name="description" content="Responsive, Bootstrap, BS4" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimal-ui" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <!-- for ios 7 style, multi-resolution icon of 152x152 -->
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-barstyle" content="black-translucent">
  <link rel="apple-touch-icon" href="/assets/images/logo.svg">
  <meta name="apple-mobile-web-app-title" content="Flatkit">
  <!-- for Chrome on Android, multi-resolution icon of 196x196 -->
  <meta name="mobile-web-app-capable" content="yes">
  <link rel="shortcut icon" sizes="196x196" href="/assets/images/logo.svg">

  <!-- style -->
  <link rel="stylesheet" href="/libs/font-awesome/css/font-awesome.min.css" type="text/css" />

  <!-- build:css /assets/css/app.min.css -->
  <link rel="stylesheet" href="/libs/bootstrap/dist/css/bootstrap.min.css" type="text/css" />
  <link rel="stylesheet" href="/assets/css/app.css" type="text/css" />
  <link rel="stylesheet" href="/assets/css/style.css" type="text/css" />
  <!-- endbuild -->
</head>
<body>

<div class="d-flex flex-column flex">
  <div class="navbar light bg pos-rlt box-shadow">
    <div class="mx-auto">
      <!-- brand -->
      <a href="/index.html" class="navbar-brand">
      	<svg viewBox="0 0 24 24" height="28" width="28" xmlns="http://www.w3.org/2000/svg">
      	    <path d="M0 0h24v24H0z" fill="none"/>
      	    <path d="M19.51 3.08L3.08 19.51c.09.34.27.65.51.9.25.24.56.42.9.51L20.93 4.49c-.19-.69-.73-1.23-1.42-1.41zM11.88 3L3 11.88v2.83L14.71 3h-2.83zM5 3c-1.1 0-2 .9-2 2v2l4-4H5zm14 18c.55 0 1.05-.22 1.41-.59.37-.36.59-.86.59-1.41v-2l-4 4h2zm-9.71 0h2.83L21 12.12V9.29L9.29 21z" fill="#fff" class="fill-theme"/>
      	</svg>
      	<img src="/assets/images/logo.png" alt="." class="hide">
      	<span class="hidden-folded d-inline">Punto de Venta Playeras Cisne</span>
      </a>
      <!-- / brand -->
    </div>
  </div>
  <div id="content-body">
    <div class="py-5 text-center w-100">
      <div class="mx-auto w-xxl w-auto-xs">
        <div class="px-3">
          <form name="formLogin" id="formLogin" method="post" action="index.php">
		  	<input type="hidden" name="accion" value="login">
			<input type="hidden" name="cm2893um20gjer" value="<? echo $_SESSION["authToken"]; ?>">
            <div class="form-group">
              <input type="text" class="form-control" id="txtUsuario" name="txtUsuario" placeholder="Nombre de Usuario" required>
            </div>
            <div class="form-group">
              <input type="password" class="form-control" id="txtPassword" name="txtPassword" placeholder="Contraseña" required>
            </div>
            <div class="mb-3">
              <label class="md-check">
                <input type="checkbox"><i class="primary"></i> Mantener mi sesión iniciada
              </label>
            </div>
            <button type="button" onClick="validarForm();" class="btn primary">Iniciar Sesión</button>
          </form>
          <div class="my-4">
            <a href="/password.php" class="text-primary _600">¿Olvidaste tu contraseña?</a>
          </div>
          <div>
            ¿No tienes una cuenta?
            <a href="/registro.php" class="text-primary _600">Registrate</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- build:js scripts/app.min.js -->
<!-- jQuery -->
  <script src="/libs/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap -->
  <script src="/libs/popper.js/dist/umd/popper.min.js"></script>
  <script src="/libs/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- core -->
  <script src="/libs/pace-progress/pace.min.js"></script>
  <script src="/libs/pjax/pjax.js"></script>

  <script src="scripts/lazyload.config.js"></script>
  <script src="scripts/lazyload.js"></script>
  <script src="scripts/plugin.js"></script>
  <script src="scripts/nav.js"></script>
  <script src="scripts/scrollto.js"></script>
  <script src="scripts/toggleclass.js"></script>
  <script src="scripts/theme.js"></script>
  <script src="scripts/ajax.js"></script>
  <script src="scripts/app.js"></script>
<!-- endbuild -->
	<script>
	function validarForm(){
		if($("#txtUsuario").val()==""){
			alert("ERROR: Debes indicar un nombre de usuario.");
			$("#txtUsuario").focus();
		}else if($("#txtPassword").val()==""){
			alert("ERROR: Debes indicar una contraseña.");
			$("#txtPassword").focus();
		}else{
			$("#formLogin").submit();
		}
	}
	</script>
</body>
</html>
