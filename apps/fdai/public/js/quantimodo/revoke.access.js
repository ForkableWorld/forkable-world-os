$(function() {
    $('.revoke-access').on('click', function(event) {
        var id = $(this).attr('data-client-id');
        $('#delete').attr('data-client-id', id);
        $('#confirm-delete').modal('show');
    });

    $('#delete').on('click', function(event) {
        var $this = $(this);
        var id = $this.attr('data-client-id');
        $this.attr('data-client-id', '');

        $.post("/account/revoke-access", {clientId: id}, function(result) {
            if (result.success) {
                $('button[data-client-id="'+id+'"]').parent().parent().remove();
            }
            $('#confirm-delete').modal('hide');
        });

        return false;
    });
});
