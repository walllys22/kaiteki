$(document).ready(function() {
    $('.form-edit-add').submit(function(e) {
        $('.btn-submit').html('Guardando... <i class="fa fa-spinner fa-spin"></i>');
        $('.btn-submit').attr('disabled', true);
        $('.btn-cancel').attr('disabled', true);
    });
});