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