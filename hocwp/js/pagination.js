window.hocwpTheme = window.hocwpTheme || {};

jQuery(document).ready(function ($) {
    var body = $("body");

    (function () {
        var paginationFinder = ".hocwp-pagination[data-ajax='1']",
            paginationList = body.find(paginationFinder),
            loadMore = parseInt(paginationList.data("load-more"));

        if (1 == loadMore) {
            var loadMoreButton = hocwpTheme.loadMoreButton;

            if ($.trim(loadMoreButton)) {
                $(loadMoreButton).insertAfter(paginationList);
            }

            body.on("click", paginationFinder + " + a.load-more-button", function (e) {
                e.preventDefault();

                var that = this,
                    element = $(that);

                if (!paginationList || !paginationList.length) {
                    paginationList = element.prev();
                }

                var nextItem = body.find(paginationFinder + " a.next");

                if (nextItem && nextItem.length) {
                    nextItem.trigger("click");
                }
            });
        }

        function hocwpThemePaginationAjax(element, href, list, pagination, hasListId, listId, recheck) {
            if ($.trim(href)) {
                recheck = recheck || false;

                body.trigger("hocwpTheme:ajaxStart", [element]);

                var loadMore = parseInt(pagination.data("load-more"));

                $.get(href, function (response) {
                    var res = $(response),
                        newList = null,
                        newPagination = null,
                        html = null;

                    if (hasListId) {
                        newList = res.find("#" + listId);
                    } else {
                        newList = res.find(paginationFinder).prev();
                    }

                    if (newList && newList.length) {
                        html = newList.html();
                    } else {
                        html = "";
                    }

                    if ($.trim(html)) {
                        if (1 == loadMore) {
                            list.append(html);
                        } else {
                            list.html(html);
                        }

                        pagination.find(".page-numbers").removeClass("current");

                        if (hasListId) {
                            newPagination = res.find(".hocwp-pagination[data-list='" + listId + "']");
                        }

                        if (!newPagination || !newPagination.length) {
                            newPagination = newList.next(paginationFinder);
                        }

                        if (1 == loadMore) {
                            var tmpNext = newPagination.find("a.next");

                            if (!tmpNext || !tmpNext.length) {
                                pagination.next("a.load-more-button").hide();
                            } else if (tmpNext && tmpNext.length) {
                                pagination.next("a.load-more-button").show();
                            }
                        }

                        pagination.replaceWith(newPagination);

                        body.trigger("hocwpTheme:ajaxDataChange");
                    } else if (recheck) {
                        href = pagination.attr("data-root-url");
                        hocwpThemePaginationAjax(element, href, list, pagination, hasListId, listId, false);
                    }

                    body.trigger("hocwpTheme:ajaxComplete", [element]);
                });
            }
        }

        body.on("click", paginationFinder + " a.page-numbers", function (e) {
            e.preventDefault();

            var that = this,
                element = $(that),
                href = element.attr("href"),
                pagination = element.closest(paginationFinder),
                listId = pagination.attr("data-list"),
                hasListId = true,
                list = null;

            if (!element.hasClass("current")) {
                if ($.trim(listId)) {
                    list = $("#" + listId);

                    if (!list || !list.length) {
                        if ("prev" == listId) {
                            list = pagination.prev();
                            listId = list.attr("id");
                        }
                    }
                }

                if (!$.trim(listId)) {
                    hasListId = false;
                }

                if (list && list.length) {
                    hocwpThemePaginationAjax(element, href, list, pagination, hasListId, listId, true);
                }
            }
        });
    })();
});