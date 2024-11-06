window.wp = window.wp || {};

jQuery(document).ready(function ($) {
    let body = $("body");

    $.fn.hocwpCodeEditor = function (options) {
        if (wp.codeEditor) {
            let settings = $.extend({}, wp.codeEditor.defaultSettings, $.fn.hocwpCodeEditor.defaults, options);

            return this.each(function () {
                let defaultSettings = settings,
                    element = $(this),
                    codeMirror = element.next(),
                    widget = element.closest("div.widget"),
                    mode = element.attr("data-mode");

                if (!$.trim(mode)) {
                    mode = "htmlmixed";
                }

                defaultSettings.codemirror.mode = mode;

                let instance = null;

                if (!element.hasClass("initialized")) {
                    instance = wp.codeEditor.initialize(element, defaultSettings);
                    element.addClass("initialized");
                }

                if (null === instance) {
                    return false;
                }

                instance.codemirror.setSize(null, element.height());

                if ("widgets" === window.pagenow && widget && widget.length) {
                    let widgetContainers;

                    widgetContainers = $(".widgets-holder-wrap:not(#available-widgets)").find("div.widget");
                    widgetContainers.one("click.toggle-widget-expanded", function toggleWidgetExpanded() {
                        let widgetContainer = $(this);

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
                        let value = instance.codemirror.getValue();

                        if (value !== element.val()) {
                            element.val(value).trigger("change");
                        }
                    });

                    if (wp.customize) {
                        instance.codemirror.on("keydown", function onKeydown(codemirror, event) {
                            let escKeyCode = 27;

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
            let termDesc = $(".term-php .form-table").find("textarea#description");

            if (termDesc && termDesc.length) {
                setTimeout(function () {
                    if ("none" !== termDesc.css("display")) {
                        let container = termDesc.closest(".wp-editor-container");

                        if (!container || !container.length) {
                            termDesc.attr("rows", 20);
                            termDesc.hocwpCodeEditor();
                        }
                    }
                }, 1000);
            }
        }
    })();
});