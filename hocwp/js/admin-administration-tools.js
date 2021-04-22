window.hocwpTheme = window.hocwpTheme || {};

jQuery(document).ready(function ($) {
    const body = $("body");

    // Change site URL
    (function () {
        body.on("click", "form[data-tab='administration_tools'] button[data-change-url='1'], form[data-tab='administration_tools'] input[data-change-url='1']", function (e) {
            e.preventDefault();

            var that = this,
                element = $(that),
                form = element.closest("form"),
                oldUrl = form.find(".old_url input"),
                newUrl = form.find(".new_url input");

            if (confirm(element.attr("data-confirm-message"))) {
                if (!$.trim(oldUrl.val())) {
                    oldUrl.focus();
                    element.removeClass("disabled");
                    element.blur();
                } else if (!$.trim(newUrl.val())) {
                    newUrl.focus();
                    element.removeClass("disabled");
                    element.blur();
                } else {
                    $.ajax({
                        type: "POST",
                        dataType: "JSON",
                        url: hocwpTheme.ajaxUrl,
                        cache: true,
                        data: {
                            action: "hocwp_theme_change_site_url",
                            old_url: oldUrl.val(),
                            new_url: newUrl.val()
                        },
                        success: function (response) {
                            if (response.success) {
                                alert(element.attr("data-message"));
                            }
                        },
                        complete: function (response) {
                            body.trigger("hocwpTheme:ajaxComplete", [element, response]);
                        }
                    });
                }
            } else {
                element.removeClass("disabled");
                element.blur();
            }
        });
    })();

    // Import administrative boundaries
    (function () {
        body.on("click", "form[data-tab='administration_tools'] button[data-import-ab='1'], form[data-tab='administration_tools'] input[data-import-ab='1']", function (e) {
            e.preventDefault();

            var that = this,
                element = $(that),
                form = element.closest("form"),
                district = form.find(".district input"),
                commune = form.find(".commune input"),
                taxonomy = form.find(".ab_taxonomy select");

            if (!$.trim(taxonomy.val())) {
                taxonomy.focus();
                element.removeClass("disabled");
                element.blur();
            } else {
                $.ajax({
                    type: "POST",
                    dataType: "JSON",
                    url: hocwpTheme.ajaxUrl,
                    cache: true,
                    data: {
                        action: "hocwp_theme_import_administrative_boundaries",
                        district: district.is(":checked") ? 1 : 0,
                        commune: commune.is(":checked") ? 1 : 0,
                        taxonomy: taxonomy.val()
                    },
                    success: function (response) {
                        if (response.success) {
                            alert(element.attr("data-message"));
                        }
                    },
                    complete: function (response) {
                        body.trigger("hocwpTheme:ajaxComplete", [element, response]);
                    }
                });
            }
        });
    })();
});