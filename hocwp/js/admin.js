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
});