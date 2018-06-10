(function () {
    var container, button, menu, items, link, subMenu, i, len;

    container = document.getElementById("mobile-navigation");

    if (!container) {
        container = document.getElementById("site-navigation");
    }

    if (!container) {
        var tmp = document.getElementsByClassName("hocwp-menu")[0];

        if (tmp && tmp.tagName == "div") {
            container = tmp;
        }
    }

    if (!container) {
        return;
    }

    var mobileMenuID = "mobile-menu",
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

    var mobileMenuClass = " " + mobileMenuID,
        parent = container.parentNode;

    var mobileWidth = parseInt(document.getElementsByTagName("body")[0].getAttribute("data-mobile-width"));

    if (null === mobileWidth || 'number' !== typeof mobileWidth || isNaN(mobileWidth) || 1 > mobileWidth) {
        return;
    }

    parent.style.position = "relative";

    window.onresize = function () {
        if (window.innerWidth > mobileWidth) {
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
    };

    if (window.innerWidth > mobileWidth) {
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

    if (-1 === container.className.indexOf(mobileMenuID)) {
        container.className += mobileMenuClass;
    }

    container.style.display = "block";

    if (-1 === menu.className.indexOf(mobileMenuID)) {
        menu.className += mobileMenuClass;
    }

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
            var span = document.createElement("span");
            span.setAttribute("class", "arrow");
            link.appendChild(span);
            span.addEventListener("click", clickMenuItemHasChildren);
        }
    }

    function clickMenuItemHasChildren(e) {
        e.preventDefault();
        e.stopPropagation();
        var link = this.parentNode;
        subMenu = link.parentNode.getElementsByTagName("ul")[0];

        if ("undefined" !== typeof subMenu) {
            console.log("click");
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