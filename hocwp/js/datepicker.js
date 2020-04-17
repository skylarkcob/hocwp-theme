jQuery(document).ready(function ($) {
    function hocwpThemeConvertDateAttr(date) {
        if (0 == date) {
            return date;
        }

        if ($.isNumeric(date)) {
            return date;
        }

        if (date) {
            var tmp = new Date(date);

            if (Object.prototype.toString.call(tmp) === "[object Date]") {
                if (isNaN(tmp.getTime())) {
                    return date;
                } else {
                    return tmp;
                }
            } else {
                return date;
            }
        }

        return date;
    }

    $.fn.hocwpDatePicker = function (options) {
        if ($.fn.datepicker) {
            var settings = $.extend({}, $.fn.hocwpDatePicker.defaults, options);

            return this.each(function () {
                var element = $(this),
                    dateFormat = element.attr("data-date-format"),
                    minDate = element.attr("data-mindate"),
                    maxDate = element.attr("data-maxdate");

                if ($.trim(dateFormat)) {
                    settings.dateFormat = dateFormat;
                }

                if ($.trim(minDate)) {
                    minDate = hocwpThemeConvertDateAttr(minDate);
                    settings.minDate = minDate;
                }

                if ($.trim(maxDate)) {
                    maxDate = hocwpThemeConvertDateAttr(maxDate);
                    settings.maxDate = maxDate;
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