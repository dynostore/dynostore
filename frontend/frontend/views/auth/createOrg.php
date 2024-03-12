<?php
include_once('../header2.php');
?>

<title>Nueva Organización</title>
  
    <div class="container">
      <div class="row">
        <div class="col-xs-12 col-md-4 col-md-offset-4">
          <!-- Margen superior (css personalizado )-->
          <div class="spacing-1"></div>

          <form data-toggle="validator" id="formulario_registro">
            <legend class="center">Nueva Organización</legend>
            <div class="form-group"> 
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-sitemap"></i>
                        </div>
                        <input type="text" class="form-control" id= "fullname" name="fullname" placeholder="Nombre completo" required>
                    </div>  
                </div>

                <div class="form-group"> 
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-sitemap"></i>
                        </div>
                        <input type="text" class="form-control" id="acronym" name="acronym" placeholder="Acrónimo" required>
                      </div>
                      <!--label class="">This will be the admin user.</label-->
                </div>

                <!--div class="form-group"> 
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-lock"></i>
                        </div>
                        <select data-placeholder="" class="form-control">
                            <option selected value="/">No pertenece</option>
                            <?php
                                foreach ($table as $row){
                                    echo '<option value="'.$row['tokenhierarchy'].'">'.$row['acronym'].'</option>';
                                }
                            ?>
                        </select>
                    </div>  
                </div-->
                
                


              <!-- Animacion de load-->
              <div class="center" id="load">
                
              </div>
              <!-- Fin load -->

              <!-- boton para activar la funcion click y enviar el los datos mediante ajax -->
              <div class="row">
                <div class="col-xs-8 col-xs-offset-2">
                  <div class="spacing-2"></div>
                <center><button type="submit" class="btn btn-default" >Crear</button></center>
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
      if (!validator.checkAll($(this))) {
        submit = false;
      }

      if (submit)
      new_org();
      return false;
    });
  </script>
  </body>
</html>
