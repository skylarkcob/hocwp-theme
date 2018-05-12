window.wp = window.wp || {};

jQuery(document).ready(function ($) {
    $.fn.hocwpCodeEditor = function (options) {
        if (wp.codeEditor) {
            var settings = $.extend(wp.codeEditor.defaultSettings, $.fn.hocwpCodeEditor.defaults, options);

            return this.each(function () {
                var defaultSettings = settings,
                    element = $(this),
                    mode = element.attr("data-mode");

                if (!$.trim(mode)) {
                    mode = "htmlmixed";
                }

                defaultSettings.codemirror.mode = mode;
                var instance = wp.codeEditor.initialize(element, defaultSettings);
                instance.codemirror.setSize(null, element.height());
            });
        }

        return this;
    };

    $.fn.hocwpCodeEditor.defaults = {};

    (function () {
        $("textarea[data-code-editor='1']").hocwpCodeEditor();
    })();
});