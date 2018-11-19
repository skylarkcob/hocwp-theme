jQuery(document).ready(function ($) {
    var body = $("body");

    (function () {
        body.on("click", ".hocwp-pagination[data-ajax='1'] a.page-numbers", function (e) {
            e.preventDefault();

            var that = this,
                element = $(that),
                href = element.attr("href"),
                pagination = element.closest(".hocwp-pagination"),
                listId = pagination.attr("data-list"),
                list = null;

            if (!element.hasClass("current")) {
                body.trigger("hocwpTheme:ajaxStart", [element]);

                $.get(href, function (response) {
                    if ($.trim(listId)) {
                        list = $("#" + listId);

                        if (list && list.length) {
                            var html = $(response).find("#" + listId).html();

                            if ($.trim(html)) {
                                list.html($(response).find("#" + listId).html());

                                pagination.find(".page-numbers").removeClass("current");

                                pagination.replaceWith($(response).find(".hocwp-pagination[data-list='" + listId + "']"));

                                body.trigger("hocwpTheme:ajaxDataChange");
                            } else {
                                href = pagination.attr("data-root-url");

                                if ($.trim(href)) {
                                    $.get(href, function (response) {
                                        if ($.trim(listId)) {
                                            list = $("#" + listId);

                                            if (list && list.length) {
                                                var html = $(response).find("#" + listId).html();

                                                if ($.trim(html)) {
                                                    list.html($(response).find("#" + listId).html());

                                                    pagination.find(".page-numbers").removeClass("current");

                                                    pagination.replaceWith($(response).find(".hocwp-pagination[data-list='" + listId + "']"));

                                                    body.trigger("hocwpTheme:ajaxDataChange");
                                                }
                                            }
                                        }

                                        body.trigger("hocwpTheme:ajaxComplete", [element]);
                                    });
                                }
                            }
                        }
                    }

                    body.trigger("hocwpTheme:ajaxComplete", [element]);
                });
            }
        });
    })();
});