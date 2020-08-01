jQuery(document).ready(function ($) {
    var body = $("body");

    (function () {
        $(".hide-column-tog").on("change", function () {
            var that = this,
                element = $(that),
                id = element.attr("id"),
                name = id.replace("-hide", "");

            if (element.prop("checked")) {
                $(".custom-fields.hocwp-theme > .field-" + name).removeClass("hidden-field");
            } else {
                $(".custom-fields.hocwp-theme > .field-" + name).addClass("hidden-field");
            }
        });
    })();

    // Custom admin menu list box
    (function () {
        body.on("change", ".item-check-list.hocwp-theme-custom-list input[type='checkbox']", function (e) {
            e.preventDefault();

            var that = this,
                element = $(that),
                list = element.closest("ul"),
                box = list.closest(".hocwp-theme-meta-box"),
                checked = list.find("input[type='checkbox']:checked"),
                submit = box.find(".add-to-menu button[type='submit']"),
                select = box.find(".button-controls input.select-all");

            if (checked && checked.length) {
                submit.removeClass("disabled");

                if (checked.length == list.find("input[type='checkbox']").length) {
                    select.prop("checked", true);
                } else {
                    select.prop("checked", false);
                }
            } else {
                submit.addClass("disabled");
                select.prop("checked", false);
            }
        });

        body.on("change", ".hocwp-theme-meta-box .button-controls input.select-all", function (e) {
            e.preventDefault();

            var that = this,
                element = $(that),
                box = element.closest(".hocwp-theme-meta-box"),
                list = box.find("ul.item-check-list"),
                checked = list.find("input[type='checkbox']:checked"),
                submit = box.find(".add-to-menu button[type='submit']");

            if ($(this).prop("checked")) {
                list.find("input[type='checkbox']").prop("checked", true);
                submit.removeClass("disabled");
            } else {
                list.find("input[type='checkbox']").prop("checked", false);
                submit.addClass("disabled");
            }
        });

        body.on("click", ".hocwp-theme-meta-box .add-to-menu button[type='submit']:not(.disabled)", function (e) {
            e.preventDefault();
            e.stopPropagation();

            var that = this,
                element = $(that),
                menuItems = [],
                box = element.closest(".hocwp-theme-meta-box"),
                list = box.find("ul.item-check-list"),
                checkboxes = list.find("input[type='checkbox']:checked"),
                re = /menu-item\[([^\]]*)/,
                api = window.wpNavMenu,
                processMethod = api.addMenuItemToBottom;

            // If no items are checked, bail.
            if (!checkboxes.length) {
                return false;
            }

            // Show the Ajax spinner.
            box.find(".button-controls .spinner").addClass("is-active");
            element.addClass("disabled");

            // Retrieve menu item data.
            $(checkboxes).each(function () {
                var that = this,
                    element = $(that),
                    li = element.closest("li"),
                    pm = processMethod,
                    title = li.find("input.menu-item-title").val(),
                    icon = li.find("input.menu-item-attr-title").val();

                if (this.className && -1 != this.className.indexOf("add-to-top")) {
                    pm = api.addMenuItemToTop;
                }

                if ($.trim(icon)) {
                    title = icon + " " + title;
                }

                menuItems.push({
                    url: li.find("input.menu-item-url").val(),
                    title: title,
                    icon: icon,
                    method: pm
                });
            });

            function hocwpAddCustomLinkItem(url, title, method) {
                // Add the items.
                api.addLinkToMenu(url, title, method, function () {
                    if (!menuItems.length) {
                        // Deselect the items and hide the Ajax spinner.
                        checkboxes.prop("checked", false);
                        box.find(".button-controls .select-all").prop("checked", false);
                        box.find(".button-controls .spinner").removeClass("is-active");
                        element.removeClass("disabled");
                    }
                });

            }

            var first = true;

            function hocwpLoopMenuItems() {
                var item = null;

                if (first) {
                    item = menuItems.shift();

                    if ("object" == typeof item) {
                        hocwpAddCustomLinkItem(item.url, item.title, item.method);
                    }

                    first = false;
                }

                setTimeout(function () {
                    item = menuItems.shift();

                    if ("object" == typeof item) {
                        hocwpAddCustomLinkItem(item.url, item.title, item.method);
                    }

                    if (menuItems.length) {
                        hocwpLoopMenuItems();
                    }
                }, 800);
            }

            hocwpLoopMenuItems();

            api.registerChange();
        });
    })();
});