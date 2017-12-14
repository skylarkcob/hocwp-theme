jQuery(document).ready(function ($) {
    var body = $("body");

    (function () {
        var shortables = $("[data-sortable='1']");
        if (shortables.length) {
            $.each(shortables, function () {
                var shortable = $(this),
                    container = shortable.parent(),
                    connectWith = shortable.attr("data-connect-with"),
                    options = {
                        placeholder: "ui-state-highlight",
                        cancel: ".ui-state-disabled",
                        items: "li:not(.ui-state-disabled)",
                        sort: function (event, ui) {
                            var that = $(this),
                                container = that.parent(),
                                sortableResult = container.find(".connected-result"),
                                uiStateHighlight = that.find(".ui-state-highlight");
                            if (sortableResult.length) {
                                //sortableResult.css({height: "auto"});
                            }
                            uiStateHighlight.css({height: ui.item.height()});
                            if (that.hasClass("display-inline")) {
                                uiStateHighlight.css({width: ui.item.width()});
                            }
                            body.trigger("hocwpTheme:sortableSort", [ui, that]);
                        },
                        stop: function (event, ui) {
                            var that = $(this),
                                container = that.parent(),
                                input = container.find("input"),
                                sortableResult = container.find(".connected-result"),
                                elementHeight = that.height(),
                                sortableResultHeight = sortableResult.height(),
                                listType = sortableResult.attr("data-list-type"),
                                topParent = container.parents(".hocwp-theme-sortable").last();

                            if (topParent.length) {
                                container = topParent.parent();
                                input = container.find("input");
                                sortableResult = container.find(".connected-result");
                                sortableResultHeight = sortableResult.height();
                                listType = sortableResult.attr("data-list-type");
                                elementHeight = topParent.height();
                            }

                            switch (listType) {
                                case "term":
                                    var value = [];
                                    sortableResult.find("li").each(function () {
                                        var element = $(this),
                                            item = {
                                                id: element.attr("data-id"),
                                                taxonomy: element.attr("data-taxonomy")
                                            };
                                        value.push(item);
                                    });
                                    value = JSON.stringify(value);
                                    input.val(value);
                                    break;
                            }

                            if (elementHeight >= sortableResultHeight) {
                                //sortableResult.css({height: elementHeight + "px"});
                            } else {
                                //sortableResult.css({height: "auto"});
                            }

                            body.trigger("hocwpTheme:sortableStop", [ui, that]);
                        },
                        update: function (event, ui) {
                            var that = $(this),
                                container = that.parent(),
                                item = $(ui.item),
                                receiver = item.closest(".sortable"),
                                connectList = item.attr("data-connect-list");

                            if (!that.hasClass("connected-result")) {
                                //that.css({height: "auto"});
                            }

                            if (connectList && $.trim(connectList)) {
                                if (!receiver.hasClass(connectList) && !receiver.hasClass("connected-result")) {
                                    var tmp = item.detach(),
                                        thisParent = container.find("." + connectList).not(".connected-result");

                                    if (thisParent.length) {
                                        //thisParent.css({height: "auto", minHeight: "50px"});
                                        thisParent.append(tmp);
                                    }
                                }
                            }
                        }
                    };

                if ($.trim(connectWith)) {
                    connectWith = connectWith.replace(" ", ", .");
                    options.connectWith = "." + connectWith;
                    var lists = container.find(".sortable"),
                        first = lists.first(),
                        last = lists.last();

                    if (first.length && last.length) {
                        if (first.height() < last.height()) {
                            //first.css({height: last.height()});
                        } else {
                            //last.css({height: first.height()});
                        }
                    }
                }

                shortable.sortable(options).disableSelection();
            });
        }
    })();
});