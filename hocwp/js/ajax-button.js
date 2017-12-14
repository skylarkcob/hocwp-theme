window.hocwpTheme = window.hocwpTheme || {};
jQuery(document).ready(function ($) {
    var body = $("body"),
        ajaxOverlay = null;

    function hocwpThemeAppendAjaxOverlay() {
        if (null === ajaxOverlay) {
            body.append(hocwpTheme.ajaxOverlay);
        }
        ajaxOverlay = body.find(".hocwp-theme.ajax-overlay");
        if (ajaxOverlay.length) {
            ajaxOverlay.show();
        }
    }

    /**
     * Ajax button
     */
    (function () {
        body.on("click", "[data-ajax-button='1']", function () {
            $(this).addClass("disabled");
            hocwpThemeAppendAjaxOverlay();
        });
        body.on("hocwpTheme:ajaxStart", function (e, button, response) {
            button.addClass("disabled");
            hocwpThemeAppendAjaxOverlay();
        });
        body.on("hocwpTheme:ajaxComplete", function (e, button, response) {
            button.removeClass("disabled");
            body.find(".hocwp-theme.ajax-overlay").fadeOut();
        });
    })();
});