jQuery(document).ready(function ($) {
    (function () {
        $(".hide-column-tog").on("change", function () {
            var that = this,
                element = $(that),
                id = element.attr("id"),
                name = id.replace("-hide", "");

            if (element.prop("checked")) {
                $(".custom-fields.hocwp-theme > .field-" + name).removeClass("hidden-field");
            } else {
                $(".custom-fields.hocwp-theme > .field-" + name).addClass("hidden-field");
            }
        });
    })();
});