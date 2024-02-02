 $(function() {
        $("[data-toggle='popover']").popover();
    });
   
    $(document).ready(function() {
        $('.po-markup > .po-link').popover({
            trigger: 'hover',
            html: true, // must have if HTML is contained in popover
            // get the title and conent
            title: function() {
                return $(this).parent().find('.po-title').html();
            },
            content: function() {
                return $(this).parent().find('.po-body').html();
            },
            container: 'body',
            placement: 'right'
        });
    });
  
    $(document).ready(function() {
        $(".tooltip-examples a").tooltip({
            placement: 'top'
        });
    });