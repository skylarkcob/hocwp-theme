jQuery(document).ready(function ($) {
    $.fn.hocwpDatePicker = function (options) {
        if ($.fn.datepicker) {
            var settings = $.extend({}, $.fn.hocwpDatePicker.defaults, options);

            return this.each(function () {
                var element = $(this),
                    dateFormat = element.attr("data-date-format");

                if ($.trim(dateFormat)) {
                    settings.dateFormat = dateFormat;
                }

                element.datepicker(settings);
            });
        }

        return this;
    };

    $.fn.hocwpDatePicker.defaults = {};

    (function () {
        $(".hocwp-theme .datepicker,input[data-date-picker='1'],input[data-datetime-picker='1']").hocwpDatePicker();
    })();
});