jQuery(document).ready(function ($) {
    const body = $("body");

    (function () {
        body.on("click", ".column-featured .hocwp-theme-featured", function (e) {
            e.preventDefault();
            var element = $(this);

            $.ajax({
                type: "POST",
                dataType: "JSON",
                url: hocwpTheme.ajaxUrl,
                cache: true,
                data: {
                    action: "hocwp_theme_featured_post_ajax",
                    post_id: element.attr("data-id"),
                    featured: element.attr("data-featured")
                },
                success: function (response) {
                    if (response.success) {
                        var featured = parseInt(response.featured);
                        if (1 === featured) {
                            element.addClass("active");
                        } else {
                            element.removeClass("active");
                        }
                        element.attr("data-featured", featured);
                    }
                },
                complete: function (response) {
                    body.trigger("hocwpTheme:ajaxComplete", [element, response]);
                }
            });
        });
    })();

    (function () {
        body.on("click", "input[type='submit'].action", function (e) {
            let that = this,
                element = $(that),
                action = element.prev("select"),
                form = element.closest("form"),
                checkbox = form.find("table input[type='checkbox']:checked");

            if ("change_status" === action.val() || "change_category" === action.val()) {
                e.preventDefault();

                if (checkbox && checkbox.length && 0 < parseInt(checkbox.length)) {
                    let modal = body.find(".choose-status.modal");

                    if (modal && modal.length) {
                        modal.fadeIn(200);
                    }
                }
            }
        });

        body.on("click", ".choose-status.modal .close, .hocwp-theme-modal .close-modal", function (e) {
            e.preventDefault();
            $(this).closest(".modal").fadeOut(500);
        });
    })();
});