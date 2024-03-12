<?php
  session_start();
?>
<body id="body-signin">

  <form class="form-signin" id="login_form" data-toggle="validator">
      <img src="../../images/logo2.png" alt="Smiley face" height="100%" width="100%">
      <h1 class="h3 mb-3 font-weight-normal text-center">Iniciar sesión</h1>
      <input type="email" name="user" class="form-control" placeholder="Usuario o correo" required autofocus>
      <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
      <!-- Animacion de load (solo sera visible cuando el cliente espere una respuesta del servidor )-->
        <div class="row" id="load" hidden="hidden">
          
        </div>
        <!-- Fin load -->
      <button class="btn btn-lg btn-primary btn-block" type="button" id="login">Ingresar</button>
      <p class="mt-5 mb-3 text-muted text-center">&copy; 2018</p>
      <div class="checkbox mb-3">
        <center>
           ¿Aun no tienes una cuenta? <a href="views/auth/registro_V.php"> Regístrate</a>
        </center>
        <center>
          Crear organización <a href="views/auth/createOrg.php"> Aquí</a>
        </center>
            
      </div>
    </form>

  <!-- Js personalizado -->
  <script src="js/functions.js"></script>

  
  
</body>