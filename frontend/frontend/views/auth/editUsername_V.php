<?php
  session_start();

  if(!isset($_SESSION["connected"]) || $_SESSION["connected"]!=1){
    print "<script>window.location='/';</script>";
  }
?>

  <!-- Formulario Login -->
  <div class="container">
    <div class="row">
      <div class="col-xs-12 col-md-4 col-md-offset-4">
        <form data-toggle="validator" id="editUsername_form">
          <legend class="center">Editar Nombre de Usuario</legend>
          <!-- Caja de texto para usuario -->
          <label class="sr-only" for="username">Nombre Nuevo</label>
          <div class="input-group">
            <div class="input-group-addon"><i class="fa fa-user"></i>
            </div>
            <input type="text" class="form-control" name="username" placeholder="Ingresa el nuevo nombre" required>
          </div>

          <!-- Div espaciador -->
          <div class="spacing-2"></div>
          <button type="submit" class="btn btn-primary btn-block" >Cambiar</button>
        </form>
    </div>
  </div>

  <!-- / Final Formulario login -->


<!-- form validation -->
  <script src="js/validator/validator.js"></script>
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
      if (!validator.checkAll($(this))) {
        submit = false;
      }

      if (submit)
      edit_username();
      return false;
    });
  </script>