$('#login').click(function () {
    // Traemos los datos de los inputs
    var user = $('#email').val();
    var clave = $('#password').val();
    if (user == "" || clave == "") {
        swal('Error', 'Ingrese todos los campos', 'error');
    } else {
        var d = {};
        d['email'] = user;
        d['password'] = clave;
        //d['location'] = window.location;
        //d['origin'] = window.location.origin;
        dd = JSON.stringify(d);
        // Envio de datos mediante Ajax
        $.ajax({
            method: 'POST',
            url: 'models/auth/login_M.php',
            data: $("#login_form").serialize(),
            // Esta funcion se ejecuta antes de enviar la información al archivo indicado en el parametro url
            beforeSend: function () {
                $('#load').html('<div class="col-xs-12 center text-accent">' +
                    '<span>Validando datos...</span>' +
                    '</div>');
                $('#load').show();
            }
        })
            .done(function (res) {
                //console.log(res);
                $('#load').hide();
                if (res == 'ok') {
                    location.reload(true);
                } else {
                    swal('Error', res, 'error');
                }
            })
            .fail(function (res) {
                $('#load').hide();
                if (res) {
                    resJson = res.responseJSON;
                    switch (resJson.message) {
                        case '':
                            swal('Error', '', 'error');
                            break;
                        default:
                            swal('Error', resJson.message, 'error');
                            break;
                    }
                } else {
                    swal('Error', res, 'error');
                }
            });
    }
});


function new_user() {
    var clave = $('#password').val();
    var clave2 = $('#password2').val();
    if (clave == clave2) {
        $.ajax({
            url: '../../models/auth/newUser_M.php',
            type: 'POST',
            dataType: "json",
            data: $("#formulario_registro").serialize(),
            beforeSend: function () {
                $('#load').html("<img src='../../images/ajax-loader.gif'>");
            }
        })
            .done(function (res) {
                $('#load').html('');
                if (res) {
                    //swal('Ok', 'User created, please check your email.', 'success')
                    console.log(res);
                    if (res['codigo'] != 0) {
                        swal('Error', res['message'], 'error')
                            .then((value) => {
                                //window.location.assign("../../");
                            });
                    } else {
                        swal('Ok', res['message'], 'success')
                            .then((value) => {
                                window.location.assign("../../");
                            });
                    }

                } else {
                    swal('Error', '', 'error');
                }
            })
            .fail(function (res) {
                console.log(res);
                //$('#load').html('');
                swal('Error', 'Something went wrong.', 'error');
            });
    } else {
        swal('Error', 'Passwords dont match.', 'error');
    }
}

function new_org() {
    $.ajax({
        url: '../../models/auth/newOrg.php',
        type: 'POST',
        data: $("#formulario_registro").serialize(),
        beforeSend: function () {
            $('#load').html("<img src='../../images/ajax-loader.gif'>");
        }
    })
        .done(function (res) {
            $('#load').html('');
            if (res) {
                //swal('Ok', 'User created, please check your email.', 'success')
                swal('Ok', res, 'success')
                    .then((value) => {
                        window.location.assign("../../");
                    });
            } else {
                swal('Error', '', 'error');
            }
        })
        .fail(function (res) {
            $('#load').html('');
            swal('Error', 'Something went wrong.', 'error');
        });

}

function menu(opcion) {
    $.ajax({
        url: 'controllers/controller.php',
        type: 'POST',
        data: { opt_form: opcion },
        beforeSend: function () {
            $("#page-body").html("<img src='images/ajax-loader.gif'>");
        }
    })
        .done(function (res) {
            $("#page-body").html(res);
        })
        .fail(function (res) {
            //console.log("error");
        });
    return false;
}

function new_group() {
    var fdata = $("#newGroup_form").serialize();
    $.ajax({
        url: 'models/pub_sub/newGroup.php',
        type: 'POST',
        data: fdata,
        beforeSend: function () {
            //$("#page-body").html("<img src='images/ajax-loader.gif'>");
        }
    })
        .done(function (res) {
            if ($.trim(res) === 'ok') {
                swal('Ok', 'Group created.', 'success')
                    .then((value) => {
                        menu(301);
                    });
            } else {
                swal('Ok', res, 'error');
                //$("#page-body").html("");
            }
        })
        .fail(function (res) {
            // $("#page-body").html("Error");
        });

    return false;
}

