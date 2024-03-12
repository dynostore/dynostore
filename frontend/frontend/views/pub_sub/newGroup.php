<?php
?>
<div class="panel panel-default">
  <div class="panel-heading"><h3>Nuevo Grupo</h3></div>
    <div class="panel-body">
      
            <!--los datos ingresados se enviaran al siguiente archivo-->
            <form data-toggle="validator" id="newGroup_form">
              <div class="row">
                <div class="form-group col-md-5"> 
                    <label for="namegroup">Nombre del Grupo</label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-user"></i>
                        </div>
                        <input type="text" class="form-control" id= "namegroup" name="namegroup" placeholder="Nombre del Grupo" required>
                    </div>  
                </div>
                <div class="form-group col-md-2 col-md-offset-1">
                    <label>Visible:</label>
                        <div class="radio">
                          <label><input type="radio" name="ispublic" id="gridRadios1" value="false" checked>Privado</label>
                        </div>
                        <div class="radio">
                          <label><input type="radio" name="ispublic" id="gridRadios2" value="true">Público</label>
                        </div>
                </div>
              
                <div class="col-md-2 col-md-offset-1">
                    <div class="spacing-2"></div>
                        <button type="submit" class="btn btn-primary btn-block" name="button" id="newCatalog">Guardar </button>
                </div>
              </div>
            </form>
    </div>
</div>


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
      new_group();
      return false;
    });

    
  </script>