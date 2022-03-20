window.hocwpTheme = window.hocwpTheme || {};

jQuery(document).ready(function ($) {
    var body = $("body");

    (function () {
        var unSave = false,
            formOptions = $(".hocwp-theme #hocwpOptions");

        formOptions.find("input, textarea, select").on("input change paste keyup", function () {
            unSave = true;
        });

        formOptions.on("hocwpTheme:formDataChange", function () {
            unSave = true;
        });

        formOptions.on("submit", function () {
            unSave = false;
        });

        window.addEventListener("beforeunload", function (e) {
            if (!unSave) {
                return;
            }

            var confirmationMessage = hocwpTheme.l10n.beforeUnloadConfirmMessage;

            (e || window.event).returnValue = confirmationMessage;

            return confirmationMessage;
        });
    })();

    // Fix current submenu but parent menu not open.
    (function () {
        function wpFixMenuNotOpen(menuItem) {
            if (menuItem.length) {
                var topMenu = menuItem.closest("li.menu-top"),
                    notCurrentClass = "wp-not-current-submenu";

                if (topMenu.hasClass(notCurrentClass)) {
                    var openClass = "wp-has-current-submenu wp-menu-open";
                    topMenu.removeClass(notCurrentClass).addClass(openClass);
                    topMenu.children("a").removeClass(notCurrentClass).addClass(openClass);
                }
            }
        }

        $(".wp-has-submenu .wp-submenu li.current").each(function () {
            var that = this,
                element = $(that);

            wpFixMenuNotOpen(element);
        });

        if (body.hasClass("post-new-php") || body.hasClass("post-php")) {
            var postType = body.find("#post_type");

            if (postType.length && $.trim(postType.val())) {
                var menuLink = body.find("a[href='edit.php?post_type=" + postType.val() + "']");

                if (menuLink.length) {
                    var menuItem = menuLink.parent();

                    menuItem.addClass("current");
                    wpFixMenuNotOpen(menuItem);
                }
            }
        }
    })();

    (function () {
        body.on("click", "button.add-data-html", function (e) {
            e.preventDefault();

            let that = this,
                element = $(that),
                box = element.closest(".allow-add-data"),
                list = box.find("ul"),
                base = list.find(".base-data"),
                clone = base.clone(),
                count = parseInt(list.children().length);

            clone.removeClass("base-data");

            clone.find("input").each(function () {
                $(this).attr("id", $(this).attr("id") + count);
            });

            clone = clone.prop("outerHTML").replace(/%count%/g, count.toString());

            list.append(clone.toString());
            list.children().not(".base-data").show();
        });

        body.on("click", ".allow-add-data li .remove", function (e) {
            e.preventDefault();

            let that = this,
                element = $(that);

            if (confirm(hocwpTheme.l10n.confirmDeleteMessage)) {
                element.closest("li").remove();
            }
        });
    })();

    /*--------------------------------------------------------------
    # Modal content
    --------------------------------------------------------------*/
    (function () {
        const modal = $("#hocwpThemeModal");

        body.on("click", ".hocwp-theme .show-modal-me", function (e) {
            e.preventDefault();

            var that = this,
                element = $(that);

            modal.find(".modal-content").html(element.clone());
            modal.find(".modal-caption").html(element.attr("title"));
            modal.fadeIn();
        });

        modal.on("click", ".close", function (e) {
            e.preventDefault();
            modal.find(".modal-content").html("");
            modal.find(".modal-caption").html("");
            modal.fadeOut();
        });
    })();

    // Sticky save setting button and sidebar
    (function () {
        const settingsBox = $(".hocwp-theme .settings-box");

        if (settingsBox && settingsBox.length) {
            const sticky = settingsBox.find(".module-header input[type='submit']"),
                sidebar = settingsBox.find("#nav ul"),
                sideTop = sidebar.offset().top + (sidebar.height() / 3),
                form = settingsBox.find(".settings-content > form");

            var sTop = null;

            if (sticky && sticky.length) {
                sTop = sticky.offset().top + $(".hocwp-theme .settings-box .module-header").height();
            }

            var sBottom = settingsBox.find(".module-footer").offset().top - hocwpTheme.object.screenHeight() - 120;

            if (!form || !form.length || form.height() > sidebar.height()) {
                hocwpTheme.object.sticky($, sticky, sTop, "", sBottom);

                if (settingsBox.find(".settings-content").height() > sidebar.height()) {
                    hocwpTheme.object.sticky($, sidebar, sideTop, "", sBottom);
                }

                $(window).scroll(function () {
                    sBottom = settingsBox.find(".module-footer").offset().top - hocwpTheme.object.screenHeight() - 120;

                    hocwpTheme.object.sticky($, sticky, sTop, "", sBottom);

                    if (settingsBox.find(".settings-content").height() > sidebar.height()) {
                        hocwpTheme.object.sticky($, sidebar, sideTop, "", sBottom);
                    }
                });
            }
        }
    })();

    // Collapse expand setting row
    (function () {
        body.on("click", ".hocwp-theme .form-table th .dashicons-admin-collapse", function (e) {
            e.preventDefault();

            var that = this,
                element = $(that),
                row = element.closest("tr");

            row.children("td").slideUp();
            element.hide();

            row.find(".dashicons-editor-expand").fadeIn();

            row.addClass("collapsed");
        });

        body.on("click", ".hocwp-theme .form-table th .dashicons-editor-expand", function (e) {
            e.preventDefault();

            var that = this,
                element = $(that),
                row = element.closest("tr");

            row.removeClass("collapsed");

            row.children("td").slideDown();
            element.hide();

            row.find(".dashicons-admin-collapse").fadeIn();
        });
    })();
});