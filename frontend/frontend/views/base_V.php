<?php
session_start();
?>

<body>
    <?php
    include_once('navbar.php');
    ?>
    <div class="col-md-12 col-sm-12 col-xs-12 well"></div>
    <div class="col-md-10 col-md-offset-1 col-sm-12 col-xs-12 well" id="page-body">
        <?php
        include_once('home_V.php');
        ?>
    </div>

    <!-- Animacion de load -->
    <!-- PARA FUTURAS MEJORAS -->
    <div class="row" id="load" hidden="hidden">
        <div class="col-xs-4 col-xs-offset-4 col-md-2 col-md-offset-5"></div>
        <div class="col-xs-12 center text-accent"></div>
    </div>
    <!-- Fin load -->
</body>
<!-- Js personalizado -->
<script src="js/functions.js"></script>
<script>
    /*$('document').ready(function() {
        menu(201);
    });*/
</script>

</html>