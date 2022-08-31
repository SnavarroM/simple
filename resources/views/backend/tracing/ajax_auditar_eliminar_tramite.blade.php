<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Eliminación de trámite</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form id="formAuditarRetrocesoEtapa" method='POST' class='ajaxForm'
                action="<?= url('backend/seguimiento/borrar_tramite/' . $tramite->id) ?>">
                {{csrf_field()}}
                <div class='validacion'></div>
                <div class="form-group">
                    <label>Indique la razón por la cuál elimina el trámite:</label>
                    <textarea class="form-control" name='descripcion' type='text' required></textarea>
                </div>
                <hr>
                <h6>
                    Estado:
                    <span class="badge badge-primary">{{$estado}}</span>
                </h6>
                <h6>
                    Etapa Actual:
                    @foreach ($etapas as $etapa)
                        <span class="badge badge-primary">{{$etapa}}</span>&nbsp;
                    @endforeach
                </h6>
                <hr>
                <div class="alert alert-danger" role="alert">
                    <div class="form-row">
                        <h6>
                            A continuación va a eliminar un trámite, esta acción es irreversible, para confirmar la eliminación de este trámite debe ingresar su ID
                        </h6>
                        <input type="text" class="form-control is-invalid" id="confirm-tramite-id" name="tarea_id" placeholder="Ingrese id de trámite">
                        <div class="invalid-feedback">
                            Asegúrese de ingnresar el ID correcto.
                        </div>
                    </div>
                </div>
            </form>

        </div>
        <div class="modal-footer">
            <button class="btn btn-light" data-dismiss="modal">Cerrar</button>
            <a href="#" id="eliminar-tramite-confirmar" onclick="javascript:$('#formAuditarRetrocesoEtapa').submit();
        return false;" class="btn btn-primary">Guardar</a>
        </div>
    </div>
</div>

<script>
    var CONFIRM_TRAMITE_ID={{$tramite->id}};
    $(document).ready(function(){
        $('#eliminar-tramite-confirmar').addClass('disabled');
        $('#confirm-tramite-id').on('change keyup paste input', function(){
            var element = $(this);
            var data = $(element).val();
            if (CONFIRM_TRAMITE_ID==data) {
                element.removeClass('is-invalid').addClass('is-valid')
                element.parent().addClass('was-validated');
                element.parent().parent().removeClass('alert-danger');
                element.parent().parent().addClass('alert-success');
                $('#eliminar-tramite-confirmar').removeClass('disabled');
            } else {
                element.removeClass('is-valid').addClass('is-invalid')
                element.parent().removeClass('alert-danger was-validated');
                element.parent().parent().removeClass('alert-success');
                element.parent().parent().addClass('alert-danger');
                $('#eliminar-tramite-confirmar').addClass('disabled');
            }
        });
    });
</script>