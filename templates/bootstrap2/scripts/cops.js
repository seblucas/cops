function postRefresh()
{
    $('[data-toggle="tooltip"]').tooltip();
    hash = window.location.hash.replace("#", "");
    var elmnt = document.getElementById(hash);
    if (elmnt) elmnt.scrollIntoView();
}