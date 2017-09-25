jQuery(document).ready(function ($) {
    (function () {
        $(".hocwp-theme .datepicker").each(function () {
            var element = $(this);
            element.datepicker({
                dateFormat: element.attr("data-date-format")
            });
        });
    })();
});