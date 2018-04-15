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

    var clase = 'TipoOperacion';  // la clase que implementa el CRUD para este grid
    var idPager = 'tipo_operacion-pager';  // la barra de navegación del grid ubicada en la parte inferior

    // las columnas de un grid se definen como un array de objetos con múltiples atributos
    var columnas = [
        {'label': 'ID', name: 'id_tipo_operacion', index: 'id_tipo_operacion', align:"center", width: 60, sortable: true, editable: true,
            editrules: {required: true,custom:true,custom_func:validarTipo},
            editoptions: {dataInit: asignarAncho}
        },
        {'label': 'Descripción', name: 'descripcion_operacion', index: 'descripcion_operacion', align:"left", width: 150, sortable: true, editable: true,
            editrules: {required: true,custom:true,custom_func:validarTipo},
            editoptions: {dataInit: asignarAncho}
        },
        {'label': 'Categoría', name: 'categoria', index: 'categoria', width: 60, sortable: true, align:"center", editable: true,
            editrules: {custom:true,custom_func:validarTipo}, edittype: 'select',
            editoptions: {
                value: categoriaTipo, // <-- ver en index.js como se obtienen los valores de este select
                dataInit: asignarAncho
            }
        },
    ];

    // inicializa el grid
    var grid = jQuery('#tipo_operacion-grid').jqGrid({
        url: 'controlador/fachada.php',
        datatype: "json",
        mtype: 'POST',
        postData: {
            clase: clase,
            oper: 'select'
        },
        rowNum: 50,
        rowList: [50, 80, 100],
        colModel: columnas,
        autowidth: false,
        shrinkToFit: false,
        sortname: 'id_tipo_operacion',
        sortorder: "asc",
        height: altoGrid,
        width: anchoGrid,
        pager: "#" + idPager,
        viewrecords: true,
        caption: "Tipo Operación Producción",
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
        width: 480,
        modal: true,
        afterSubmit: respuestaServidor
    }, {// add
        width: 480,
        modal: true,
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
    function validarTipo(valor, columna) {
        if (columna === 'Id') {
            if (valor.length <= 1) {
                return [false, columna + ": Longitud errónea, mínimo 2"];
            } else if (valor.length >= 10) {
                return [false, columna + ": Longitud errónea, máximo 10"];
            }
        }
        if (columna === 'Descripción') {
            if (valor.length <= 3) {
                return [false, columna + ": Longitud errónea, mínimo 4"];
            } else if (valor.length >= 30) {
                return [false, columna + ": Longitud errónea, máximo 30"];
            }
        }

        if (columna === 'Categoria') {
            if (valor == 0) {
                return [false, columna + ": Debe Seleccionar la categoría"];
            }
        } else if (columna === 'OtroCampoXxxxxxxxx') {
            // y así sucesivamente
        }
        return [true, ""];
    }

});


