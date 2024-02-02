 var c = window.location.href.match(/c=inline/i) ? 'inline' : 'popup';
    $.fn.editable.defaults.mode = c === 'inline' ? 'inline' : 'popup';

    $(function() {
        $('#f').val(f);
        $('#c').val(c);

        $('#frm').submit(function() {
            var f = $('#f').val();
            if (f === 'jqueryui') {
                $(this).attr('action', 'demo-jqueryui.html');
            } else if (f === 'plain') {
                $(this).attr('action', 'demo-plain.html');
            } else if (f === 'bootstrap2') {
                $(this).attr('action', 'demo.html');
            } else {
                $(this).attr('action', 'x-editable');
            }
        });
    });
    var f = 'bootstrap3';