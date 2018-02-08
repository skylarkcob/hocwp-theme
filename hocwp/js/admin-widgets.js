jQuery(document).ready(function ($) {
    var body = $("body");

    if (!body.hasClass("widgets-php")) {
        return;
    }

    (function () {
        $(document).on("widget-updated", function (event, widget) {
            $(widget).find("select[data-chosen='1']").hocwpSelectChosen();
            $(widget).find("[data-sortable='1']").hocwpSortable();
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
                container.find("select[data-chosen='1']").hocwpSelectChosen();
                container.find("[data-sortable='1']").hocwpSortable();
            }
        });

    })();
});