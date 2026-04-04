<form action="#" class="form-edit-add" id="delete_form" method="POST">
    {{ method_field('DELETE') }}
    {{ csrf_field() }}
    <div class="modal modal-danger fade" data-backdrop="static"  id="modal-delete" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" style="color:rgb(255, 255, 255) !important"><i class="voyager-trash"></i> ¿Estás seguro que quieres eliminar?</h4>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="voyager-trash" style="color: red; font-size: 4em;"></i>
                        <br>
                        <p><b>¿Estás seguro que quieres eliminar?</b></p>
                    </div>
                    <div class="form-group">
                        <textarea name="deleteObservation" class="form-control" rows="4" placeholder="Describa el motivo de la eliminación..." required></textarea>
                    </div>
                    <label class="checkbox-inline">
                        <input type="checkbox" required>Confirmar eliminación..!
                    </label>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-cancel" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger btn-submit">Sí, eliminar</button>
                </div>
            </div>
        </div>
    </div>
</form>
