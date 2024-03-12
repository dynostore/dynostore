<?php ?>
    <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" onclick="menu(10)">SkyCDS</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> Catalogos <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a onclick="menu(200)">Nuevo Catalogo</a></li>
                <li><a href="list_catalogs.php">Mis Catalogos</a></li>
                <!--li><a onclick="menu(203)">Suscribir a un Catalogo</a></li-->
                <!--enlaza con la lista de los catalogos a los que se esta suscrito-->
                <!--li><a onclick="menu(204)">Suscripciones</a></li-->
                <!--enlaza con la lista de las solicitudes para unirse a los catalogos propios-->
                <!--li><a onclick="menu(205)">Solicitudes de Catalogos</a></li-->
                
              </ul>
            </li>

            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Grupos<span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a onclick="menu(300)">Nuevo Grupo</a></li>
                <li><a onclick="menu(301)">Mis Grupos</a></li>
                <li><a onclick="menu(302)">Unirse a un Grupo</a></li>
                <!--li><a onclick="menu(305)">Solicitudes de Grupos</a></li-->               
              </ul>
            </li>
            

            <?php if($_SESSION["isadmin"]=='T'){ ?>
              <li><a onclick="menu(101)">Administrar Usuarios</a></li>
            <?php } ?>
          
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <?php echo $_SESSION["username"].'('.$_SESSION["acronym"].')'; ?> <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="perfil.php">Opciones de desarrollador</a></li>
                <li><a href="logout.php">Salir</a></li>
              </ul>
            </li>

          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>