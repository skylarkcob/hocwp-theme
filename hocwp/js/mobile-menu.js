(function () {
    var container, button, menu, items, link, subMenu, i, len;

    container = document.getElementById("mobile-navigation");

    if (!container) {
        container = document.getElementById("site-navigation");
    }

    if (!container) {
        return;
    }

    button = container.getElementsByTagName("button")[0];

    if ("undefined" === typeof button) {
        return;
    }

    button.style.maxWidth = "60px";

    menu = container.getElementsByTagName("ul")[0];

    var mobileMenuClass = " mobile-menu",
        parent = container.parentNode;

    parent.style.position = "relative";

    window.onresize = function () {
        if (window.innerWidth > mobileWidth) {
            button.style.display = "none";

            if ("undefined" !== typeof menu) {
                menu.className = menu.className.replace(mobileMenuClass, "");
            }

            container.className = container.className.replace(mobileMenuClass, "");
        } else {
            button.style.display = "block";

            if ("undefined" !== typeof menu) {
                menu.className += mobileMenuClass;
            }
            
            container.className += mobileMenuClass;
        }
    };

    var mobileWidth = document.getElementsByTagName("body")[0].getAttribute("data-mobile-width");

    if (window.innerWidth > mobileWidth) {
        button.style.display = "none";
        return;
    }

    button.style.display = "block";

    if ("undefined" === typeof menu) {
        return;
    }

    menu.addEventListener("click", function (e) {
        if ("UL" === e.target.tagName) {
            button.click();
        }
    });

    container.className += mobileMenuClass;
    menu.className += mobileMenuClass;

    button.onclick = function () {
        if (-1 !== container.className.indexOf("toggled")) {
            container.className = container.className.replace(" toggled", "");
            button.setAttribute("aria-expanded", "false");
            menu.setAttribute("aria-expanded", "false");
        } else {
            container.className += " toggled";
            button.setAttribute("aria-expanded", "true");
            menu.setAttribute("aria-expanded", "true");
        }
    };

    items = menu.getElementsByClassName("menu-item-has-children");

    for (i = 0, len = items.length; i < len; i++) {
        link = items[i].getElementsByTagName("a")[0];

        if ("undefined" !== typeof link) {
            link.addEventListener("click", clickMenuItemHasChildren, true);
        }
    }

    function clickMenuItemHasChildren(e) {
        e.preventDefault();
        e.stopPropagation();
        subMenu = this.parentNode.getElementsByTagName("ul")[0];

        if ("undefined" !== typeof subMenu) {
            this.parentNode.className = this.parentNode.className.replace(" focus", "");

            if (-1 !== this.className.indexOf("toggled")) {
                this.className = container.className.replace(" toggled", "");
                this.setAttribute("aria-expanded", "false");
                subMenu.setAttribute("aria-expanded", "false");
            } else {
                this.className += " toggled";
                this.setAttribute("aria-expanded", "true");
                subMenu.setAttribute("aria-expanded", "true");
            }
        }
    }
})();