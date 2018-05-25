jQuery(document).ready(function ($) {
    $.fn.hocwpSelectChosen = function (options) {
        if ($.fn.chosen) {
            var settings = $.extend({
                width: "100%"
            }, $.fn.hocwpSelectChosen.defaults, options);

            return this.each(function () {
                var element = $(this);

                if (1 !== element.attr("data-loaded")) {
                    element.chosen(settings);
                    element.attr("data-loaded", 1);
                    element.next(".chosen-container").show();
                }
            });
        }

        return this;
    };

    $.fn.hocwpSelectChosen.defaults = {
        width: "100%"
    };

    (function () {
        $("select[data-chosen='1'], #parent_id").hocwpSelectChosen();
    })();
});