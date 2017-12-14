jQuery(document).ready(function ($) {
    $(".hocwp-theme").on("change", ".relationship", function () {
        var element = $(this),
            tagName = element.prop("tagName").toUpperCase(),
            container = element.closest("div"),
            relationGroup = element.attr("data-relation-group");
        container.find("." + relationGroup).fadeOut();
        if ("SELECT" === tagName) {
            var option = element.children("option[value='" + element.val() + "']");
            $(option.attr("data-relation")).fadeIn();
        }
    });
});