jQuery(document).ready(function ($) {
    var body = $("body");

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
});