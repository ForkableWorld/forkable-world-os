$(function() {
        // Replace the <textarea id="editor1"> with a CKEditor
        // instance, using default configuration.
        // CKEDITOR.replace('editor1');
        //bootstrap WYSIHTML5 - text editor
        $(".textarea").wysihtml5();
    });
   
    $(function() {
        // CKEditor Standard
        $('textarea#ckeditor_standard').ckeditor({
            height: '150px',
            toolbar: [{
                    name: 'document',
                    items: ['Source', '-', 'NewPage', 'Preview', '-', 'Templates']
                }, // Defines toolbar group with name (used to create voice label) and items in 3 subgroups.
                ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'], // Defines toolbar group without name.
                {
                    name: 'basicstyles',
                    items: ['Bold', 'Italic']
                }
            ]
        });
        // CKEditor Full
        $('textarea#ckeditor_full').ckeditor({
            height: '200px'
        });
        //summernote JS
        $('.summernote').summernote({
            height: 200
        });

    });
    // Bootstrap
    $('#bootstrap-editor').wysihtml5({
        stylesheets: [
            'vendors/bootstrap-wysihtml5/wysiwyg-color.css'
        ]
    });