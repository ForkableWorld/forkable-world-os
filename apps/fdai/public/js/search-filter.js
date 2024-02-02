function notFoundBox(searchId) {
    if(!searchId){searchId = "no-table";}
    return getElementOrError(searchId + '-not-found-box');
}
function getElementOrError(searchId) {
    var el = document.getElementById(searchId);
    if (!el) {console.error("no " + searchId)}
    return el;
}
function debounce(func, timeout = 300){
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => { func.apply(this, args); }, timeout);
    };
}
function getSearchInput(searchId){
    var input = document.getElementById(searchId + '-input');
    if(input){input = input.value;}
    if(!input || !input.length){
        return ''
    }
    input = input.toUpperCase();
    return input;
}

function searchFilter(searchId) {
    //debugger
    var searching = document.getElementById('searching-pill');
    var input = getSearchInput(searchId);
    var listId = searchId + '-list';
    var ul = document.getElementById(listId);
    var items = ul.getElementsByTagName('a');
    toggleNotFound();
    hideItems();
    if(searching){searching.style.display = "block";}
    function toggleNotFound() {
        if(!input){return;}
        var notFound = notFoundBox(searchId);
        if(searching){searching.style.display = "none";}
        if (notFound) {
            if (input.length > 2) {
                notFound.style.display = "";
            } else {
                notFound.style.display = "none";
            }
        }
    }
    function hideItems() {
        [...items].forEach(function (item){
            var txtValue = item.textContent || item.innerText;
            if(item.__x_for && item.__x_for.item && item.__x_for.item.keywords){
                txtValue = item.__x_for.item.keywords
            }
            if (txtValue.toUpperCase().indexOf(input) > -1) {
                item.style.display = "";
            } else {
                item.style.display = "none";
            }
        })
    }
}
