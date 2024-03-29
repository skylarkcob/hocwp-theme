window.hocwpTheme = window.hocwpTheme || {};

(function () {
    let body, container, button, menu, items, link, subMenu, i, len, siteNavigation, mobileWidth, windowWidth;

    body = document.getElementsByTagName("body")[0];

    mobileWidth = parseInt(body.getAttribute("data-mobile-width"));

    if ("number" !== typeof mobileWidth && hocwpTheme.mobileScreenWidth) {
        mobileWidth = parseInt(hocwpTheme.mobileScreenWidth);
    }

    windowWidth = window.innerWidth || window.screen.width;

    // Find primary menu container
    siteNavigation = document.getElementById("site-navigation");

    // Find mobile menu container
    container = document.getElementById("mobile-navigation");

    if (!siteNavigation) {
        siteNavigation = container;
    }

    if (!container) {
        // Find main menu container
        container = siteNavigation;

        if (siteNavigation && windowWidth <= mobileWidth) {
            // Hide site navigation
            siteNavigation.style.display = "none";
        }
    } else {
        // If site has mobile navigation then hide site navigation
        if (siteNavigation && windowWidth <= mobileWidth) {
            siteNavigation.style.display = "none";
        }
    }

    if (!container) {
        // Find site header container
        let masthead = document.getElementById("masthead");

        if (masthead) {
            // Find any menu on site header
            let tmp = masthead.getElementsByClassName("hocwp-menu")[0];

            if (tmp && "div" === tmp.tagName) {
                container = tmp;
            }
        }
    }

    // Stop function if site has no menu
    if (!container) {
        return;
    }

    let mobileMenuID = "mobile-menu",
        mobileMenu = document.getElementById(mobileMenuID);

    if (mobileMenu) {
        mobileMenu.style.display = "none";
    }

    button = container.getElementsByTagName("button")[0];

    if ("undefined" === typeof button) {
        return;
    }

    button.style.maxWidth = "60px";

    menu = container.getElementsByTagName("ul")[0];

    let mobileMenuClass = " " + mobileMenuID,
        parent = container.parentNode;

    if (null === mobileWidth || 'number' !== typeof mobileWidth || isNaN(mobileWidth) || 1 > mobileWidth) {
        return;
    }

    let siteHeader = document.getElementsByClassName("site-header")[0];

    if (siteHeader) {
        // Make site header position relative prevent menu absolute out of header
        siteHeader.style.position = "relative";
    }

    parent.style.position = "relative";

    window.onresize = function () {
        windowWidth = window.innerWidth || window.screen.width;

        if (windowWidth > mobileWidth) {
            button.style.display = "none";

            if ("undefined" !== typeof menu) {
                menu.className = menu.className.replace(mobileMenuClass, "");

                if (-1 !== menu.getAttribute("id").indexOf(mobileMenuID)) {
                    menu.style.display = "none";
                }
            }

            container.className = container.className.replace(mobileMenuClass, "");
            menu.className = menu.className.replace(mobileMenuClass, "");
        } else {
            button.style.display = "block";

            if ("undefined" !== typeof menu) {
                if (-1 === menu.className.indexOf(mobileMenuID)) {
                    menu.className += mobileMenuClass;
                }

                if (-1 !== menu.getAttribute("id").indexOf(mobileMenuID)) {
                    menu.style.display = "block";
                }
            }

            if (-1 === container.className.indexOf(mobileMenuID)) {
                container.className += mobileMenuClass;
            }
        }

        button.onclick = function () {
            onClickMobileMenuButton();
        };
    };

    if (windowWidth > mobileWidth) {
        button.style.display = "none";

        if (-1 !== menu.getAttribute("id").indexOf(mobileMenuID)) {
            menu.style.display = "none";
        }

        return;
    }

    button.style.display = "block";

    if ("undefined" === typeof menu) {
        return;
    }

    menu.style.display = "block";

    menu.addEventListener("click", function (e) {
        if ("UL" === e.target.tagName) {
            button.click();
        }
    });

    let closeButton = menu.getElementsByClassName("close-menu")[0];

    if (closeButton) {
        closeButton.addEventListener("click", function () {
            button.click();
        });
    }

    // Detect if user click outside the menu and menu control button.
    window.addEventListener("click", function (e) {
        if (!menu.contains(e.target) && !button.contains(e.target)) {
            if (-1 !== container.className.indexOf("toggled")) {
                button.click();
            }
        }
    });

    if (-1 === container.className.indexOf(mobileMenuID)) {
        container.className += mobileMenuClass;
    }

    container.style.display = "block";

    if (-1 === menu.className.indexOf(mobileMenuID)) {
        menu.className += mobileMenuClass;
    }

    function onClickMobileMenuButton() {
        // Hide mobile menu
        if (-1 !== container.className.indexOf("toggled")) {
            container.className = container.className.replace(" toggled", "");
            body.className = body.className.replace(" menu-opened", "");

            button.setAttribute("aria-expanded", "false");
            menu.setAttribute("aria-expanded", "false");
        } else { // Open mobile menu
            container.className += " toggled";
            body.className += " menu-opened";

            button.setAttribute("aria-expanded", "true");
            menu.setAttribute("aria-expanded", "true");
        }
    }

    button.onclick = function () {
        onClickMobileMenuButton();
    };

    // Add arrow to menu items have children
    items = menu.getElementsByClassName("menu-item-has-children");

    for (i = 0, len = items.length; i < len; i++) {
        link = items[i].getElementsByTagName("a")[0];

        if ("undefined" !== typeof link) {
            let span = link.getElementsByTagName("span")[0];

            if (!span) {
                span = document.createElement("span");
                span.setAttribute("class", "arrow");
                link.appendChild(span);
            }

            span.addEventListener("click", clickMenuItemHasChildren);
        }
    }

    // Run this function when user click on menu item has children
    function clickMenuItemHasChildren(e) {
        e.preventDefault();
        e.stopPropagation();

        let link = this.parentNode;
        subMenu = link.parentNode.getElementsByTagName("ul")[0];

        if ("undefined" !== typeof subMenu) {
            link.parentNode.className = link.parentNode.className.replace(" focus", "");

            if (-1 !== link.className.indexOf("toggled")) {
                link.className = link.className.replace(" toggled", "");
                link.setAttribute("aria-expanded", "false");
                subMenu.setAttribute("aria-expanded", "false");
            } else {
                link.className += " toggled";
                link.setAttribute("aria-expanded", "true");
                subMenu.setAttribute("aria-expanded", "true");
            }
        }
    }
})();