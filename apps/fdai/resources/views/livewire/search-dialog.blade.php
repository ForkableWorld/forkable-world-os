<?php /** @var App\Models\Variable $user */ ?>
<button id="show-search-dialog-button" type="button" class="mdl-button">Show Dialog</button>
<dialog id="search-dialog" class="mdl-dialog">
    @include('livewire.search')
    <div id="dialog-actions" class="mdl-dialog__actions">
        <button id="dialog-close-button"  type="button" class="mdl-button close">Cancel</button>
    </div>
</dialog>
<script>
    var dialog = document.querySelector('#search-dialog');
    var showDialogButton = document.querySelector('#show-search-dialog-button');
    if (! dialog.showModal) {dialogPolyfill.registerDialog(dialog);}
    showDialogButton.addEventListener('click', function() {
        dialog.showModal();
    });
    dialog.querySelector('#dialog-close-button').addEventListener('click', function() {
        dialog.close();
    });
</script>
