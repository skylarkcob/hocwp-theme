window.hocwpTheme = window.hocwpTheme || {};

jQuery(document).ready(function ($) {
    var body = $("body"),
        ajaxOverlay = null,
        currentButton = null;

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
            if ("SELECT" == $(this).prop("tagName")) {
                return;
            }

            if (!$(this).hasClass("disabled")) {
                $(this).addClass("disabled");
                hocwpThemeAppendAjaxOverlay();
            }
        });

        body.on("change", "select[data-ajax-button='1']", function () {
            if (!$(this).hasClass("disabled")) {
                $(this).addClass("disabled");
                hocwpThemeAppendAjaxOverlay();
            }

            currentButton = $(this);
        });

        body.on("hocwpTheme:ajaxStart", function (e, button, response) {
            if (!button.hasClass("disabled")) {
                button.addClass("disabled");
                hocwpThemeAppendAjaxOverlay();
            }

            currentButton = button;
        });

        body.on("hocwpTheme:ajaxComplete", function (e, button, response) {
            button.removeClass("disabled");
            body.find(".hocwp-theme.ajax-overlay").fadeOut();

            currentButton = button;
        });
    })();

    (function () {
        $(document).keyup(function (e) {
            e = e || window.event;

            if (("key" in e && ("Escape" == e.key || "Esc" == e.key )) || e.keyCode == 27) {
                if (currentButton && currentButton.length) {
                    currentButton.removeClass("disabled");
                }

                body.find(".hocwp-theme.ajax-overlay").fadeOut();
            }
        });
    })();
});