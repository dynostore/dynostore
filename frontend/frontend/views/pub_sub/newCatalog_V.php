<?php
  session_start();
  require_once "../models/Curl.php";

  $curl = new Curl();
  $url = $_ENV['APIGATEWAY_HOST'].'/pub_sub/v1/view/groups/user/'.$_SESSION['tokenuser'].'/subscribed?access_token='.$_SESSION['access_token'];
  $curl = new Curl();
  $response = $curl->get($url);
  $groups  = $response["data"]["data"];
  
?>
<div class="panel panel-default">
  <div class="panel-heading"><h3>Nuevo Catalogo</h3></div>
    <div class="panel-body">
      
            <!--los datos ingresados se enviaran al siguiente archivo-->
            <form data-toggle="validator" id="newCatalog_form">
                <div class="row">
                <div class="form-group col-md-4"> 
                    <label for="catalogname">Nombre del Catalogo</label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-user"></i>
                        </div>
                        <input type="text" class="form-control" id= "catalogname" name="catalogname" placeholder="Nombre del Catalogo" required>
                    </div>  
                </div>
                
                <div class="form-group col-md-4">
                    <label>Seleccione dispersemode:</label>
                        <div class="radio">
                          <label><input type="radio" name="dispersemode" id="gridRadios1" value="SINGLE" checked>SINGLE</label>
                        </div>
                        <div class="radio">
                          <label><input type="radio" name="dispersemode" id="gridRadios2" value="IDA">IDA</label>
                        </div>
                </div>
                <!--opcion para elegir si el registro del catalogo estara cifrado o no-->
                <div class="form-group col-md-4">
                    <label>Cifrado:</label>
                        <div class="radio">
                          <label><input type="radio" name="encryption" id="cgridRadios1" value="true" checked>Activado</label>
                        </div>
                        <div class="radio">
                          <label><input type="radio" name="encryption" id="cgridRadios2" value="false">Desactivado</label>
                        </div>
                </div>

                <div class="form-group">
                  <label for="exampleFormControlSelect1">Grupo:</label>
                  <select class="form-control" name="group" id="exampleFormControlSelect1">
                    <?php foreach ($groups as $rows) {
                        echo "<option value='".$rows["tokengroup"]."'>".$rows["namegroup"]."</option>";
                    }  ?>
                  </select>
                </div>
                
                
                <div class="col-md-3">
                    <div class="spacing-2"></div>
                        <button type="submit" class="btn btn-primary btn-block" name="button" id="newCatalog">Guardar Catalogo</button>
                </div>
            </div>
            </form>
    </div>
  </div>
</div>
        <!-- Js personalizado-->

        <script src="js/chosen.jquery.js" type="text/javascript"></script>
        <script src="js/prism.js" type="text/javascript" charset="utf-8"></script>
        <script src="js/init.js" type="text/javascript" charset="utf-8"></script>

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
      new_catalog();
      return false;
    });
  </script>