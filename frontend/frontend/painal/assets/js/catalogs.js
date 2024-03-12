function deleteCatalog(tokencatalog, namecatalog, father, fathername) {
    console.log(tokencatalog);
    swal({
        title: "Confirmación",
        text: "¿Seguro que deseas eliminar " + namecatalog + "?",
        icon: "warning",
        buttons: true,
        dangerMode: false,
    })
        .then(json => {
            if (json) {
                $.ajax({
                    url: 'models/pubsub/deleteCatalog.php',
                    type: 'POST',
                    dataType: "json",
                    data: { key: tokencatalog }
                })
                    .done(function (res) {
                        console.log(res);
                        if (res.code == 1) {
                            toastr.error('Error al eliminar ' + namecatalog);
                        } else {
                            swal('Ok', res.message, 'success');
                            var table = $('#table-files').DataTable();
                            table.clear().draw();
                            getcatalogstree(false);
                            getsubcatalogs(father, fathername);
                        }

                    })
                    .fail(function (res) {
                        console.log(res);
                        toastr.error('Error al eliminar ' + namecatalog);
                    });
            } else {
                swal("Cancelated", "");
            }
        });
}

function getsubcatalogs(tokencatalog, textfather) {
    console.log(tokencatalog, textfather);
    $.ajax({
        method: 'GET',
        url: 'models/pubsub/get_catalogs.php?cat=' + tokencatalog,
        data: {},
        dataType: "json",
        // Esta funcion se ejecuta antes de enviar la información al archivo indicado en el parametro url
        beforeSend: function () {
            //toastr.info('Obteniendo catálogos');
            //$('#load').show();
        }
    }).done(function (res) {
        if (res.code == 0) {
            //toastr.success('Catálogos obtenidos');
            console.log(res);
            catalogs = res.data;
            files = res.files;
            console.log("SUB " + catalogs);
            console.log(catalogs);
            var t = $('#table-files').DataTable();
            var counter = 1;
            catalogs.forEach(e => {
                console.log(e.namecatalog, textfather);
                row1 = '<a style="cursor: pointer" onclick="showContents(\'' + e.tokencatalog + '\',\'' + e.namecatalog + '\')" class="d-flex align-items-center">\
                    <figure class="avatar avatar-sm mr-3">\
                        <span class="avatar-title bg-warning text-black-50 rounded-pill">\
                            <i class="ti-folder"></i>\
                        </span>\
                    </figure>\
                    <span class="d-flex flex-column">\
                        <span class="text-primary">'+ e.namecatalog.split(textfather + "/").pop() + '</span>\
                    </span>\
                </a>';
                lastrow = '<div class="dropdown">\
                <a href="#" class="btn btn-floating" data-toggle="dropdown">\
                    <i class="ti-more-alt"></i>\
                </a>\
                <div class="dropdown-menu dropdown-menu-right">\
                    <a href="#" class="dropdown-item" data-sidebar-target="#view-detail">View\
                        Details</a>\
                    <a href="#" class="dropdown-item">Share</a>\
                    <a onclick="deleteCatalog(\''+ e.tokencatalog + '\',\'' + e.namecatalog + '\',\'' + tokencatalog + '\',\'' + textfather + '\')" class="dropdown-item" style="cursor: pointer">Delete</a>\
                </div>\
            </div>';

                t.row.add([row1, e.created_at, e.status, '', e.group, lastrow]).draw(false);
            });

            files.forEach(e => {
                console.log(e);
                row1 = '<a href="showfile.php?file='+e.keyfile+'&catalog='+tokencatalog+'" class="d-flex align-items-center">\
                            <figure class="avatar avatar-sm mr-3">\
                                <span class="avatar-title rounded-pill">\
                                    <i class="ti-file"></i>\
                                </span>\
                            </figure>\
                            <span class="d-flex flex-column">\
                                <span class="text-primary">'+ e.namefile + '</span>\
                            </span>\
                        </a>';
                lastrow = '<div class="dropdown">\
                                <a href="#" class="btn btn-floating" data-toggle="dropdown">\
                                    <i class="ti-more-alt"></i>\
                                </a>\
                                <div class="dropdown-menu dropdown-menu-right">\
                                    <a href="#" class="dropdown-item" data-sidebar-target="#view-detail">View\
                                        Details</a>\
                                    <a href="#" class="dropdown-item">Share</a>\
                                    <a href="#" class="dropdown-item">Delete</a>\
                                </div>\
                            </div>';

                t.row.add([row1, e.created_at, '', e.sizefile, '', lastrow]).draw(false);
            });


        } else {
            console.log('Error');
        }
    }).fail(function (res) {
        console.log(res);
        //toastr.error('Error');
    });
}

