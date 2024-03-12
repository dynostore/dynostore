<?php
include_once('../header2.php');
require_once "../../models/Curl.php";

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

<title>Registro</title>

<!-- Formulario Login -->
<div class="container">
  <div class="row">
    <div class="col-xs-12 col-md-4 col-md-offset-4">
      <!-- Margen superior (css personalizado )-->
      <div class="spacing-1"></div>

      <form data-toggle="validator" id="formulario_registro">
        <legend class="center">Registro</legend>
        <div class="form-group">
          <div class="input-group">
            <div class="input-group-addon">
              <i class="fa fa-user"></i>
            </div>
            <input type="text" class="form-control" id="username" name="username" placeholder="Ingresa tu nombre" required>
          </div>
        </div>

        <div class="form-group">
          <div class="input-group">
            <div class="input-group-addon">
              <i class="fa fa-envelope"></i>
            </div>
            <input type="text" class="form-control" id="email" name="email" placeholder="Ingresa tu email" required>
          </div>
        </div>

        <div class="form-group">
          <div class="input-group">
            <div class="input-group-addon">
              <i class="fa fa-lock"></i>
            </div>
            <input type="password" class="form-control" autocomplete="off" id="password" name="password" placeholder="Verifica tu contraseña" required>
          </div>
        </div>

        <div class="form-group">
          <div class="input-group">
            <div class="input-group-addon">
              <i class="fa fa-lock"></i>
            </div>
            <input type="password" class="form-control" autocomplete="off" id="password2" name="password2" data-validate-linked="password" placeholder="Verifica tu contraseña" required>
          </div>
        </div>

        <div class="form-group">
          <div class="input-group">
            <div class="input-group-addon">
              <i class="fa fa-sitemap"></i>
            </div>
            <select data-placeholder="" class="form-control" name="tokenorg">
              <?php
              foreach ($table as $row) {
                echo '<option value="' . $row['tokenorg'] . '">' . $row['acronym'] . '</option>';
              }
              ?>
            </select>
          </div>
        </div>


        <!-- Animacion de load-->
        <div class="center" id="load">

        </div>
        <!-- Fin load -->

        <!-- boton para activar la funcion click y enviar el los datos mediante ajax -->
        <div class="row">
          <div class="col-xs-8 col-xs-offset-2">
            <div class="spacing-2"></div>
            <center><button type="submit" class="btn btn-default">Regístrate</button></center>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>


<script src="../../js/jquery.js" type="text/javascript"></script>
<script src="../../js/functions.js" type="text/javascript"></script>

<!-- form validation -->
<script src="../../js/validator/validator.js"></script>
<script>
  // initialize the validator function
  validator.message['date'] = 'not a real date';

  // validate a field on "blur" event, a 'select' on 'change' event & a '.reuired' classed multifield on 'keyup':
  $('form')
    .on('blur', 'input[required], input.optional, select.required', validator.checkField)
    .on('change', 'select.required', validator.checkField)
    .on('keypress', 'input[required][pattern]', validator.keypress);

  $('.multi.required')
    .on('keyup blur', 'input', function() {
      validator.checkField.apply($(this).siblings().last()[0]);
    });

  // bind the validation to the form submit event
  //$('#send').click('submit');//.prop('disabled', true);

  $('form').submit(function(e) {
    e.preventDefault();
    var submit = true;
    // evaluate the form using generic validaing
    /*if (!validator.checkAll($(this))) {
      submit = false;
    }
    console.log(submit);
    if (submit)*/
    new_user();
    return false;
  });
</script>
</body>

</html>