function new_catalog() {
    var fdata = $("#newCatalog_form").serialize();
    $.ajax({
        url: 'models/pub_sub/newCatalog_C.php',
        type: 'POST',
        data: fdata,
        beforeSend: function () {
            $("#page-body").html("<img src='images/ajax-loader.gif'>");
        }
    })
        .done(function (res) {
            console.log(res);
            if ($.trim(res) === 'ok') {
                swal('Ok', 'Catalog created.', 'success')
                    .then((value) => {
                        menu(201);
                    });
            } else {
                swal('Error', res, 'error');
                $("#page-body").html("");
            }
        })
        .fail(function () {
            //$("#page-body").html("Error");
        });

    return false;
}


function edit_username() {
    var fdata = $("#editUsername_form").serialize();
    console.log(fdata);
    $.ajax({
        url: 'models/auth/editUsername_M.php',
        type: 'POST',
        data: fdata,
        beforeSend: function () {
            $("#page-body").html("<img src='images/ajax-loader.gif'>");
        }
    })
        .done(function (res) {
            console.log(res);
            if ($.trim(res) === 'ok') {
                menu(10);
            } else {
                $("#page-body").html("Error");
            }
        })
        .fail(function (res) {
            $("#page-body").html("Error");
        });

    return false;
}

function delete_user(key) {
    swal({
        title: "Confirm?",
        text: "",
        icon: "warning",
        buttons: true,
        dangerMode: false,
    })
        .then(json => {
            $.ajax({
                url: 'models/auth/deleteUser.php',
                type: 'POST',
                data: { key: key }
            })
                .done(function (res) {
                    console.log(res);
                    if ($.trim(res) === 'ok') {
                        swal('Ok', 'User deleted.', 'success')
                            .then((value) => {
                                menu(101);
                            });
                    }
                })
                .fail(function (res) {
                    console.log(res);
                });
        });
}

function delete_catalog(key) {
    swal({
        title: "Confirm?",
        text: "",
        icon: "warning",
        buttons: true,
        dangerMode: false,
    })
        .then(json => {
            if (json) {
                $.ajax({
                    url: 'models/pub_sub/deleteCatalog.php',
                    type: 'POST',
                    data: { key: key }
                })
                    .done(function (res) {
                        swal('Ok', res, 'success')
                            .then((value) => {
                                menu(201);
                            });
                    })
                    .fail(function () {
                    });
            } else {
                swal("Cancelated", "");
            }
        });
}

function delete_group(key) {
    swal({
        title: "Confirm?",
        text: "",
        icon: "warning",
        buttons: true,
        dangerMode: false,
    })
        .then(json => {
            if (json) {
                $.ajax({
                    url: 'models/pub_sub/deleteGroup.php',
                    type: 'POST',
                    data: { key: key }
                })
                    .done(function (res) {
                        if ($.trim(res) === 'ok') {
                            swal('Ok', 'Group deleted.', 'success')
                                .then((value) => {
                                    menu(301);
                                });
                        }
                    })
                    .fail(function () {
                    });
            } else {
                swal("Cancelated", "");
            }
        });
}

/*
// FUNCION PARA CAMBIAR EL CORREO DE UN USUARIO
$('#c_email').click(function() {
    var key = document.getElementById('keyuser').value;
    var email = document.getElementById('email').value;
    if (key == '' || email == '') {
        swal('Error', 'Faltan datos.', 'error');
    } else {
        var dd = {};
        dd['keyuser'] = key;
        dd['email'] = email;
        $.ajax({
                url: '/auth',
                type: 'PUT',
                dataType: 'JSON',
                data: JSON.stringify(dd),
                beforeSend: function() {
                    $('#resp').html('...');
                }
            })
            .done(function(res) {
                if (res.message == 'Done.') {
                }
            })
            .fail(function(res) {
                $('#resp').html('');
                resJson = res.responseJSON;
                switch (resJson.message) {
                    case '':
                        swal('Error', '', 'error');
                        break;
                    default:
                        swal('Error', resJson.message, 'error');
                        break;
                }
            });

    }
});

// FUNCION PARA CAMBIAR LA CONTRASEÑA DE UN USUARIO
$('#c_pass').click(function() {
    var key = document.getElementById('keyuser').value;
    var pass = document.getElementById('password').value;
    if (key == '' || pass == '') {
        swal('Error', 'Faltan datos.', 'error');
    } else {
        var dd = {};
        dd['keyuser'] = key;
        dd['password'] = pass;
        $.ajax({
                url: '/auth',
                type: 'PUT',
                dataType: 'JSON',
                data: JSON.stringify(dd),
                beforeSend: function() {
                    $('#resp').html('...');
                }
            })
            .done(function(res) {
                if (res.message == 'Done.') {
                }
            })
            .fail(function(res) {
                console.log(res);
                $('#resp').html('');
                resJson = res.responseJSON;
                switch (resJson.message) {
                    case '':
                        swal('Error', '', 'error');
                        break;
                    default:
                        swal('Error', resJson.message, 'error');
                        break;
                }
            });

    }
});*/

