var widgets = document.getElementsByClassName("hocwp-widget-tabber");

if (widgets.length) {
    var widgetCount = widgets.length;

    for (var i = 0; i < widgetCount; i++) {
        var widget = widgets[i],
            navTabs = widget.getElementsByClassName("nav-tabs")[0],
            tabTitles = widget.getElementsByClassName("tab-title");

        if (tabTitles.length) {
            for (var j = 0; j < tabTitles.length; j++) {
                var titleLink = tabTitles[j],
                    currentWidget = titleLink.closest(".widget-in-tab"),
                    li = document.createElement("li");

                titleLink.addEventListener("click", function (e) {
                    e.preventDefault();

                    var link = this,
                        item = link.parentNode,
                        tabs = item.parentNode,
                        items = tabs.children,
                        tabPane = document.getElementById(link.getAttribute("href").substr(1)),
                        tabContent = tabPane.parentNode,
                        tabPanes = tabContent.children;

                    for (var k = 0; k < items.length; k++) {
                        items[k].removeAttribute("class");
                    }

                    for (var l = 0; l < tabPanes.length; l++) {
                        tabPanes[l].className = tabPanes[l].className.replace(" active", "")
                    }

                    item.setAttribute("class", "active");
                    tabPane.className += " active";
                });

                titleLink.setAttribute("href", "#" + currentWidget.getAttribute("id"));

                li.appendChild(titleLink);

                if (0 == j) {
                    li.setAttribute("class", "active");
                    currentWidget.className += " active";
                }

                navTabs.appendChild(li);
            }
        } else {
            var tabPanes = widget.getElementsByClassName("tab-pane");

            if (tabPanes.length) {
                for (var l = 0; l < tabPanes.length; l++) {
                    tabPanes[l].className += " active";
                }
            }
        }
    }
}