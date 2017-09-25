window.hocwpTheme = window.hocwpTheme || {};
jQuery(document).ready(function ($) {
    var body = $("body");
    (function () {
        body.on("click", "input[data-ajax-button='1']", function (e) {
            e.preventDefault();
            var element = $(this),
                form = element.closest("form"),
                compress_css_and_js = [];
            $("tr.compress_css_and_js input[type='checkbox'].path").each(function () {
                if ($(this).is(":checked")) {
                    compress_css_and_js.push($(this).attr("value"));
                }
            });
            if (1 > compress_css_and_js.length) {
                compress_css_and_js = form.find("#development_compress_css_and_js").is(":checked");
            } else {
                compress_css_and_js = JSON.stringify(compress_css_and_js)
            }
            $.ajax({
                type: "POST",
                dataType: "json",
                url: hocwpTheme.ajaxUrl,
                cache: true,
                data: {
                    action: "hocwp_theme_execute_development",
                    compress_css_and_js: compress_css_and_js,
                    publish_release: form.find("#development_publish_release").is(":checked")
                },
                success: function (response) {

                },
                complete: function (response) {
                    body.trigger("hocwpTheme:ajaxComplete", [element, response]);
                }
            });
        });
    })();
});