/*function share(key) {
    $.ajax({
        url: 'models/pub_sub/key.php',
        type: 'POST',
        data: { key: key }
    })
        .done(function (res) {
            if ($.trim(res) === 'ok') {
                menu(202);
            }
        })
        .fail(function (res) {
            //console.log("error");
        });
    return false;
}*/

function see_resource(key) {
    console.log(key);

    $.ajax({
        url: 'models/pub_sub/key.php',
        type: 'POST',
        data: { key: key }
    })
        .done(function (res) {
            if ($.trim(res) === 'ok') {
                menu(202);
            }
        })
        .fail(function (res) {
            //console.log("error");
        });
    return false;
}

function send_subscribe(key) {
    swal({
        title: "Confirm?",
        text: "",
        icon: "warning",
        buttons: true,
        dangerMode: false,
    })
        .then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: 'models/pub_sub/sendSubscribe.php',
                    type: 'POST',
                    data: { key: key }
                })
                    .done(function (res) {
                        if ($.trim(res) === 'ok') {
                            swal('Request send', 'Need approval', 'success')
                                .then((value) => {
                                    menu(10);
                                });
                        } else {
                            swal('Error', res, 'error');
                        }
                    })
                    .fail(function (res) {
                        swal('Error', '', 'error');
                    });
            } else {
                swal("Cancelated", "");
            }
        });
}

function allow_notification(key) {
    swal({
        title: "Confirm?",
        text: "",
        icon: "warning",
        buttons: true,
        dangerMode: false,
    })
        .then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: 'models/pub_sub/respondNotification.php',
                    type: 'POST',
                    data: { key: key, status: 2 }
                })
                    .done(function (res) {
                        if ($.trim(res) === 'ok') {
                            swal('Accepted', '', 'success')
                                .then((value) => {
                                    menu(10);
                                });
                        } else {
                            swal('Error', res, 'error');
                        }
                    })
                    .fail(function (res) {
                        swal('Error', '', 'error');
                    });
            } else {
                swal("Cancelated", "");
            }
        });
}

function deny_notification(key) {
    swal({
        title: "Confirm?",
        text: "",
        icon: "warning",
        buttons: true,
        dangerMode: false,
    })
        .then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: 'models/pub_sub/respondNotification.php',
                    type: 'POST',
                    data: { key: key, status: 3 }
                })
                    .done(function (res) {
                        if ($.trim(res) === 'ok') {
                            swal('Deleted', '', 'success')
                                .then((value) => {
                                    menu(10);
                                });
                        } else {
                            swal('Error', res, 'error');
                        }
                    })
                    .fail(function (res) {
                        swal('Error', '', 'error');
                    });
            } else {
                swal("Cancelated", "");
            }
        });
}

function see_entity(key) {
    $.ajax({
        url: 'models/pub_sub/key.php',
        type: 'POST',
        data: { key: key }
    })
        .done(function (res) {
            if ($.trim(res) === 'ok') {
                menu(303);
            }
        })
        .fail(function (res) {
            //console.log("error");
        });
    return false;
}

function subscribe_group(key) {
    swal({
        title: "Confirm?",
        text: "",
        icon: "warning",
        buttons: true,
        dangerMode: false,
    })
        .then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: 'models/pub_sub/sendSubscribeGroup.php',
                    type: 'POST',
                    data: { key: key }
                })
                    .done(function (res) {
                        if ($.trim(res) === 'ok') {
                            swal('Request send', 'Need approval', 'success')
                                .then((value) => {
                                    menu(10);
                                });
                        } else {
                            swal('Error', res, 'error');
                        }
                    })
                    .fail(function (res) {
                        swal('Error', '', 'error');
                    });
            } else {
                swal("Cancelated", "");
            }
        });
}

