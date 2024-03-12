<!-- Header -->
<div class="header d-print-none">
        <div class="header-container">
            <div class="header-body">
                <div class="header-body-left">
                    <ul class="navbar-nav">
                        <li class="nav-item navigation-toggler">
                            <a href="#" class="nav-link">
                                <i class="ti-menu"></i>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="header-body-right">
                    <ul class="navbar-nav">
                       

                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link profile-nav-link dropdown-toggle" title="User menu"
                               data-toggle="dropdown">
                                <span class="mr-2 d-sm-inline d-none"><?php echo $_SESSION["username"]; ?></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-big">
                                <div class="text-center py-4">
                                    <figure class="avatar avatar-lg mb-3 border-0">
                                        <img src="<?php echo PROJECT_HOME;?>/assets/media/image/logo-painal.png"
                                             class="rounded-circle" alt="image">
                                    </figure>
                                    <h5 class="mb-0"><?php echo $_SESSION["username"]; ?></h5>
                                </div>
                                <div class="list-group list-group-flush">
                                    <a href="#" class="list-group-item" data-sidebar-target="#settings">Configuración</a>
                                    <a href="logout.php?logout=true" class="list-group-item text-danger">¡Cerrar sesión!</a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item header-toggler">
                    <a href="#" class="nav-link">
                        <i class="ti-arrow-down"></i>
                    </a>
                </li>
                <li class="nav-item sidebar-toggler">
                    <a href="#" class="nav-link">
                        <i class="ti-cloud"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <!-- ./ Header -->