<?php
include_once("includes/config.php");

include_once(SESIONES);

//INICIA LA SESIÓN
Sessions::startSession("muyalpainal");

if(empty($_SESSION['tokenuser'])){
  header("Location: login.php");
}

?>

<!DOCTYPE html>
<html lang="en">
<!-- header -->
<?php
  $title = "Dashboard";
  include_once( VISTAS .  "/components/head.php");
?>

<body>

<!-- Preloader 
<div class="preloader">
    <div class="preloader-icon"></div>
</div>
./ Preloader -->

<!-- Layout wrapper -->
<div class="layout-wrapper">
  <?php
    include_once( VISTAS .  "/components/header_bar.php");
    include_once( VISTAS .  "/components/menu.php");

  ?>

    <!-- Content body -->
    <div class="content-body">
      <!-- Content -->
      <div class="content">
        <?php //print_r($_SESSION); ?>
      </div>
    </div>

  <?php

    include_once( VISTAS .  "/components/sidebar.php");
  ?>
</div>

<?php
  include_once( VISTAS .  "/components/scripts.php");
?>
</body>
</html>