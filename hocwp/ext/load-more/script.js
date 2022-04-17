window.HTELoadMore = window.HTELoadMore || {};

jQuery(document).ready(function ($) {
    const body = $("body");

    let pagination = body.find(".pagination");

    if (pagination && pagination.length) {
        pagination.children("li").hide();
        pagination.append("<li class='pagination-item page-item'>" + HTELoadMore.button + "</li>");

        body.on("click", ".pagination .load-more", function (e) {
            e.preventDefault();

            let that = this,
                element = $(that),
                next = pagination.find(".current, .active").closest("li").next("li").find("a");

            if (next && next.length) {
                element.attr("data-text", element.text());
                element.text(element.attr("data-loading"));
                element.addClass("disabled");

                $.get(next.attr("href"), function (response) {
                    let html = $(response),
                        newPagination = html.find(".pagination"),
                        loop = newPagination.prev(".loop");

                    if (loop && loop.length) {
                        pagination.prev(".loop").append(loop.html());
                        newPagination.children("li").hide();
                        newPagination.append("<li class='pagination-item page-item'>" + HTELoadMore.button + "</li>");
                        pagination.replaceWith(newPagination);
                        pagination = body.find(".pagination");
                    } else {
                        element.hide();
                        element.closest(".pagination").hide();
                    }

                    element.text(element.attr("data-text"));
                    element.removeClass("disabled");
                });
            } else {
                element.hide();
                element.closest(".pagination").hide();
            }
        });
    }
});