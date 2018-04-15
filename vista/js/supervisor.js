/* 
 * Permite la actualización de la información de órdenes de producción
 * Demostración de las posibilidades más usuales de un elemento jqGrid
 */

$(function () {

    $(window).resize(); // forzar un resize para detectar el ancho del contenedor (ver index.js)
    var anchoGrid = anchoContenedor; // se asigna a una variable local el ancho del contenedor
    var altoGrid = $(window).height() - 350;

    if (altoGrid < 200) {
        altoGrid = 200;
    }

    var clase = 'Supervisor';  // la clase que implementa el CRUD para este grid
    var idPager = 'supervisor-pager';  // la barra de navegación del grid ubicada en la parte inferior

    // las columnas de un grid se definen como un array de objetos con múltiples atributos
    var columnas = [
        {'label': 'Identificación', name: 'id_usuario', align: 'center', index: 'fk_usuario', width: 100, sortable: true, editable: true,
            editrules: {required: true, custom: true, custom_func: validarPersona},
            editoptions: {dataInit: asignarAncho}
        },
        {'label': 'Nombres', name: 'nombres', align: 'center', index: 'nombres', width: 130, sortable: true, editable: true,
            editrules: {required: true, custom: true, custom_func: validarPersona},
            editoptions: {dataInit: asignarAncho}
        },
        {'label': 'Apellidos', name: 'apellidos', index: 'apellidos', align: 'center', width: 130, sortable: true, editable: true,
            editrules: {required: true, custom: true, custom_func: validarPersona},
            editoptions: {dataInit: asignarAncho}
        },
        {'label': 'Dirección', name: 'direccion', index: 'direccion', align: 'center', width: 150, sortable: true, editable: true,
            editrules: {required: false},
            editoptions: {dataInit: asignarAncho}
        },
        {'label': 'Teléfono', name: 'telefonos', index: 'telefonos', align: 'center', width: 120, sortable: true, editable: true,
            editrules: {required: false},
            editoptions: {dataInit: asignarAncho}
        },
        {'label': 'Correo', name: 'correos', index: 'correos', align: 'center', width: 160, sortable: true, editable: true,
            editrules: {required: false, custom: true, custom_func: validarPersona},
            editoptions: {dataInit: asignarAncho}
        },
        {'label': 'Contraseña', name: 'contrasena', index: 'contrasena', hidden: true, edittype: 'password', width: 180, sortable: true, editable: true,
            editrules: {edithidden: true, required: true},
            editoptions: {dataInit: asignarAncho}
        },
    ];

    // inicializa el grid
    var grid = jQuery('#supervisor-grid').jqGrid({
        url: 'controlador/fachada.php',
        datatype: "json",
        mtype: 'POST',
        postData: {
            clase: clase,
            oper: 'select'
        },
        rowNum: 10,
        rowList: [10, 20, 30],
        colModel: columnas,
        autowidth: false,
        shrinkToFit: false,
        sortname: 'fk_usuario',
        sortorder: "asc",
        height: altoGrid,
        width: anchoGrid,
        pager: "#" + idPager,
        viewrecords: true,
        caption: "Supervisor",
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
    });

    // inicializa los elementos de la barra de navegación del grid
    grid.jqGrid('navGrid', "#" + idPager, {
        refresh: true,
        edit: true,
        add: true,
        del: true,
        view: false,
        search: true,
        closeOnEscape: false
    }, {// edit
        width: 420,
        modal: true,
        beforeSubmit: function (postdata) {
            // antes de enviar los datos al servidor, se encriptan
            postdata['contrasena'] = MD5(postdata['contrasena'])
        },
        afterSubmit: respuestaServidor
    }, {// add
        width: 420,
        modal: true,
        beforeSubmit: function (postdata) {
            // antes de enviar los datos al servidor, se encriptan
            postdata['contrasena'] = MD5(postdata['contrasena'])
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

    function validarPersona(valor, columna) {
        if (columna === 'Correo') {
            if (valor) {
                return validarCorreo(valor, columna);
            }
        }
        if (columna === 'Identificación') {
            if (valor.length <= 7) {
                return [false, columna + ": Longitud errónea, mínimo 8"];
            } else if (valor.length >= 15) {
                return [false, columna + ": Longitud errónea, máximo 15"];
            }
        }

        if (columna === 'Nombres') {
            if (valor.length <= 2) {
                return [false, columna + ": Longitud errónea, mínimo 3"];
            } else if (valor.length >= 50) {
                return [false, columna + ": Longitud errónea, máximo 50"];
            }
        }

        if (columna === 'Apellidos') {
            if (valor.length <= 2) {
                return [false, columna + ": Longitud errónea, mínimo 3"];
            } else if (valor.length >= 50) {
                return [false, columna + ": Longitud errónea, máximo 50"];
            }
        } else if (columna === 'OtroCampoXxxxxxxxx') {
            // y así sucesivamente
        }
        return [true, ""];
    }


});


