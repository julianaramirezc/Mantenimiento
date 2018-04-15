/* 
 * Permite la actualización de la información de órdenes de producción
 * Demostración de las posibilidades más usuales de un elemento jqGrid
 */

$(function () {
    var gridActividades;

    $(window).resize(); // forzar un resize para detectar el ancho del contenedor (ver index.js)
    var anchoGrid = anchoContenedor-20; // se asigna a una variable local el ancho del contenedor
    var altoGrid = $(window).height() - 350;

    if (altoGrid < 200) {
        altoGrid = 200;
    }

    function getFecha() {
        var fechaTurno = $("#operproduccion-fecha").val();
        return fechaTurno;
    }

    function getMaquina(){
        var maquina = $('#operproduccion-maquina').text();
        return maquina;
    }

    function myelem(value, options) {
        var el = document.createElement("input");
        el.type = "text";
        el.value = value;
        return el;
    }

    $("#operproduccion-fecha").datepicker(initDatePicker).on("input change", function (e) {
        console.log("Date change: ", e.target.value);
        actualizarGridActividades();
    });

    $("#operproduccion-orden").on("input change", function (e) {//////
        actualizarGridActividades();
    });
    $("#operproduccion-maquina").html(getElementos({'clase': 'Maquina', 'oper': 'getSelect', 'json': true})).on("change", function () {
        actualizarGridActividades();///////
    });

    $("#operproduccion-operarios").html(getElementos({'clase': 'Operario', 'oper': 'getSelect', 'json': true})).on("change", function () {
        actualizarGridActividades();
    });
    // ejemplo de llamado de una instrucción $.post
    var tiposOperaciones;
    $.post("controlador/fachada.php", {
        clase: 'TipoOperacion',
        oper: 'getArray'
    }, function (data) {
        tiposOperaciones = data;
    }, 'json');

    var clase = 'OperacionProduccion';  // la clase que implementa el CRUD para este grid
    var idPager = 'operacion_produccion-pager';  // la barra de navegación del grid ubicada en la parte inferior

    // las columnas de un grid se definen como un array de objetos con múltiples atributos
    var columnas = [
        {'label': 'Orden de producción', name: 'orden_produccion', index: 'orden_produccion', align: "center", width: 120, sortable: true, hidden: false, editable: false,
            editrules: {edithidden: false, required: false},
            editoptions: {dataInit: asignarAncho}
        },
        {'label': 'Operario', name: 'fk_operario', index: 'fk_operario', width: 120, align: "center", sortable: true, editable: true, edittype: 'select',
            editrules: {required: true, custom: true, custom_func: validarOperacion}, // ver la codificación de esta función al final del código
            editoptions: {
                dataUrl: 'controlador/fachada.php?clase=Operario&oper=getSelect',
                dataInit: asignarAncho,
                defaultValue: '0'
            }
        },
        {'label': 'Hora Inicio', name: 'hora_inicio', index: 'hora_inicio', align: "center", width: 150, sortable: true, hidden: false, editable: false,
            editrules: {edithidden: false, required: false},
            editoptions: {dataInit: asignarAncho}
        },
        {'label': 'Hora Fin', name: 'hora_fin', index: 'hora_fin', width: 150, align: "center", sortable: true, hidden: false, editable: false,
            editrules: {edithidden: false, required: false},
            editoptions: {dataInit: asignarAncho}
        },
        {'label': 'Duración', name: 'duracion', index: 'duracion', width: 80, align: "center", sortable: true, editable: true,
            editrules: {required: true, custom: true, custom_func: validarOperacion},
            editoptions: {dataInit: asignarAncho}
        },
        {'label': 'Máquina', name: 'maquina', index: 'fk_maquina', align: "center", width: 100, sortable: true, hidden: false, editable: false, edittype: 'custom',
            editrules: {edithidden: false, required: false},
            editoptions: {dataInit: asignarAncho}
        },
        {'label': 'Actividad', name: 'fk_tipo_operacion', index: 'fk_tipo_operacion', align: "left", width: 150, sortable: true, editable: true, edittype: 'text',
            editrules: {custom: true, custom_func: validarOperacion, required: true}, // ver la codificación de esta función al final del código
            editoptions: {
                dataInit: function (element) {
                    $(element).width(260);
                    window.setTimeout(function () {
                        $(element).autocomplete({
                            id: 'operacion-autocomplete',
                            source: tiposOperaciones,
                            minLength: 1, // mínima longitud para empezar la búsqueda
                            autoFocus: true
                        });
                    }, 100);
                }
            }
        },
        {'label': 'Fecha', name: 'fecha', index: 'fecha', width: 80, sortable: true, hidden: true, editable: true, edittype: 'custom', editrules: {edithidden: false, required: false},
            editoptions: {dataInit: asignarAncho, custom_element: myelem, custom_value: getFecha}
        }
    ];
    actualizarGridActividades();

    function actualizarGridActividades() {
        var fechaTurno = $("#operproduccion-fecha").val();
        var maquina = $('#operproduccion-maquina option:selected').text();
        var idOperario = $('#operproduccion-operarios').val();
        var Orden_produccion = $("#operproduccion-orden").val();
        var titulo = 'Gestion de actividades de producción';



        if (gridActividades) {
            console.log("refresh");
            //console.log(fechaTurno + ' ' + turno + ' ' + idOperario);
            var titulo = 'Gestión de actividades de producción';
            gridActividades.jqGrid('setGridParam', {
                postData: {
//                    'fechaTurno': fechaTurno,
//                    'maquina': maquina,
//                    'idOperario': idOperario,
//                    'orden_produccion': Orden_produccion
                }
            }).setCaption(titulo).trigger("reloadGrid");
            //console.log("hola papi");
            return;
        }
        console.log("creando");
        console.log(maquina);
        gridActividades = jQuery('#operacion_produccion-grid').jqGrid({
            url: 'controlador/fachada.php',
            datatype: "json",
            mtype: 'POST',
            postData: {
                clase: clase,
                oper: 'select',
//                'fechaTurno': fechaTurno,
                //'maquina': maquina,
                //'idOperario': idOperario,
                //'orden_produccion': Orden_produccion
            },
            
            rowNum: 50,
            rowList: [100, 200, 300],
            colModel: columnas,
            autowidth: false,
            shrinkToFit: false,
            sortname: 'id_operacion',
            sortorder: "asc",
            height: altoGrid,
            width: anchoGrid,
            pager: "#" + idPager,
            viewrecords: true,
            caption: titulo,
            multiselect: false,
            multiboxonly: true,
            hiddengrid: false,
            gridComplete: function () {
                // hacer algo...
            },
            loadError: function (jqXHR, textStatus, errorThrown) {
                alert('Error. No se tiene acceso a los datos de órdenes de producción.')
                console.log('textStatus: ' + textStatus);
                console.log(errorThrown);
                console.log(jqXHR.responseText);
            },
            editurl: "controlador/fachada.php?clase=" + clase
        }).jqGrid('navGrid', "#" + idPager, {
            refresh: true,
            edit: true,
            add: true,
            del: true,
            view: false,
            search: true,
            closeOnEscape: false
        }, {// edit
            width: 480,
            modal: true,
            beforeSubmit: function (postdata) {
            postdata['orden_produccion'] = $("#operproduccion-orden").val();
            postdata['maquina']= $('#operproduccion-maquina').val();
        },
            afterSubmit: respuestaServidor
        }, {// add
            width: 480,
            modal: true,
            beforeSubmit: function (postdata) {
            postdata['orden_produccion'] = $("#operproduccion-orden").val();
            postdata['maquina']= $('#operproduccion-maquina').val();
        },
            afterSubmit: respuestaServidor
        }, {// del
            width: 335,
            modal: true, // jqModal: true,
            afterSubmit: respuestaServidor
        }, {// búsqueda
            multipleSearch: true,
            multipleGroup: true}, {}
        );
    }

    /**
     * Asigna ancho a un elemento del grid
     * @param {type} elemento El nombre del elemento 
     * @returns {undefined}
     */
    function asignarAncho(elemento) {
        $(elemento).width(260);
    }

    /**
     * Validación personalizada de los campos de un jqGrid
     * @param {type} valor el dato contenido en un campo
     * @param {type} columna nombre con que está etiquetada la columna
     * @returns {Array} un array indicando si la validación fue exitosa o no
     */

    function validarOperacion(valor, columna)
    {
        if (columna === 'Operario') {
            if (valor == 0) {
                return [false, columna + ": Debe seleccionar el operario"];
            }
        }
        if (columna === 'Duracion') {
            if (valor == 0) {
                return [false, columna + ": Debe escribir la duración en minutos"];
            }
        }

        if (columna === 'Actividad') {
            if (valor == 0) {
                return [false, columna + ": Debe seleccionar la actividad"];
            }
        }
        return [true, ""];


    }

    function validarOtros(columna) {
         $("#fk_tipo_operacion").change(function () {
            if ($("#fk_tipo_operacion").text() == "51 - Otros") {
                alert("asdasdasd");
            }
        });
         $("#fk_tipo_operacion option:selected").text();

    }

    function minutos_a_horas(columna)
    {
        var duracion = (columna == 'duracion');
        var hrs = Math.floor(duracion / 60);
        duracion = duracion % 60;
        if (duracion < 10)
            duracion = "0" + duracion;
        return hrs + ":" + duracion;
    }
});


