(function () {
    if (window.innerWidth < 1240) {
        return;
    }
    var sidebars = document.getElementsByClassName("sidebar");

    function hocwpLoopSidebarWidget(sidebars, scrollPos, top, footer, scroll) {
        scroll = scroll || false;
        for (var i = 0; i < sidebars.length; i++) {
            var sidebar = sidebars[i],
                widgets = sidebar.getElementsByClassName("widget");
            if (widgets) {
                var widget = widgets[widgets.length - 1],
                    offsetTop = widget.offsetTop;
                widget.style.width = widget.offsetWidth + "px";
                if (!scroll) {
                    widget.setAttribute("data-offset-top", offsetTop);
                } else {
                    if (widget.getAttribute("data-offset-top")) {
                        offsetTop = parseInt(widget.getAttribute("data-offset-top"));
                    }
                }
                if (scrollPos > 0 && scrollPos > offsetTop) {
                    if (-1 === widget.className.indexOf("fixed")) {
                        widget.className += " fixed";
                    }

                    if (footer) {
                        var footerHeight = footer.offsetHeight,
                            totalHeight = scrollPos + widget.offsetHeight;
                        if (totalHeight > footer.offsetTop) {
                            widget.style.top = "auto";
                            widget.style.bottom = Math.abs(footerHeight) + "px";
                        } else {
                            if (widget.style.bottom) {
                                widget.style.bottom = "";
                            }
                            widget.style.top = top + "px";
                        }
                    } else {
                        widget.style.top = top + "px";
                    }
                } else {
                    widget.className = widget.className.replace(" fixed", "");
                    widget.style.top = "";
                    widget.style.bottom = "";
                }
            }
        }
    }

    if (sidebars) {
        var scrollPos = window.scrollY || window.scrollTop || document.getElementsByTagName("html")[0].scrollTop,
            wpadminbar = document.getElementById("wpadminbar"),
            top = 0,
            footer = document.getElementById("colophon");
        if (wpadminbar) {
            top = wpadminbar.offsetHeight;
        }

        hocwpLoopSidebarWidget(sidebars, scrollPos, top, footer);
        window.addEventListener("scroll", function () {
            if (window.innerWidth < 1240) {
                return;
            }
            wpadminbar = document.getElementById("wpadminbar")
            if (wpadminbar) {
                top = wpadminbar.offsetHeight;
            }
            scrollPos = window.scrollY || window.scrollTop || document.getElementsByTagName("html")[0].scrollTop;
            hocwpLoopSidebarWidget(sidebars, scrollPos, top, footer, true);
        });
    }
})();