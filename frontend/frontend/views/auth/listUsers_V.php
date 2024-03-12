<?php
    session_start();
?>
    <div class="col-md-12">
        <h1>Usuarios</h1>
    </div>
    <div class="col-md-12">    
        <table id="listatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
            <tr>
                <!--th>Id</th-->
                <th>Usuario</th>
                <!--th>Contraseña</th-->
                <th>Correo</th>
                 <th>Estado</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>        
    </div>

    
 
    <!--Javascript-->    
    <script src="js/jquery.dataTables.min.js"></script>
    <script src="js/dataTables.bootstrap.min.js"></script>          
    <script src="js/listUsers.js"></script>

    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
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

    $('#editUsername_form').submit(function(e) {
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

