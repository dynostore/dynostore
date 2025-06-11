<?php
include_once("includes/config.php");
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


        <h5>Inicio de sesión</h5>

        <!-- form -->
        <form class="form-signin" id="login_form">
            <div class="form-group">
                <input type="text" name="username" class="form-control" placeholder="Nombre de usuario o correo" required autofocus>
            </div>
            <div class="form-group">
                <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
            </div>
            <div class="form-group d-flex justify-content-between">
                <a href="#">Restaurar contraseña</a>
            </div>
            <button class="btn btn-primary btn-block" id="login">Iniciar sesión</button>
            <p class="text-muted">¿No tienes una cuenta?</p>
            <a href="registro.php" class="btn btn-outline-light btn-sm">¡Registrarse ahora!</a>
            <hr>
            <a href="#" class="btn btn-outline-light btn-sm" data-toggle="modal" data-target="#createOrg">Crear organización</a>
        </form>
        <!-- ./ form -->

        <div class="modal fade" id="createOrg" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form id="frmCreateOrg">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalCenterTitle">Crear organización</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <i class="ti-close"></i>
                            </button>
                        </div>
                        <div class="modal-body">

                            <div class="form-group">
                                <label for="txtOrgName">Nombre de la organización</label>
                                <input type="text" class="form-control" id="txtOrgName" name="fullname" placeholder="Ingrese el nombre de la organización" required>
                                </small>
                            </div>
                            <div class="form-group">
                                <label for="txtOrgName">Acrónimo de la organización</label>
                                <input type="text" class="form-control" id="txtOrgName" name="acronym" placeholder="Ingrese el acrónimo de la organización" required>
                                </small>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar
                            </button>
                            <button type="submit" class="btn btn-primary">Crear</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

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



</body>

</html>