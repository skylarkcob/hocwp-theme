window.wp = window.wp || {};

jQuery(document).ready(function ($) {
    var body = $("body");

    var widgetArea = $("#secondary").find(".widget-area");

    widgetArea.masonry({
        itemSelector: ".widget",
        columnWidth: 300,
        gutterWidth: 20,
        isRTL: body.is(".rtl")
    });

    if ("undefined" !== typeof wp && wp.customize && wp.customize.selectiveRefresh) {
        wp.customize.selectiveRefresh.bind("sidebar-updated", function (sidebarPartial) {
            if ("sidebar-1" === sidebarPartial.sidebarId) {
                widgetArea.masonry("reloadItems");
                widgetArea.masonry("layout");
            }
        });
    }
});