function allow_notificationGroup(key) {
    swal({
        title: "Confirm?",
        text: "",
        icon: "warning",
        buttons: true,
        dangerMode: false,
    })
        .then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: 'models/pub_sub/respondNotificationGroup.php',
                    type: 'POST',
                    data: { key: key, status: 2 }
                })
                    .done(function (res) {
                        if ($.trim(res) === 'ok') {
                            swal('Accepted', '', 'success')
                                .then((value) => {
                                    menu(205);
                                });
                        } else {
                            swal('Error', res, 'error');
                        }
                    })
                    .fail(function (res) {
                        swal('Error', '', 'error');
                    });
            } else {
                swal("Cancelated", "");
            }
        });
}

function deny_notificationGroup(key) {
    swal({
        title: "Confirm?",
        text: "",
        icon: "warning",
        buttons: true,
        dangerMode: false,
    })
        .then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: 'models/pub_sub/respondNotificationGroup.php',
                    type: 'POST',
                    data: { key: key, status: 3 }
                })
                    .done(function (res) {
                        if ($.trim(res) === 'ok') {
                            swal('Deleted', '', 'success')
                                .then((value) => {
                                    menu(205);
                                });
                        } else {
                            swal('Error', res, 'error');
                        }
                    })
                    .fail(function (res) {
                        swal('Error', '', 'error');
                    });
            } else {
                swal("Cancelated", "");
            }
        });
}

function share_group(key) {
    $.ajax({
        url: 'models/pub_sub/key.php',
        type: 'POST',
        data: { key: key }
    })
        .done(function (res) {
            if ($.trim(res) === 'ok') {
                menu(310);
            }
        })
        .fail(function (res) {
            //console.log("error");
        });
    return false;
}

function share_group_with_users(key) {
    $.ajax({
        url: 'models/pub_sub/usersByOrg.php',
        type: 'POST',
        data: { key: key }
    })
        .done(function (res) {
            res = JSON.parse(res);
            bootbox.prompt({
                title: "Seleccione un usuario",
                inputType: 'select',
                inputOptions: res,
                callback: function (result) {
                    if (result) {
                        $.ajax({
                            url: 'models/pub_sub/publishGroupToUser.php',
                            type: 'POST',
                            data: { group: key, user: result },
                        })
                            .done(function (res) {
                                //console.log(res);
                                swal('Ok', res, 'success');
                            })
                            .fail(function () {
                                console.log("error");
                            })
                        //menu(310);
                    } else {
                        swal(" ", "Usuario sin seleccionar");
                    }
                }
            });


        })
        .fail(function () {
            //console.log("error");
        });
}

function subscribe_group_to_users(key) {
    //console.log(key);
    swal({
        title: "Confirm?",
        text: "",
        icon: "warning",
        buttons: true,
        dangerMode: false,
    })
        .then(json => {
            if (json) {
                $.ajax({
                    url: 'models/pub_sub/subscribeGroupToUser.php',
                    type: 'POST',
                    data: { key: key }
                })
                    .done(function (res) {
                        //console.log(res);
                        swal('Ok', res, 'success')
                            .then((value) => {
                                //menu(301);
                            });
                    })
                    .fail(function () {
                    });
            } else {
                swal("Cancelated", "");
            }
        });
}



function share_catalog_with_users(key) {
    //bootbox.alert("Hello world!");
    $.ajax({
        url: 'models/pub_sub/usersByOrg.php',
        type: 'POST',
        data: { key: key }
    })
        .done(function (res) {
            console.log(res);
            //if ($.trim(res) === 'ok') {
            res = JSON.parse(res);
            if(res.length > 0){
                bootbox.prompt({
                    title: "Seleccione un usuario",
                    inputType: 'select',
                    inputOptions: res,
                    callback: function (result) {
                        if (result) {
                            $.ajax({
                                url: 'models/pub_sub/publishCatalogToUser.php',
                                type: 'POST',
                                data: { catalog: key, user: result },
                            })
                                .done(function (res) {
                                    console.log(res);
                                    swal('Ok', res, 'success');
                                })
                                .fail(function (res) {
                                   console.log(res);
                                })
                            //menu(310);
                        } else {
                            swal(" ", "Usuario sin seleccionar");
                        }
                    }
                });
            }else{
                swal(" ", "No hay más usuarios.");
            }
            
        })
        .fail(function (res) {
            //console.log("error");
        });
    return false;
}