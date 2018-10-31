window.hocwpTheme = window.hocwpTheme || {};

jQuery(document).ready(function ($) {
    var body = $("body");

    $.fn.hocwpUpdateMeta = function (options) {
        return this.each(function () {
            var element = $(this),
                meta_type = element.attr("data-meta-type"),
                meta_key = element.attr("data-meta-key"),
                meta_value = element.attr("data-meta-value"),
                object_id = element.attr("data-id");

            var settings = $.extend({}, $.fn.hocwpUpdateMeta.defaults, options);

            if (!$.trim(meta_type)) {
                meta_type = settings.meta_type;
            }

            element.on("click", function () {
                var value_type = element.attr("data-value-type"),
                    requireLogin = parseInt(element.attr("data-require-login"));

                if (1 == requireLogin && !body.hasClass("logged-in")) {
                    window.location.href = hocwpTheme.loginUrl;
                    return;
                }

                body.trigger("hocwpTheme:ajaxStart", [element]);

                $.ajax({
                    type: "POST",
                    dataType: "JSON",
                    url: hocwpTheme.ajaxUrl,
                    cache: true,
                    data: {
                        action: "hocwp_theme_ajax",
                        callback: "update_meta",
                        method: "post",
                        meta_type: meta_type,
                        meta_key: meta_key,
                        meta_value: meta_value,
                        object_id: object_id,
                        value_type: value_type,
                        change_id: element.attr("data-object-id"),
                        nonce: hocwpTheme.nonce
                    },
                    success: function (response) {
                        if (response.success) {
                            meta_value = response.data.meta_value;

                            element.attr("data-meta-value", meta_value);

                            var container = element.parent(),
                                displayResult = container.find(element.attr("data-display-result"));

                            if (displayResult.length && response.data.formatted_meta_value) {
                                displayResult.html(response.data.formatted_meta_value);
                            }

                            if ("up_down" === value_type) {
                                element.addClass("disabled");
                                element.prop("disabled", true);
                            }

                            var doText = element.attr("data-text"),
                                undoText = element.attr("data-undo-text");

                            if ("undo" == response.data.job_action && $.trim(doText)) {
                                element.html(doText);
                            } else if ("do" == response.data.job_action && $.trim(undoText)) {
                                element.html(undoText);
                            }
                        }

                        element.trigger("hocwpUpdateMeta:ajaxSuccess", [element, response]);

                        if (response.data && response.data.message && $.trim(response.data.message)) {
                            alert(response.data.message);
                        }
                    },
                    complete: function (response) {
                        body.trigger("hocwpTheme:ajaxComplete", [element, response]);
                        element.blur();
                    }
                });
            });
        });
    };

    $.fn.hocwpUpdateMeta.defaults = {
        meta_type: 'post'
    };

    (function () {
        $("[data-ajax-meta='1']").hocwpUpdateMeta();
    })();
});