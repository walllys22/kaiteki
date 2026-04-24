$(document).ready(function() {
    const fixedDojoId = $('#modal_person_dojo_id').length ? $('#modal_person_dojo_id').val() : '';

    $('#create-form-person').submit(function(e) {
        e.preventDefault();
        $('.btn-save-person').attr('disabled', true);
        $('.btn-save-person').val('Guardando...');

        let form = $(this);
        let dojoId = $('#modal_person_dojo_select').val() || fixedDojoId || $('select[name="dojo_id"]').first().val() || $('#dojo_id').val() || '';

        if ($('#modal_person_dojo_id').length) {
            $('#modal_person_dojo_id').val(dojoId);
        }

        $.post(form.attr('action'), $(this).serialize(), function(data) {
            if (data.person) {
                toastr.success('Persona registrada', 'Exito');
                $(document).trigger('person:created', [data.person]);
                form[0].reset();
                if ($('#modal_person_dojo_id').length) {
                    $('#modal_person_dojo_id').val(fixedDojoId);
                }
                if (fixedDojoId && $('#modal_person_dojo_select').length && $('#modal_person_dojo_select').is(':disabled')) {
                    $('#modal_person_dojo_select').val(fixedDojoId).trigger('change');
                }
                $('#modal-create-person').modal('hide');
            } else {
                toastr.error(data.error, 'Error');
            }
        })
            .fail(function(data) {
                toastr.error(data.responseJSON.error, 'Error');
            })
            .always(function() {
                $('.btn-save-person').attr('disabled', false);
                $('.btn-save-person').val('Guardar');
            });
    });
});
