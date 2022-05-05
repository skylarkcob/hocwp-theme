window.hocwpTheme = window.hocwpTheme || {};

jQuery(document).ready(function ($) {
    let body = $("body");

    (function () {
        let unSave = false,
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

            let confirmationMessage = hocwpTheme.l10n.beforeUnloadConfirmMessage;

            (e || window.event).returnValue = confirmationMessage;

            return confirmationMessage;
        });
    })();

    // Fix current submenu but parent menu not open.
    (function () {
        function wpFixMenuNotOpen(menuItem) {
            if (menuItem.length) {
                let topMenu = menuItem.closest("li.menu-top"),
                    notCurrentClass = "wp-not-current-submenu";

                if (topMenu.hasClass(notCurrentClass)) {
                    let openClass = "wp-has-current-submenu wp-menu-open";
                    topMenu.removeClass(notCurrentClass).addClass(openClass);
                    topMenu.children("a").removeClass(notCurrentClass).addClass(openClass);
                }
            }
        }

        $(".wp-has-submenu .wp-submenu li.current").each(function () {
            let that = this,
                element = $(that);

            wpFixMenuNotOpen(element);
        });

        if (body.hasClass("post-new-php") || body.hasClass("post-php")) {
            let postType = body.find("#post_type");

            if (postType.length && $.trim(postType.val())) {
                let menuLink = body.find("a[href='edit.php?post_type=" + postType.val() + "']");

                if (menuLink.length) {
                    let menuItem = menuLink.parent();

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
                listChild = list.children(),
                count = listChild.length;

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

            let that = this,
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

            let sTop = null;

            if (sticky && sticky.length) {
                sTop = sticky.offset().top + $(".hocwp-theme .settings-box .module-header").height();
            }

            let sBottom = settingsBox.find(".module-footer").offset().top - hocwpTheme.object.screenHeight() - 120;

            if (!form || !form.length || form.height() > sidebar.height()) {
                hocwpTheme.object.sticky($, sticky, sTop, "", sBottom);

                if (sidebar.hasClass("has-sticky")) {
                    if (settingsBox.find(".settings-content").height() > sidebar.height()) {
                        hocwpTheme.object.sticky($, sidebar, sideTop, "", sBottom);
                    }
                }

                $(window).scroll(function () {
                    sBottom = settingsBox.find(".module-footer").offset().top - hocwpTheme.object.screenHeight() - 120;

                    hocwpTheme.object.sticky($, sticky, sTop, "", sBottom);

                    if (sidebar.hasClass("has-sticky")) {
                        if (settingsBox.find(".settings-content").height() > sidebar.height()) {
                            hocwpTheme.object.sticky($, sidebar, sideTop, "", sBottom);
                        }
                    }
                });
            }
        }
    })();

    // Collapse expand setting row
    (function () {
        body.on("click", ".hocwp-theme .form-table th .dashicons-admin-collapse", function (e) {
            e.preventDefault();

            let that = this,
                element = $(that),
                row = element.closest("tr");

            row.children("td").slideUp();
            element.hide();

            row.find(".dashicons-editor-expand").fadeIn();

            row.addClass("collapsed");
        });

        body.on("click", ".hocwp-theme .form-table th .dashicons-editor-expand", function (e) {
            e.preventDefault();

            let that = this,
                element = $(that),
                row = element.closest("tr");

            row.removeClass("collapsed");

            row.children("td").slideDown();
            element.hide();

            row.find(".dashicons-admin-collapse").fadeIn();
        });
    })();
});