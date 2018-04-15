/* 
 * Control de opciones para generales de la aplicación
 * Se quiere que las instrucciones que hay dentro de la función anónima function () {..};
 * se ejecuten cuando la aplicación haya sido cargada, por eso se usa on ready:
 * $(document).on('ready', function () {...});
 * los demás script de páginas sólo requerirán la función principal
 */

 'use strict';

 var initDatePicker = {
    dateFormat: 'yy-mm-dd',
    minDate: new Date(2010, 0, 1),
    maxDate: new Date(2020, 0, 1),
    showOn: 'focus'
};
var estadosProduccion;
var categoriaTipo;
var tipoMaquina;


var anchoContenedor;

$(document).on('ready', function () {

    // Dialogo utilizado por el plugin que detecta la inactividad del usuario
    $("#idle-timeout-dialog").dialog({
        autoOpen: false,
        modal: true,
        width: 340,
        height: 195,
        closeOnEscape: false,
        draggable: false,
        resizable: false,
        buttons: [
        {id: "btnSeguirTrabajando", text: "Si, continuar", icons: {primary: "ui-icon-check"}, click: function () {
            $(this).dialog('close');
        }
    },
    {id: "btnCerrarSesion", text: "No, cerrar", icons: {primary: "ui-icon-close"}, click: function () {
        $.idleTimeout.options.onTimeout.call(this);
    }
}
],
open: function (event, ui) {
    $(".ui-dialog-titlebar-close").hide();
}
});
    
    // ajustes para el formulario de autenticación
    $("#index-frmautentica").estiloFormulario({
        anchoEtiquetas: '75px',
        anchoEntradas: '215px',
        claseFormulario: 'divForm'
    });
    
    // creación del diálogo para solicitar datos de autenticación
    $('#index-frmautentica').dialog({
        autoOpen: true,
        width: 390,
        modal: true,
        open: function () {
            $(".ui-dialog-titlebar-close").hide();
            var anchoLista = ($('#index-frmautentica > ol > li').width()) + 'px';
            $("#index-iniciar-sesion, #index-activar-cuenta").button().css('width', anchoLista);
        }
    });
    
    // Bloque de código que gestiona los eventos de botones y span de index-frmautentica
    $("#index-iniciar-sesion").on("click", function () {
        $.blockUI({message: getMensaje('Verificando usuario')});
        $.post("controlador/fachada.php", {
            clase: "Usuario",
            oper: 'autenticar',
            usuario: $('#index-nombre-usuario').val(),
            contrasena: MD5($('#index-contrasena').val())
        }, function (data) {
            //data.autenticado = true;
            if (data.autenticado) {
                switch(data.tipo){
                    case "operario":
                    cargarPagina("#index-contenido", "vista/html/operacion_produccion.html");
                    break;
                    case "administrador":
                    cargarPagina("#index-panel-izquierdo", "vista/html/menu-administrador.html");
                    break;
                    case "supervisor":
                    cargarPagina("#index-panel-izquierdo", "vista/html/menu-supervisor.html");
                    break;
                }
                $("#index-frmautentica").dialog("close");
            } else {
                mostrarMensaje("Usuario o contraseña erróneos", '#index-mensaje-inicio');
            }
        }, 'json').fail(function () {
            mostrarMensaje("No se pudo realizar la autenticación del usuario", '#index-mensaje-inicio');
        }).always(function () {
            $.unblockUI();
        });
    });

    // Gestión del evento "Olvidé mi contraseña"
    $("#index-mensaje-generarClave").on("click", function () {
        var usuario = $("#index-nombre-usuario").val().trim() + '';

        if (!usuario) {
            mostrarMensaje('<b>Falta la identificación del usuario</b>', '#index-mensaje-inicio');
        } else {
            $.blockUI({message: getMensaje('Enviando nueva contraseña a su correo')});

            $.post("controlador/fachada.php", {
                clase: 'Usuario',
                oper: 'nuevoPassword',
                id_usuario: usuario
            }, function (data) {
//                if (!data.ok) {
    mostrarMensaje('<b>' + data.mensaje + '</b>', '#index-mensaje-inicio');
//                }
}, 'json').always(function () {
    $.unblockUI();
});
}
});

    // Gestión del evento "Cambiar contraseña"
    $("#index-mensaje-cambiarClave").on("click", function () {
        var usuario = $("#index-nombre-usuario").val().trim();

        if (usuario.length === 0) {
            mostrarMensaje('<b>Falta la identificación del usuario</b>', '#index-mensaje-inicio');
        } else {
            // ajustes para el formulario de cambio de contraseña
            $("#index-frmcambio-password").estiloFormulario({
                anchoEtiquetas: '170px',
                anchoEntradas: '195px',
                claseFormulario: 'divForm'
            });
            
            $('#index-frmcambio-password').dialog({
                autoOpen: true,
                width: 470, // aproximadamente anchoEtiquetas + anchoEntradas + 110
                modal: true,
                open: function () {
                    $("#index-actual-id").val($("#index-nombre-usuario").val().trim());
                    $("#index-actual-password").focus();
                },
                close: function () {
                    $(this).dialog("close");
                }
            });
            var anchoLista2 = ($('#index-frmcambio-password > ol > li').width() - 6) + 'px';
            $("#index-cambiar-password").button().css('width', anchoLista2);
        }
    });
    
    // el cambio de password propiamente dicho
    $("#index-cambiar-password").button().on("click", function () {
        var password1 = $("#index-nuevo-password1").val().trim() + '';
        var password2 = $("#index-nuevo-password2").val().trim() + '';

        if (passwordSeguro(password1)) {
            if (password2.localeCompare(password1) === 0) {
                $.blockUI({message: getMensaje('Notificando el cambio realizado')});

                $.post("controlador/fachada.php", {
                    clase: 'Usuario',
                    oper: 'cambiarPassword',
                    id_usuario: $("#index-actual-id").val().trim() + '',
                    actualPassword: MD5($("#index-actual-password").val().trim() + ''),
                    nuevoPassword: MD5(password1)
                }, function (data) {
                    if (!data.ok) {
                        mostrarMensaje('<b>' + data.mensaje + '<b>', "#index-mensaje-cambio");
                    } else {
                        mostrarMensaje('<b>' + data.mensaje + '<b>', "#index-mensaje-cambio");
                        //$('#index-frmcambio-password').dialog("close");
                    }
                }, 'json').always(function () {
                    $.unblockUI();
                });
            } else {
                mostrarMensaje('<b>Las nuevas contraseñas no coinciden<b>', "#index-mensaje-cambio");
            }
        } else {
            mostrarMensaje('<b>La nueva contraseña es insegura<b>', "#index-mensaje-cambio");
        }
    });

    /**
     * Validar que el password sea "seguro"
     * @param {type} password La cadena que debe cumplir con los criterios
     * @returns {RegExp|Boolean} True si contiene minúsculas y mayúsculas, números y caracteres especiales
     */
    function passwordSeguro(password) { // Debe contener minúsculas y mayúsculas, números y caracteres especiales
        return password.length > 7 && // Al menos 8 caracteres 
                /[a-z]/.test(password) && // Al menos una letra minúscula (az) 
                /[A-Z]/.test(password) && // Al menos una letra mayúscula (AZ)
                /\d/.test(password) && // Al menos un número (0-9) 
                /[!"#$%&'()*+.\/:;<=>?@\[\\\]^_`{|}~-]/.test(password); // Al menos un símbolo especial
            }

    // Gestión del evento "Activar mi cuenta" para un usuario existente pero que nunca a ingresado al sistema
    $("#index-activar-cuenta").button().on("click", function () {
        var usuario = $("#index-nombre-usuario").val().trim() + '';
        var contrasena = $("#index-contrasena").val()+'';

        if (!usuario) {
            mostrarMensaje('<b>Falta la identificación del usuario</b>', '#index-mensaje-inicio');
        } else {
            $.blockUI({message: getMensaje('Enviando respuesta a su correo')});
            $.post("controlador/fachada.php", {
                clase: 'Usuario',
                oper: 'nuevoUsuario', 
                id_usuario: usuario,
                contrasena: contrasena
            }, function (data) {
                mostrarMensaje('<b>' + data.mensaje + '<b>', '#index-mensaje-inicio');
                if (!data.ok) {
                    mostrarMensaje('<b>' + data.mensaje + '<b>', '#index-mensaje-inicio');
                } else {
                    // decirle algo al usuario pero no con alert(...)
                }
            }, 'json').always(function () {
                $.unblockUI();
            });
        }
    });

    $("#cerrarSesion").click(function(){
        $.post("controlador/fachada.php", {
        clase: 'Usuario',
        oper: 'cerrarSesion'
    }, function (data) {
        console.log(data);
    }, 'json');
    });
    
    // hasta aquí las definiciones para manejo de usuarios y sesiones

    // un ejemplo de uso de selectores jQuery para controlar eventos sobre links
    $("#index-menu-superior li a").each(function () {
        var opcion = $(this).text();
        $(this).on('click', function (event) {
            switch (opcion) {
                case "La Patria":
                window.open('http://www.lapatria.com/');
                break;
                case "Manizales":
                window.open('http://www.lapatria.com/manizales');
                break;
                case "Actualidad":
                window.open('http://www.lapatria.com/actualidad');
                break;
                default:
                alert('La opción <' + opcion + '> no está disponible');
            }
            event.preventDefault();
        })
    })  // fin de $("#index-menu-superior li a").each(function () {...})

    // otro ejemplo de uso de selectores jQuery para controlar eventos sobre links
    $("#index-pie_pagina a").each(function () {
        var opcion = $(this).text();

        $(this).on('click', function (event) {
            switch (opcion) {
                default:
                alert('La opción <' + opcion + '> no está disponible');
            }
            event.preventDefault();
        });
    });

    // ejemplo de llamado de una instrucción $.post
    $.post("controlador/fachada.php", {
        clase: 'UtilConexion',
        oper: 'getEstadosProduccion'
    }, function (estados) {
        console.log(estados);
        estadosProduccion = estados;
    }, 'json');
    
    $.post("controlador/fachada.php", {
        clase: 'UtilConexion',
        oper: 'getTipoMaquina'
    }, function (tipo) {
        console.log(tipo);
        tipoMaquina = tipo;
    }, 'json');

    $.post("controlador/fachada.php", {
        clase: 'UtilConexion',
        oper: 'getCategoria'
    }, function (categoria) {
        console.log(categoria);
        categoriaTipo = categoria;
    }, 'json');

    // cada que se redimensione el navegador se actualiza anchoContenedor
    $(window).on('resize', function () {
        anchoContenedor = $(window).width() - 220;
        console.log('ancho usable: ' + anchoContenedor)
        $('.ui-jqgrid-btable').each(function () {
            $(this).jqGrid('setGridWidth', anchoContenedor);
        });
    });

});

/**
 * Carga el contenido de una página sobre un elemento del DOM
 * @param {type} contenedor el elemento sobre el que se mostrará la página html
 * @param {type} url la dirección de la página html que será mostrada
 */
 function cargarPagina(contenedor, url) {
    $(contenedor).load(url, function (response, status, xhr) {
        if (status === "error") {
            alert("Lo siento. Error " + xhr.status + ": " + xhr.statusText);
        }
    });
}

/**
 * Esta función se requiere a nivel global para procesar la respuesta que recibe un objeto jqGrid desde el servidor
 * @param {type} response Una cadena JSON con el estado y el mensaje que envía el servidor luego de procesar una acción
 * @param {type} postdata Los datos que envía jqGrid al servidor
 * @returns {Array} La respuesta del estado de la operación para mostrarla como error si fuese necesario
 */
 function respuestaServidor(response, postdata) {
    var respuesta = jQuery.parseJSON(response.responseText);
    console.log(respuesta);
    return [respuesta.ok, "El servidor no pudo completar la acción"];
}

/**
 * Ejemplo de uso: $("#idSelect").getSelectList({clase:'NombreClase', oper:'getSelect'});
 * @param {type} parametros
 * @returns {jQuery.fn.getSelectList|jQuery.fn.getSelectList.combo}
 */
 jQuery.fn.getSelectList = function (parametros) {
    var combo = this;
    var asincrono = ("async" in parametros) ? parametros['async'] : false;
    var aviso = ("aviso" in parametros) ? parametros['aviso'] : false;

    if (!("id" in parametros)) {
        parametros['id'] = $(this).attr('id');
    }
    if (!("dataType" in parametros)) {
        parametros['dataType'] = 'json';
    }

    $.ajax({
        type: "POST",
        url: "controlador/fachada.php",
        beforeSend: function (xhr) {
            if (aviso) {
                // $.blockUI({message: getMensaje(aviso)});
            }
        },
        data: parametros,
        async: asincrono,
        tipoRetorno: parametros['dataType']  // [xml|html|json|jsonp|text]
    }).done(function (data) {
        combo.html(data);
    }).fail(function () {
        console.log("Error de carga de datos: " + JSON.stringify(parametros));
        alert("Error de carga de datos");
    }).always(function () {
        if (aviso) {
            // $.unblockUI();
        }
    });
    return combo;
};

/**
 * Retorna una lista de elementos creados a partir de una tabla
 * @param {object} parametros clase, operacion y argumentos adicionales de la forma {p1:v1, .. pN:vN}, incluso el parámetro asincrono[true|false] por defecto false
 * @returns Object Un objeto con la lista de la forma {id1:elemento1, .. idN:elementoN}
 */
 function getElementos(parametros) {
    var asincrono, aviso, elementos = new Object(), tipoDatos, url;
    aviso = ("aviso" in parametros) ? parametros['aviso'] : false;
    asincrono = ("async" in parametros) ? parametros['async'] : false;
    tipoDatos = ("tipoDatos" in parametros) ? parametros['tipoDatos'] : "json";
    url = ("url" in parametros) ? parametros['url'] : "controlador/fachada.php";

    $.ajax({
        type: "POST",
        url: url,
        beforeSend: function (xhr) {
            if (aviso) {
                // $.blockUI({message: getMensaje(aviso)});
            }
        },
        data: parametros,
        async: asincrono,
        dataType: tipoDatos
    }).done(function (data) {
        elementos = data;
    }).fail(function () {
        console.log("Error de carga de datos: " + JSON.stringify(parametros));
        alert("Error de carga de datos");
    }).always(function () {
        if (aviso) {
            // $.unblockUI();
        }
    });
    return elementos;
}

/**
 * Permite formatear un formulario con un estilo presentable.
 * @param {object} valoresEstilos Diferentes atributos que pueden ser aplicados.
 * @returns {jQuery.fn.estiloFormulario}
 * Ejemplo de la llamada:
 *      $("#formulario-cliente").estiloFormulario({
 *          'claseFormulario': 'box',
 *          'anchoFormulario': ...
 *      });
 */
 jQuery.fn.estiloFormulario = function (valoresEstilos) {
    var div = this;
    jQuery(this).each(function () {
        var idDiv = $(this).attr('id');
        var item;
        if ($('#' + idDiv + '> ol').length) {
            item = '#' + idDiv + '>ol>li>';
        } else {
            item = '#' + idDiv + '>';
        }

        var estilo = {
            'claseFormulario': '',
            'anchoFormulario': '700px',
            'anchoEtiquetas': '100px',
            'anchoEntradas': '550px',
            'alturaTextArea': '90px',
            'tamanioFuente': '100%',
            'fondo': "url('vista/imagenes/fondo1.jpg') repeat"
        };
        if (typeof (valoresEstilos) === 'object') {
            if (estilo.anchoFormurio === valoresEstilos && estilo.anchoFormulario !== '700px') {
                valoresEstilos = 0
            }
            estilo = $.extend(true, estilo, valoresEstilos);
        }

        $('#' + idDiv).addClass(estilo.claseFormulario);
        $(this).css({
            "font-size": estilo.tamanioFuente,
            "font-family": "Helvetica, sans-serif",
            "width": estilo.anchoFormulario,
            "margin-top": "5px",
            "background": estilo.fondo
        });
        $(item + 'input,' + item + 'textarea,' + item + 'select').css({
            'padding': '5px',
            'width': estilo.anchoEntradas,
            'font-family': 'Helvetica, sans-serif',
            'font-size': estilo.tamanioFuente,
            'margin': '0px 0px 2px 0px',
            'border': '1px solid #ccc'
        });
        $('#' + idDiv + ' :button').css({
            'width': (parseInt(estilo.anchoEntradas) + 11) + 'px'
        });
        // es raro...el ancho de los select no guarda la misma proporción de los otros componentes y hay que hacer ajustes
        $(item + 'select').each(function () {
            if (typeof this.attributes['multiple'] === 'undefined') {
                $(this).css({'width': (parseInt(estilo.anchoEntradas)) + 'px', 'display': 'block'});
            } else {
                // suponiendo un select formateado con el plugin multiselect de Eric Hynds
                $(this).css('width', (parseInt(estilo.anchoEntradas) + 6) + 'px');
            }
        });
        $(item + 'textarea').css("height", estilo.alturaTextArea);
        $(item + 'input,' + item + 'textarea,' + item + 'select').on('focus', function () {
            $(this).css("border", "1px solid #900");
        });
        $(item + 'input,' + item + 'textarea,' + item + 'select').on('blur', function () {
            $(this).css("border", "1px solid #ccc");
        });
        $(item + 'label').css({
            'float': 'left',
            'text-align': 'right',
            'margin-right': '15px',
            'width': estilo.anchoEtiquetas,
            'padding-top': '5px',
            'font-size': estilo.tamanioFuente
        });
        ////  excluir este tipo en los estilos anteriores por ahora dejarlo así/////////////////////
        $(item + 'input:checkbox').css({
            'margin-top': '10px',
            'width': 10
        });
        $(item + 'input,' + item + 'label,' + item + 'button,' + item + 'textarea').css('display', 'block');
    });
    return div;
};

/**
 * Muestra un mensaje por unos segundos...
 * @param {type} mensaje El texto del mensaje
 * @param {type} elemento El DIV que contendrá el mesaje
 * @returns {undefined}
 */
 function mostrarMensaje(mensaje, elemento) {
    mensaje = mensaje + '<br>';
    $(elemento).html(mensaje).show().effect("highlight", {color: '#FA5858'}, 4000).promise().then(function () {
        $(this).hide();
    });
}

/**
 * Devuelve un mensaje formateado para bloquear el sistema mediante BlockUI
 * @param {type} mensaje El texto que se va a formatear
 * @returns {String} el HTML de la cadena formateada
 */
 function getMensaje(mensaje) {
    return '<h4><img src="vista/imagenes/ajax-loader.gif"><br>' + mensaje + '<br>Por favor espere...</h4>';
}

/**
 * Verifica que una lista de correos esté escrita correctamente y devuelve la lista separada por comas
 * @param {type} valor
 * @param {type} nombreColumna
 * @returns {Array}
 */
 function validarCorreo(valor, nombreColumna) {
    valor = valor.toLowerCase();
    var correos = new Array();
    var tmp = new Array();
    var tmp2 = new Array();
    if (valor.contains(',')) {
        tmp = valor.split(',');
        for (i = 0; i < tmp.length; i++) {
            correos.push(tmp[i]);
        }
    }
    if (valor.contains(';')) {
        if (correos.length > 0) {
            for (i = 0; i < correos.length; i++) {
                if (correos[i].contains(';')) {
                    tmp = correos[i].split(';');
//                    correos.splice.apply(correos,tmp2.unshift(i+1,tmp2.length));
correos.splice(i, 1);
for (var j = i; j < tmp.length + 1; j++) {
    correos.splice(j, 0, tmp[j - i]);
}
}
}
} else {
    tmp = valor.split(';');
    for (i = 0; i < tmp.length; i++) {
        correos.push(tmp[i]);
    }
}
}
if (valor.contains(' ')) {
    if (correos.length > 0) {
        for (i = 0; i < correos.length; i++) {
            if (correos[i].contains(' ')) {
                tmp = correos[i].split(' ');
//                    correos.splice.apply(correos,tmp2.unshift(i+1,tmp2.length));
correos.splice(i, 1);
for (var j = i; j < tmp.length + 1; j++) {
    correos.splice(j, 0, tmp[j - i]);
}
}
}
} else {
    tmp = valor.split(' ');
    for (i = 0; i < tmp.length; i++) {
        correos.push(tmp[i]);
    }
}
}
if (correos.length === 0) {
    correos.push(valor);
}


var filtro = /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/;
var i;
for (i = 0; i < correos.length; i++) {
    correos[i] = jQuery.trim(correos[i])
    if (correos[i]) {
        if (!filtro.test(correos[i])) {
            return [false, " " + nombreColumna + " : Con errores "];
        }
    }
}
$("#" + nombreColumna).val(correos.join(' '));
return [true, ""];



}

/*
 *  SliderNav - A Simple Content Slider with a Navigation Bar
 *  Copyright 2010 Monjurul Dolon, http://mdolon.com/
 *  Released under the MIT, BSD, and GPL Licenses.
 *  More information: http://devgrow.com/slidernav
 *  Notas: se adaptó la funcionalidad a casillas de selección
 *         Corregido un bug y cambiada la salida del debug por Carlos Cuesta
 */
 $.fn.sliderNav = function (options) {
    var defaults = {
        items: ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"],
        debug: false,
        height: null,
        arrows: true
    };
    var opts = $.extend(defaults, options);
    var o = $.meta ? $.extend({}, opts, $.data()) : opts;
    var slider = $(this);
    $(slider).addClass('slider');
    $('.slider-content li:first', slider).addClass('selected');
    $(slider).append('<div class="slider-nav"><ul></ul></div>');
    for (var i = 0; i < o.items.length; i++) {  // C. Cuesta tuvo que cambiar la instrucción in por esta, ¿por qué? - no se sabe porqué aparece un elemento más
        $('.slider-nav ul', slider).append("<li><a alt='#" + o.items[i] + "'>" + o.items[i] + "</a></li>");
    }
    var height = $('.slider-nav', slider).height();
    if (o.height)
        height = o.height;
    $('.slider-content, .slider-nav', slider).css('height', height);

    $('.slider-nav a', slider).on('mouseover', function (event) {
        var target = $(this).attr('alt');
        var cOffset = $('.slider-content', slider).offset().top;
        var tOffset = $('.slider-content ' + target, slider).offset().top;  // Error si los id de las sublistas <> $('#x')...sliderNav({ ... items:['x', 'y', 'z'], ...
        var height = $('.slider-nav', slider).height();
        if (o.height)
            height = o.height;
        var pScroll = (tOffset - cOffset) - height / 8;

        $('.slider-content li', slider).removeClass('selected');
        $(target).addClass('selected');
        $('.slider-content', slider).stop().animate({
            scrollTop: '+=' + pScroll + 'px'
        });
        if (o.debug)
            console.log('Error' + tOffset);
    });
    if (o.arrows) {
        $('.slider-nav', slider).css('top', '20px');
        $(slider).prepend('<div class="slide-up end"><span class="arrow up"></span></div>');
        $(slider).append('<div class="slide-down"><span id="slide-abajo" class="arrow down"></span></div>');

        $('.slide-down', slider).on('click', function () {
            $('.slider-content', slider).animate({
                scrollTop: "+=" + height + "px"
            }, 500);
        });
        $('.slide-up', slider).on('click', function () {
            $('.slider-content', slider).animate({
                scrollTop: "-=" + height + "px"
            }, 500);
        });
    }
};

/**
 * Inicializa el plugin para temporizar el cierre de sesión por inactividad del usuario
 * Tiene asociadas las scripts: jquery.idletimer.js y jquery.idletimeout.js
 * Ver: https://github.com/ehynds/jquery-idle-timeout/wiki || https://github.com/ehynds/jquery-idle-timeout
 * Este plugin referencia a un dialog cuyo div (idle-timeout-dialog) está definido en index.html  
 */
 function initIdleTimeout() {
    $.idleTimeout('#idle-timeout-dialog', 'div.ui-dialog-buttonpane button:first', {
        idleAfter: 3600,                              // tiempo en segundos, de inactividad permitida
        warningLength: 20,                          // tiempo en segundo que se muestra la advertencia de cierre de sesión
        pollingInterval: 3600,                         // intervalo de chequeo
        keepAliveURL: 'controlador/fachada.php',    // url para llamar y mantener la sesión activa mientras el usuario está activo
        data: {                                     // modifiqué jquery.idletimeout.js para poder enviar parámetros adicionales
            'clase': 'Usuario',
            'oper': 'verificar'
        },
        serverResponseEquals: 'OK',
        failedRequests: 2,
        onTimeout: function () {
           $.post("controlador/fachada.php", {
            clase: 'Usuario', 
            oper: 'cerrarSesion'
        }, function () {
//                window.location.href = "index.html";    Cambiar por llamado autenticar

});
       },
       onIdle: function () {
        $(this).dialog("open");
    },
    onCountdown: function (counter) {
            $("#dialog-cuenta-regresiva").html(counter); // actualizar el contador
        },
        onAbort: function () {
            console.log('se abortó porque no llegó la respuesta esperada');
            window.location.href = "index.html";
        }
    });
    
    
}
