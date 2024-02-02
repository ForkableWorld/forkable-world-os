<!-- Start resources/views/search-filter-script.blade.php -->
{{-- Make sure to wrap items with an <a> tag so they're hidden --}}
<form onsubmit="return false;" style="margin-top: 1rem; margin-bottom: 1rem;">
    <div style="padding: 0.5em;
    width: 100%;
    font-size: 0.9em;
    border: 1px solid #cccccc;
    border-radius: 2rem;
    box-shadow: 0 1px 6px 0 rgba(32, 33, 36, 0.28);">
        <i class="fas fa-search" style="padding: 0.5rem"></i>
        <!--suppress HtmlFormInputWithoutLabel -->
        <input
            type="text"
            style="margin: 0; display: inline; width: 80%"
            id="{{$searchId ?? $table ?? "no-search-id-provided"}}-input"
            onkeyup="searchFilter('{{$searchId ?? $table ?? "no-search-id-provided"}}')"
            placeholder="{{ $placeholder ?? "Enter your search term..."}}"
            autofocus>
    </div>
</form>

@include('ionic_js')
<!-- End resources/views/search-filter-script.blade.php -->
