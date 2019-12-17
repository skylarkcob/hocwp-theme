jQuery(document).ready(function ($) {
    var body = $("body");

    if (!body.hasClass("widgets-php")) {
        return;
    }

    (function () {
        var widgets = body.find(".hocwp_widget_tabber");

        widgets.each(function () {
            var widget = $(this).closest(".widget"),
                sidebar = widget.closest(".widgets-sortables"),
                sidebarID = sidebar.attr("id");

            widget.find("select option[value='" + sidebarID + "']").remove();
        });
    })();

    (function () {
        /*
         * On widget saved event.
         */
        $(document).on("widget-updated", function (event, widget) {
            widget = $(widget);

            var sidebar = widget.closest(".widgets-sortables"),
                sidebarID = sidebar.attr("id"),
                widgetID = widget.attr("id");

            if (widgetID.indexOf("hocwp_widget_tabber")) {
                widget.find("select option[value='" + sidebarID + "']").remove();
            } else {
                widget.find("select[data-chosen='1']").hocwpSelectChosen();
                widget.find("[data-sortable='1']").hocwpSortable();

                widget.find("textarea[data-code-editor='1']").parent().children(".CodeMirror").remove();
                widget.find("textarea[data-code-editor='1']").hocwpCodeEditor();
            }
        });

        /*
         * On ajax success event.
         */
        $(document).ajaxSuccess(function (e, xhr, settings) {
            if ("undefined" === typeof settings.data) {
                return;
            }

            if (settings.data.search("action=save-widget") !== -1) {
                var container = $(this),
                    sidebarID = hocwpTheme.getParamByName(settings.data, "sidebar"),
                    sidebar = $("#" + sidebarID),
                    widgetIDBase = hocwpTheme.getParamByName(settings.data, "id_base"),
                    widgetID = hocwpTheme.getParamByName(settings.data, "widget-id"),
                    widget = container.find("div[id$='" + widgetID + "']");

                if ("hocwp_widget_tabber" == widgetIDBase) {
                    sidebar.find("select option[value='" + sidebarID + "']").remove();
                } else {
                    var chosenContainer = widget.find(".chosen-container");

                    if (chosenContainer.length > 0) {
                        chosenContainer.hide();
                    }

                    container.find("select[data-chosen='1']").hocwpSelectChosen();
                    container.find("[data-sortable='1']").hocwpSortable();

                    container.find("textarea[data-code-editor='1']").parent().children(".CodeMirror").remove();
                    container.find("textarea[data-code-editor='1']").hocwpCodeEditor();
                }
            }
        });

    })();

    (function () {
        body.on("click", ".widget .hocwp-theme .nav-tab", function (e) {
            e.preventDefault();

            var that = this,
                element = $(that),
                widget = element.closest(".widget"),
                tabWrapper = element.parent(),
                href = element.attr("href"),
                pane = widget.find(href);

            if (!element.hasClass("nav-tab-active")) {
                pane.parent().children(".tab-pane").removeClass("active");
                pane.addClass("active");
                tabWrapper.children(".nav-tab").removeClass("active nav-tab-active");
                element.addClass("active nav-tab-active");
            }
        });
    })();
});