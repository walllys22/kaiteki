$(document).ready(function(){   
    $('#create-form-person').submit(function(e){
        e.preventDefault();
        $('.btn-save-person').attr('disabled', true);
        $('.btn-save-person').val('Guardando...');

        let form = $(this);
        
        $.post(form.attr('action'), $(this).serialize(), function(data){
            if(data.person){
                toastr.success('Persona registrada', 'Éxito');
                form[0].reset();
                $('#modal-create-person').modal('hide');
            }else{
                toastr.error(data.error, 'Error');
            }
        })
        .fail(function(data){
            toastr.error(data.responseJSON.error, 'Error');
        })
        .always(function(){
            $('.btn-save-person').attr('disabled', false);
            $('.btn-save-person').val('Guardar');
        });
    });
});

