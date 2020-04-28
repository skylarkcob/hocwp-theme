window.wp = window.wp || {};

jQuery(document).ready(function ($) {
    var body = $("body");

    $.fn.hocwpCodeEditor = function (options) {
        if (wp.codeEditor) {
            var settings = $.extend(wp.codeEditor.defaultSettings, $.fn.hocwpCodeEditor.defaults, options);

            return this.each(function () {
                var defaultSettings = settings,
                    element = $(this),
                    codeMirror = element.next(),
                    widget = element.closest("div.widget"),
                    mode = element.attr("data-mode");

                if (!$.trim(mode)) {
                    mode = "htmlmixed";
                }

                defaultSettings.codemirror.mode = mode;

                var instance = null;

                if (!element.hasClass("initialized") || null === instance) {
                    instance = wp.codeEditor.initialize(element, defaultSettings);
                    element.addClass("initialized");
                }

                instance.codemirror.setSize(null, element.height());

                if ("widgets" === window.pagenow && widget && widget.length) {
                    var widgetContainers;

                    widgetContainers = $(".widgets-holder-wrap:not(#available-widgets)").find("div.widget");
                    widgetContainers.one("click.toggle-widget-expanded", function toggleWidgetExpanded() {
                        var widgetContainer = $(this);

                        if (widgetContainer.is(widget)) {
                            setTimeout(function () {
                                instance.codemirror.refresh();
                            }, 100);
                        }
                    });

                    setTimeout(function () {
                        instance.codemirror.refresh();
                    }, 100);

                    codeMirror.attr({
                        "role": "textbox",
                        "aria-multiline": "true",
                        "aria-labelledby": element.attr("id") + "-label",
                        "aria-describedby": "editor-keyboard-trap-help-1 editor-keyboard-trap-help-2 editor-keyboard-trap-help-3 editor-keyboard-trap-help-4"
                    });

                    $("#" + element.attr("id") + "-label").on("click", function () {
                        instance.codemirror.focus();
                    });

                    element.on("change", function () {
                        if (this.value !== instance.codemirror.getValue()) {
                            instance.codemirror.setValue(this.value);
                        }
                    });

                    instance.codemirror.on("change", function () {
                        var value = instance.codemirror.getValue();

                        if (value !== element.val()) {
                            element.val(value).trigger("change");
                        }
                    });

                    if (wp.customize) {
                        instance.codemirror.on("keydown", function onKeydown(codemirror, event) {
                            var escKeyCode = 27;

                            if (escKeyCode === event.keyCode) {
                                event.stopPropagation();
                            }
                        });
                    }
                }
            });
        }

        return this;
    };

    $.fn.hocwpCodeEditor.defaults = {
        autoRefresh: true
    };

    (function () {
        $("textarea[data-code-editor='1']").hocwpCodeEditor();

        if (body.hasClass("term-desc-html")) {
            var termDesc = $(".term-php .form-table").find("textarea#description");

            if (termDesc && termDesc.length) {
                termDesc.attr("rows", 20);
                termDesc.hocwpCodeEditor();
            }
        }
    })();
});