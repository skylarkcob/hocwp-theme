(function () {
    var page = document.getElementById("page"),
        widgetInners = page.getElementsByClassName("widget-inner");
    if (widgetInners) {
        for (var i = 0; i < widgetInners.length; i++) {
            var inner = widgetInners[i],
                html = inner.innerHTML;
            if (!html) {
                inner.parentNode.removeChild(inner);
            }
        }
    }
})();