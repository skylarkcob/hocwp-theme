window.wp = window.wp || {};
window.hocwpTheme = window.hocwpTheme || {};

var log = console.log.bind(document);

hocwpTheme.getParamByName = function (url, name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");

    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(url);

    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
};

log("%c" + hocwpTheme.l10n.themeCreatedBy, "font-size:16px;color:red;font-family:tahoma;padding:10px 0");

function hocwpThemeFilterList(input) {
    var filter, ul, li, a, i, txtValue;

    if ("object" != typeof input) {
        input = document.getElementById(input);
    }

    filter = input.value.toUpperCase();

    ul = input.closest(".filter-box").getElementsByTagName("ul")[0];

    li = ul.getElementsByTagName("li");

    for (i = 0; i < li.length; i++) {
        a = li[i].getElementsByTagName("a")[0];

        txtValue = a.getAttribute("title");

        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            li[i].style.display = "";
        } else {
            li[i].style.display = "none";
        }
    }
}