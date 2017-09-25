window.hocwpTheme = window.hocwpTheme || {};
jQuery(document).ready(function ($) {
    var body = $("body");
    /**
     * Ajax button
     */
    (function () {
        body.on("click", "[data-ajax-button='1']", function () {
            $(this).addClass("disabled");
            body.append(hocwpTheme.ajaxOverlay);
        });
        body.on("hocwpTheme:ajaxComplete", function (e, button, response) {
            button.removeClass("disabled");
            body.find(".hocwp-theme.ajax-overlay").fadeOut().remove();
        });
    })();
});