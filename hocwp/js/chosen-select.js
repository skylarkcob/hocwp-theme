jQuery(document).ready(function ($) {
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
        $("select[data-chosen='1']").each(function () {
            hocwpSelectChosen($(this));
        });
    })();
});