function hocwpIsExternalLink(url) {
    if (url.indexOf("mailto") !== -1 || url.indexOf("javascript") !== -1) {
        return false;
    }

    var tempLink = document.createElement("a");
    tempLink.href = url;

    if (!tempLink.hostname) {
        return false;
    }

    return tempLink.hostname !== window.location.hostname;
}

(function () {
    var container, links, i, len;

    container = document.getElementsByTagName("body")[0];
    links = container.getElementsByTagName("a");

    for (i = 0, len = links.length; i < len; i++) {
        var link = links[i];

        if (hocwpIsExternalLink(link.href)) {
            var newTab = parseInt(link.getAttribute("data-new-tab")),
                rel = link.getAttribute("rel");

            if ((!isNaN(newTab) && 1 === newTab) || "dofollow" === rel) {
                continue;
            }

            link.setAttribute("rel", "nofollow");
            link.setAttribute("target", "_self");
            link.href = hocwpTheme.homeUrl + '?goto=' + links[i].href;
        }
    }
})();