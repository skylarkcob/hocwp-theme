jQuery(document).ready(function ($) {
    var body = $("body");

    $.fn.hocwpSetBooleanMetaStatus = function (options) {
        return this.each(function () {
            var element = $(this),
                meta_type = element.attr("data-meta-type"),
                meta_key = element.attr("data-meta-key"),
                meta_value = element.attr("data-meta-value"),
                object_id = element.attr("data-id");

            var settings = $.extend({}, $.fn.hocwpSetBooleanMetaStatus.defaults, options);

            if (!$.trim(meta_type)) {
                meta_type = settings.meta_type;
            }

            element.on("click", function () {
                $.ajax({
                    type: "POST",
                    dataType: "JSON",
                    url: hocwpTheme.ajaxUrl,
                    cache: true,
                    data: {
                        action: "hocwp_theme_boolean_meta_ajax",
                        meta_type: meta_type,
                        meta_key: meta_key,
                        meta_value: meta_value,
                        object_id: object_id
                    },
                    success: function (response) {
                        if (response.success) {
                            meta_value = response.data.meta_value;

                            if (1 === meta_value) {
                                element.addClass("active");
                            } else {
                                element.removeClass("active");
                            }

                            element.attr("data-meta-value", meta_value);
                        }

                        element.trigger("hocwpThemeBooleanMeta:ajaxSuccess", [element, response]);
                    },
                    complete: function (response) {
                        body.trigger("hocwpTheme:ajaxComplete", [element, response]);
                    }
                });
            });
        });
    };

    $.fn.hocwpSetBooleanMetaStatus.defaults = {
        meta_type: 'post'
    };

    (function () {
        $("[data-boolean-meta='1']").hocwpSetBooleanMetaStatus();
    })();
});