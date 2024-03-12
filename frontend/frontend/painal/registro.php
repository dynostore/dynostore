<?php
include_once("includes/config.php");

include_once(SESIONES);

//INICIA LA SESIÓN
Sessions::startSession("muyalpainal");

if (!empty($_SESSION['tokenuser'])) {
    header("Location: index.php");
}

include_once(CLASES . "/Curl.php");
$url = $_ENV['APIGATEWAY_HOST'] . '/auth/v1/view/hierarchy/all';

$curl = new Curl();
$response = $curl->get($url);
//print_r($response);
//if ($response['code']==200 && isset($response['data']['data'])) {
if ($response['code'] == 200 && isset($response['data']['data'])) {
    //$table = $response['data']['data'];
    $table = $response['data']['data'];
} else {
    $table = array();
}

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Filedash - File Manager Dashboard</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo PROJECT_HOME; ?>/assets/media/image/favicon.png" />

    <!-- Plugin styles -->
    <link rel="stylesheet" href="<?php echo PROJECT_HOME; ?>/vendors/bundle.css" type="text/css">

    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;700&display=swap" rel="stylesheet">

    <!-- Prism -->
    <link rel="stylesheet" href="<?php echo PROJECT_HOME; ?>/vendors/prism/prism.css" type="text/css">

    <!-- Sweet Alert -->
    <link rel="stylesheet" href="<?php echo PROJECT_HOME; ?>/assets/css/sweetalert.css" type="text/css">

    <!-- App styles -->
    <link rel="stylesheet" href="<?php echo PROJECT_HOME; ?>/assets/css/app.min.css" type="text/css">
    <link rel="stylesheet" href="<?php echo PROJECT_HOME; ?>/vendors/select2/css/select2.min.css" type="text/css">
</head>

<body class="form-membership">

    <!-- begin::preloader-->
    <div class="preloader">
        <div class="preloader-icon"></div>
    </div>
    <!-- end::preloader -->

    <div class="form-wrapper">

        <!-- logo -->
        <div id="logo">
            <img width="310px" src="<?php echo PROJECT_HOME; ?>/assets/media/image/logomuyalpainal.png" alt="image">
        </div>
        <!-- ./ logo -->


        <h5>Crear una cuente</h5>

        <!-- form -->
        <form id="formulario_registro">
            <div class="form-group">
                <input type="text" class="form-control" id="username" name="username" placeholder="Nombre de usuario" required autofocus>
            </div>
            <div class="form-group">
                <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" id="password2" name="password2" placeholder="Confirme su contraseña" required>
            </div>
            <div class="form-group">
                <select class="select2-example" name="tokenorg">
                    <option>Seleccione una organización</option>
                    <?php
                    foreach ($table as $row) {
                        echo '<option value="' . $row['tokenorg'] . '">' . $row['acronym'] . '</option>';
                    }
                    ?>
                </select>
            </div>
            <button class="btn btn-primary btn-block">Register</button>
            <hr>
            <p class="text-muted">Ya tengo una cuenta</p>
            <a href="login.php" class="btn btn-outline-light btn-sm">Iniciar sesión</a>
        </form>
        <!-- ./ form -->


    </div>


    <!-- Plugin scripts -->
    <script src="<?php echo PROJECT_HOME; ?>/vendors/bundle.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.4/raphael-min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/justgage/1.2.9/justgage.min.js"></script>

    <!-- Prism -->
    <script src="<?php echo PROJECT_HOME; ?>/vendors/prism/prism.js"></script>


    <!-- App scripts -->
    <script src="<?php echo PROJECT_HOME; ?>/assets/js/app.min.js"></script>
    <script src="<?php echo PROJECT_HOME; ?>/assets/js/login.js"></script>
    <!-- Javascript -->
    <script src="<?php echo PROJECT_HOME; ?>/vendors/select2/js/select2.min.js"></script>

    <script>
        $('.select2-example').select2({
            placeholder: 'Seleccione una organización'
        });

        $('#formulario_registro').submit(function(e) {
            e.preventDefault();
            var submit = true;

            toastr.options = {
                timeOut: 3000,
                progressBar: true,
                showMethod: "slideDown",
                hideMethod: "slideUp",
                showDuration: 200,
                hideDuration: 200
            };

            new_user();
            return false;
        });

        function new_user() {
            var clave = $('#password').val();
            var clave2 = $('#password2').val();
            if (clave == clave2) {
                $.ajax({
                        url: 'models/auth/registro.php',
                        type: 'POST',
                        dataType: "json",
                        data: $("#formulario_registro").serialize(),
                        beforeSend: function() {
                            toastr.info('Registrando usuario...');
                        }
                    })
                    .done(function(res) {
                        $('#load').html('');
                        if (res) {
                            //swal('Ok', 'User created, please check your email.', 'success')
                            console.log(res);
                            if (res['codigo'] != 0) {
                                toastr.error(res['message']);
                            } else {
                                toastr.success(res['message']);
                            }

                        } else {
                            toastr.error('Error');
                        }
                    })
                    .fail(function(res) {
                        console.log(res);
                        //$('#load').html('');
                        toastr.error('Error');
                    });
            } else {
                toastr.error('Las contraseñas no coinciden.');
            }
        }
    </script>


</body>

</html>