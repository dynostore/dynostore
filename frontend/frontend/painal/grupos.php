<?php
include_once("includes/config.php");

include_once(SESIONES);

//INICIA LA SESIÓN
Sessions::startSession("muyalpainal");

print_r($_SESSION);
if (empty($_SESSION['tokenuser'])) {
  header("Location: login.php");
}

?>

<!DOCTYPE html>
<html lang="en">
<!-- header -->
<?php
$title = "Dashboard";
include_once(VISTAS .  "/components/head.php");
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
    include_once(VISTAS .  "/components/header_bar.php");
    include_once(VISTAS .  "/components/menu.php");

    ?>

    <!-- Content body -->
    <div class="content-wrapper">
      <div class="content-body">
        <!-- Content -->
        <div class="content">
          <div class="page-header d-flex justify-content-between">
            <h2>Mis grupos</h2>
            <a href="#" class="files-toggler">
              <i class="ti-menu"></i>
            </a>
          </div>

          <div class="col-xl-12">
            <div class="d-md-flex justify-content-between mb-4">
              <ul class="list-inline mb-3">
                <li class="list-inline-item mb-0">
                  <a href="#" class="btn btn-outline-light dropdown-toggle" data-toggle="dropdown">
                    Crear
                  </a>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" data-toggle="modal" data-target="#createGroup" style="cursor: pointer">Grupo</a>
                  </div>
                </li>
              </ul>
              <div id="file-actions" class="d-none">
                <ul class="list-inline">
                  <li class="list-inline-item mb-0">
                    <a href="#" class="btn btn-outline-light" data-toggle="tooltip" title="Move">
                      <i class="ti-arrow-top-right"></i>
                    </a>
                  </li>
                  <li class="list-inline-item mb-0">
                    <a href="#" class="btn btn-outline-light" data-toggle="tooltip" title="Download">
                      <i class="ti-download"></i>
                    </a>
                  </li>
                  <li class="list-inline-item mb-0">
                    <a href="#" class="btn btn-outline-danger" data-toggle="tooltip" title="Delete">
                      <i class="ti-trash"></i>
                    </a>
                  </li>
                </ul>
              </div>
            </div>

            <div class="table-responsive" style="padding-left: 20px;">
              <table id="table-groups" class="table table-striped">
                <thead>
                  <tr>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody id="tablegroupsbody">
                </tbody>
              </table>
            </div>

          </div>
        </div>
      </div>

      <?php include_once("views/modals/creategroup.php"); ?>

      <?php

      include_once(VISTAS .  "/components/sidebar.php");
      ?>
    </div>

    <?php
    include_once(VISTAS .  "/components/scripts.php");
    ?>
    <!-- Datatable -->
    <script src="<?php echo PROJECT_HOME; ?>/vendors/dataTable/datatables.min.js"></script>
    <script src="<?php echo PROJECT_HOME; ?>/assets/js/catalogs.js"></script>
    <script>
      $(function () {
        $('#ttable-groups').DataTable({
            "scrollY": "400px",
            "scrollX": "400px",
            "scrollCollapse": true,
            searching: false
        });
        getGroups();
      });
      
    </script>
</body>

</html>