function getcatalogstree(first) {
    $.ajax({
        method: 'GET',
        url: 'models/pubsub/get_catalogs.php',
        data: {},
        dataType: "json",
        // Esta funcion se ejecuta antes de enviar la información al archivo indicado en el parametro url
        beforeSend: function () {
            toastr.info('Obteniendo catálogos');
            //$('#files').html("Cargando catálogos");
        }
    }).done(function (res) {

        if (res.code == 0) {
            toastr.success('Catálogos obtenidos');
            catalogs = res.data;
            var jsonDataCatalogs = {};
            jsonDataCatalogs['data'] = [];
            jsonDataCatalogs['themes'] = {
                dots: false
            };
            res.data.forEach(element => {
                children = [];
                add_childs(element, 1, children)
                folder = {
                    'text': element.namecatalog,
                    'id': element.tokencatalog,
                    'type': 'folder',
                    'children': children
                };
                jsonDataCatalogs['data'].push(folder);
            });

            if (first) {
                $('#files').jstree({
                    'core': jsonDataCatalogs,
                    "types": {
                        "folder": {
                            "icon": "ti-folder text-warning",
                        },
                        "file": {
                            "icon": "ti-file",
                        }
                    },
                    plugins: ["types"]
                });
            } else {
                console.log("RECARGAR");
                $('#files').jstree("destroy");
                $('#files').jstree({
                    'core': jsonDataCatalogs,
                    "types": {
                        "folder": {
                            "icon": "ti-folder text-warning",
                        },
                        "file": {
                            "icon": "ti-file",
                        }
                    },
                    plugins: ["types"]
                });
                $('#files').jstree("refresh");
            }

            $('#files').on("select_node.jstree", function (e, data) {
                console.log(data.node);
                showContents(data.node.id, data.node.text);
            });


            //$('#files').jstree(true).refresh();

            console.log(jsonDataCatalogs);

        } else {
            toastr.error('Error');
        }

    }).fail(function (res) {
        console.log(res);
        toastr.error('Error');
    });
}

function add_childs(cat, level, object) {
    //childs = [];

    for (let i = 0; i < cat.childs.length; i++) {
        var chams = []
        add_childs(cat.childs[i], ++level, chams)
        object.push(
            {
                'text': cat.childs[i].namecatalog.replace(cat.namecatalog + "/", ""),
                'type': 'folder',
                'id': cat.childs[i].tokencatalog,
                'children': chams
            }
        );
    }
}

function showContents(tokencatalog, namecatalog) {
    var table = $('#table-files').DataTable();
    table.clear().draw();
    //$("#tablefilesbody").html("");
    getsubcatalogs(tokencatalog, namecatalog);
}



function delete_group(key, namegroup) {
    swal({
        title: "Confirmar",
        text: "¿Desea eliminar el grupo " + namegroup + "?",
        icon: "warning",
        buttons: true,
        dangerMode: false,
    })
        .then(json => {
            if (json) {
                $.ajax({
                    url: 'models/pubsub/deleteGroup.php',
                    type: 'POST',
                    dataType: "json",
                    data: { key: key }
                })
                    .done(function (res) {
                        if (res.code == 0) {
                            toastr.success(res.message);
                            try {
                                getGroups();
                            } catch (error) {
                                console.error(error);
                                // expected output: ReferenceError: nonExistentFunction is not defined
                                // Note - error messages will vary depending on browser
                            }
                        }else{
                            toastr.error(res.message);
                        }
                    })
                    .fail(function (res) {
                        console.log(res);
                        toastr.error("Error");
                    });
            } else {
                toastr.error("Error");
            }
        });
}

