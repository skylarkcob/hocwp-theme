jQuery(document).ready(function ($) {
    $.fn.hocwpColorPicker = function (options) {
        if ($.fn.iris) {
            var settings = $.extend({}, $.fn.hocwpColorPicker.defaults, options);

            return this.each(function () {
                var element = $(this);

                element.wpColorPicker(settings);
            });
        }

        return this;
    };

    $.fn.hocwpColorPicker.defaults = {};

    (function () {
        $("input[data-color-picker='1']").hocwpColorPicker();
    })();
});