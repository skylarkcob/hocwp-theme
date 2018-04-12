window.wp = window.wp || {};
window.hocwpTheme = window.hocwpTheme || {};

hocwpTheme.getParamByName = function (url, name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");

    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(url);

    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
};