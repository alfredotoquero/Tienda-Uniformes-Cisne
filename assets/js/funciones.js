const App = {

    modulos: {

        pagos: function () {
            $(".formBusqueda").trigger("submit");
        }
        
    }

};

$(function () {
    $(".formBusqueda").on("submit", function (e) {
        e.preventDefault();

        cargarContenedor(
            $("#archivo",this).val(),
            Object.fromEntries(new FormData(this)),
            $("#contenedor",this).val()
        );
    });

    const modulo = $(".modulo").data("modulo");
    if (App.modulos[modulo]) {
        App.modulos[modulo]();
    }
});

function conexionServidor(form){
    const formData = new FormData(form);

    fetch("/assets/php/controladores/" + $("#controlador",form).val() + ".php", {
        method: "POST",
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        
    })
    .catch(err => {
        console.error("Error:", err);
    });
}

function cargarContenedor(archivo,datos,contenedor){
    if (!archivo || !contenedor) {
        console.warn("Archivo o contenedor no definido");
        return;
    }
    
    $("#" + contenedor).load(archivo,datos);
}

function filtrarPedidosPago(idvendedor){
    cargarContenedor(
        "/modulos/pagos/listaPedidos.php",
        {
            cliente: $("#slcCliente","#formAgregarPago").val(),
            idvendedor: idvendedor
        },
        "listaPedidos"
    );
}