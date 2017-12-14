jQuery(document).ready(function ($) {
    var body = $("body");

    if (!body.hasClass("widgets-php")) {
        return;
    }

    function hocwpSelectChosen(select) {
        var chosenOptions = {
            width: "100%"
        };
        if ($.fn.chosen && select.length) {
            if (1 !== select.attr("data-loaded")) {
                select.chosen(chosenOptions);
                select.attr("data-loaded", 1);
                select.next(".chosen-container").show();
            }
        }
    }

    (function () {
        hocwpSelectChosen($("select[data-chosen='1']"));

        $(document).on("widget-updated", function (event, widget) {
            hocwpSelectChosen($(widget).find("select[data-chosen='1']"));
        });

        $("div.widgets-sortables").bind("sortreceive", function (event, ui) {
            //hocwpSelectChosen($(ui.item).find("select[data-chosen='1']"));
        }).bind("sortstop", function (event, ui) {
            //hocwpSelectChosen($(ui.item).find("select[data-chosen='1']"));
        });

        $(document).ajaxSuccess(function (e, xhr, settings) {
            if ("undefined" === typeof settings.data) {
                return;
            }
            if (settings.data.search("action=save-widget") !== -1) {
                var container = $(this),
                    chosenContainer = container.find(".chosen-container");
                if (chosenContainer.length > 0) {
                    chosenContainer.hide();
                }
                hocwpSelectChosen(container.find("select[data-chosen='1']"));
            }
        });

    })();
});