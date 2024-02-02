$(document).ready(function(){

  $('input').iCheck({
    checkboxClass: 'icheckbox_square-blue',
    radioClass: 'iradio_square-blue',
    increaseArea: '20%' // optional
  });
});
 $(function () {
                $('#datetimepicker1').datetimepicker();
            });
  $(function() {
        //Datemask dd/mm/yyyy
        $("#datemask").inputmask("dd/mm/yyyy", {
            "placeholder": "dd/mm/yyyy"
        });
        //Datemask2 mm/dd/yyyy
        $("#datemask2").inputmask("mm/dd/yyyy", {
            "placeholder": "mm/dd/yyyy"
        });
        //Money Euro
        $("[data-mask]").inputmask();

        //Date range picker
        $('#reservation').daterangepicker();
        //Date range picker with time picker
        $('#reservationtime').daterangepicker({
            timePicker: true,
            timePickerIncrement: 30,
            format: 'MM/DD/YYYY h:mm A'
        });
        //Date range as a button
        $('#daterange-btn').daterangepicker({
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
                    'Last 7 Days': [moment().subtract('days', 6), moment()],
                    'Last 30 Days': [moment().subtract('days', 29), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                },
                startDate: moment().subtract('days', 29),
                endDate: moment()
            },
            function(start, end) {
                $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            }
        );

    });
   
    var placeholder = "Select a State";

    $('.select2, .select2-multiple').select2({
        placeholder: placeholder
    });
    $('.select2-allow-clear').select2({
        allowClear: true,
        placeholder: placeholder
    });


    var select2OpenEventName = "select2-open";

    $(':checkbox').on("click", function() {
        $(this).parent().nextAll('select').select2("enable", this.checked);
    });


    $(".select2, .select2-multiple, .select2-allow-clear, .select2-remote").on(select2OpenEventName, function() {
        if ($(this).parents('[class*="has-"]').length) {
            var classNames = $(this).parents('[class*="has-"]')[0].className.split(/\s+/);
            for (var i = 0; i < classNames.length; ++i) {
                if (classNames[i].match("has-")) {
                    $('#select2-drop').addClass(classNames[i]);
                }
            }
        }

    });
  
    $(document).ready(function() {
        $("#e1").select2();
    });
   
    $(document).ready(function() {
        $("#e2").select2();
    });
   
    $(function() {
        var opts = $("#source").html(),
            opts2 = "<option></option>" + opts;
        $("select.populate").each(function() {
            var e = $(this);
            e.html(e.hasClass("placeholder") ? opts2 : opts);
        });
        $(".examples article:odd").addClass("zebra");
    });
  
    $(document).ready(function() {
        function format(state) {
            if (!state.id) return state.text; // optgroup
            return "<img class='flag' src='../assets/img/us_states_flags/" + state.id.toLowerCase() + ".png'/>" + state.text;
        }
        $("#e4").select2({
            formatResult: format,
            formatSelection: format,
            escapeMarkup: function(m) {
                return m;
            }
        });
    });
   
    $(document).ready(function() {

        //iCheck for checkbox and radio inputs
        $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
            checkboxClass: 'icheckbox_minimal',
            radioClass: 'iradio_minimal'
        });
        //Red color scheme for iCheck
        $('input[type="checkbox"].minimal-red, input[type="radio"].minimal-red').iCheck({
            checkboxClass: 'icheckbox_minimal-red',
            radioClass: 'iradio_minimal-red'
        });
        //green color scheme for iCheck
        $('input[type="checkbox"].minimal-green, input[type="radio"].minimal-green').iCheck({
            checkboxClass: 'icheckbox_minimal-green',
            radioClass: 'iradio_minimal-green'
        });
        //Flat red color scheme for iCheck
        $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
            checkboxClass: 'icheckbox_flat-red',
            radioClass: 'iradio_flat-red'
        });
    });
  
    $(document).ready(function() {
        $('.chk').iCheck({
            checkboxClass: 'icheckbox_flat-red',
            radioClass: 'iradio_flat-red'
        });
    });
   
    $(document).ready(function() {
        $('.chk1').iCheck({
            checkboxClass: 'icheckbox_flat-blue',
            radioClass: 'iradio_flat-blue'
        });
    });
    
    $(document).ready(function() {
        $(
            'input#defaultconfig'
        ).maxlength()

        $(
            'input#thresholdconfig'
        ).maxlength({
            threshold: 20

        });
        $(
            'input#moreoptions'
        ).maxlength({
            alwaysShow: true,
            warningClass: "label label-success",
            limitReachedClass: "label label-danger"
        });

        $(
            'input#alloptions'
        ).maxlength({
            alwaysShow: true,
            warningClass: "label label-success",
            limitReachedClass: "label label-danger",
            separator: ' chars out of ',
            preText: 'You typed ',
            postText: ' chars.',
            validate: true
        });


        $(
            'textarea#textarea'
        ).maxlength({
            alwaysShow: true
        });

        $('input#placement')
            .maxlength({
                alwaysShow: true,
                placement: 'top-left'
            });

    });
 
    function format(state) {
        if (!state.id) return state.text; // optgroup
        return '&nbsp;&nbsp;' + state.text;
    }
    $("#select2_sample4").select2({
        placeholder: "Select a Country",
        allowClear: true,
        formatResult: format,
        formatSelection: format,
        escapeMarkup: function(m) {
            return m;
        }
    });
    $(function() {
   $('.clockpicker').clockpicker({
    placement: 'bottom',
    align: 'left',
    donetext: 'Done'
});
     var input = $('.clockpicker-with-callbacks').clockpicker({
                    donetext: 'Done',
                        init: function() { 
                            console.log("colorpicker initiated");
                        },
                        beforeShow: function() {
                            console.log("before show");
                        },
                        afterShow: function() {
                            console.log("after show");
                        },
                        beforeHide: function() {
                            console.log("before hide");
                        },
                        afterHide: function() {
                            console.log("after hide");
                        },
                        beforeHourSelect: function() {
                            console.log("before hour selected");
                        },
                        afterHourSelect: function() {
                            console.log("after hour selected");
                        },
                        beforeDone: function() {
                            console.log("before done");
                        },
                        afterDone: function() {
                            console.log("after done");
                        }
                });

                // Manually toggle to the minutes view
                $('#check-minutes').click(function(e){
                    // Have to stop propagation here
                    e.stopPropagation();
                    input.clockpicker('show')
                            .clockpicker('toggleView', 'minutes');
                });

$("#input-43").fileinput({
    browseClass: "btn btn-info",
    showPreview: false,
    allowedFileExtensions: ["zip", "rar", "gz", "tgz"],
    elErrorContainer: "#errorBlock43"
    // you can configure `msgErrorClass` and `msgInvalidFileExtension` as well
});
$("#input-42").fileinput({
      browseClass: "btn btn-warning",
    maxFilesNum: 10,
    allowedFileExtensions: ["jpg", "gif", "png", "txt"]
});
$("#input-41").fileinput({
    browseClass: "btn btn-danger",
    maxFilesNum: 10,
    allowedFileTypes: ["image", "video"]
});
$(".btn-modify").on("click", function() {

    $btn = $(this);
    if ($btn.text() == "Modify") {
        $("#input-40").fileinput("disable");
        $btn.html("Revert");
        alert("Hurray! I have disabled the input and hidden the upload button.");
    }
    else {
        $("#input-40").fileinput("enable");
        $btn.html("Modify");
        alert("Hurray! I have reverted back the input to enabled with the upload button.");
    }
});

$("#input-23").fileinput({
      browseClass: "btn btn-default",
    showUpload: false,
    mainTemplate:
        "{preview}\n" +
        "<div class='input-group {class}'>\n" +
        "   <div class='input-group-btn'>\n" +
        "       {browse}\n" +
        "       {upload}\n" +
        "       {remove}\n" +
        "   </div>\n" +
        "   {caption}\n" +
        "</div>"
});
$("#input-21").fileinput({
    previewFileType: "image",
    browseClass: "btn btn-success",
    browseLabel: " Pick Image",
    browseIcon: '<i class="glyphicon glyphicon-picture"></i>',
    removeClass: "btn btn-danger",
    removeLabel: "Delete",
    removeIcon: '<i class="glyphicon glyphicon-trash"></i>',
    uploadClass: "btn btn-info",
    uploadLabel: " Upload",
    uploadIcon: '<i class="glyphicon glyphicon-upload"></i>',
});
$("#input-20").fileinput({
    browseClass: "btn btn-info btn-block",
    showCaption: false,
    showRemove: false,
    showUpload: false
});


$("#input-4").fileinput({ browseClass: "btn btn-success", showCaption: false}); 
$("#input-5").fileinput({  browseClass: "btn btn-warning",showUpload: false, maxFileCount: 10, mainClass: "input-group-lg"});
});

