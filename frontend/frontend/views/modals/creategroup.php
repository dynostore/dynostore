<div class="modal fade" id="createGroup" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="frmCreateGroup">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Crear catálogo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="ti-close"></i>
                    </button>
                </div>
                <div class="modal-body">

                    <div class="form-group">
                        <label for="txtGroupName">Nombre del grupo</label>
                        <input type="text" name="name" class="form-control" id="txtGroupName" placeholder="Ingrese el nombre del grupo" required>
                        </small>
                    </div>
                    <div class="form-group">
                        <label>Visibilidad</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="visibilidad" type="radio" id="chPrivado" value="false">
                            <label class="form-check-label" for="chPrivado">Privado</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="visibilidad" type="radio" id="chPublico" value="true">
                            <label class="form-check-label" for="chPublico">Público</label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar
                    </button>
                    <button type="submit" class="btn btn-primary">Crear</button>
                </div>
            </form>
        </div>
    </div>
</div>