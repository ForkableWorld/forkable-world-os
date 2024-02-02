$(document).ready(function() {
        $("#mytable #checkall").click(function() {
            if ($("#mytable #checkall").is(':checked')) {
                $("#mytable input[type=checkbox]").each(function() {
                    $(this).prop("checked", true);
                });
            } else {
                $("#mytable input[type=checkbox]").each(function() {
                    $(this).prop("checked", false);
                });
            }
        });
        //removing/hiding panel1
        $('.removepanel1').click(function() {
            $('.hidepanel1').hide();
        });
        //removing/hiding panel2
        $('.removepanel2').click(function() {
            $('.hidepanel2').hide();
        });
        //removing/hiding panel3
        $('.removepanel3').click(function() {
            $('.hidepanel3').hide();
        });
        //removing/hiding panel3
        $('.removepanel4').click(function() {
            $('.hidepanel4').hide();
        });
        //removing/hiding panel3
        $('.removepanel5').click(function() {
            $('.hidepanel5').hide();
        });
        //starts hiding three panel contents
        $('.showhide').attr('title', 'Hide Panel content');

        $(document).on('click', '.panel-heading span.clickable', function(e) {
            var $this = $(this);
            if (!$this.hasClass('panel-collapsed')) {
                $this.parents('.panel').find('.panel-body').slideUp();
                $this.addClass('panel-collapsed');
                $this.find('i').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
                $('.showhide').attr('title', 'Show Panel content');
            } else {
                $this.parents('.panel').find('.panel-body').slideDown();
                $this.removeClass('panel-collapsed');
                $this.find('i').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
                $('.showhide').attr('title', 'Hide Panel content');
            }
        });
        //Ends Hiding Three Panel Contents

    });