function getGroups() {
    $.ajax({
        method: 'GET',
        url: 'models/pubsub/getgroups.php',
        data: {},
        dataType: "json",
        // Esta funcion se ejecuta antes de enviar la información al archivo indicado en el parametro url
        beforeSend: function () {
            toastr.info('Obteniendo grupos');
            //$('#files').html("Cargando catálogos");
        }
    }).done(function (res) {
        var t = $('#table-groups').DataTable();
        t.clear().draw();
        if (res.code == 0) {
            toastr.success('Grupos obtenidos');
            console.log(res);
            res.groups.forEach(e => {
                var htmlcol3 = "";
                if (e.status == "Owner") {
                    htmlcol3 = '<button type="button" onclick="sharegroup(\'' + e.tokengroup + '\')" class="btn btn-secondary btn-rounded">\
                                    <i class="ti-share"></i>\
                                </button>\
                                <button type="button" onclick="delete_group(\''+ e.tokengroup + '\',\''+ e.namegroup + '\')" class="btn btn-danger btn-rounded">\
                                    <i class="ti-trash"></i>\
                                </button>';
                }
                t.row.add([e.namegroup, e.status, htmlcol3]).draw(false);
            });
        } else {
            toastr.error('Error');
        }

    }).fail(function (res) {
        console.log(res);
        toastr.error('Error');
    });
}

$(function () {


    toastr.options = {
        timeOut: 3000,
        progressBar: true,
        showMethod: "slideDown",
        hideMethod: "slideUp",
        showDuration: 200,
        hideDuration: 200
    };


    /*$('#files').on("select_node.jstree", function (e, data) {
        console.log(data.node);
        showContents(data.node.id, data.node.text);
    });*/

    $("#frmCreateGroup").submit(function (evt) {
        evt.preventDefault();
        var fdata = $("#frmCreateGroup").serialize();
        console.log(fdata);
        $.ajax({
            url: 'models/pubsub/newGroup.php',
            type: 'POST',
            dataType: "json",
            data: fdata,
            beforeSend: function () {
                toastr.info('Creando grupo');
            }
        })
            .done(function (res) {
                console.log(res);
                if (res.code == 0) {
                    toastr.success(res.message);
                    $("#txtGroupName").val("");
                    $('#createGroup').modal('hide');

                    try {
                        getGroups();
                    } catch (error) {
                        console.error(error);
                        // expected output: ReferenceError: nonExistentFunction is not defined
                        // Note - error messages will vary depending on browser
                    }

                } else {
                    toastr.error('Error ' + res.message);
                }
            })
            .fail(function (res) {
                console.log(res);
                //$("#page-body").html("Error");
            });
    });

    $("#frmCreateCatalog").submit(function (evt) {
        evt.preventDefault();
        var catalogName = $("#txtCatalogName").val();
        var group = $("#slGroup").val();
        console.log(catalogName, group);
        $.ajax({
            url: 'models/pubsub/newCatalog_C.php',
            type: 'POST',
            dataType: "json",
            data: { "catalogname": catalogName, "group": group },
            beforeSend: function () {
                toastr.info('Creando catálogo');
            }
        })
            .done(function (res) {
                console.log(res);
                if (res.code == 0) {
                    toastr.success(res.message);
                    $("#txtCatalogName").val("");
                    $('#createCatalog').modal('hide');
                    getcatalogstree(false);
                } else {
                    toastr.error('Error ' + res.message);
                }
            })
            .fail(function (res) {
                console.log(res);
                //$("#page-body").html("Error");
            });
    });

});