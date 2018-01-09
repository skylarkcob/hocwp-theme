function hocwpIsExternalLink(url) {
    if (url.indexOf("mailto") !== -1) {
        return false;
    }
    var tempLink = document.createElement("a");
    tempLink.href = url;
    return tempLink.hostname !== window.location.hostname;
}

(function () {
    var container, links, i, len;

    container = document.getElementsByTagName("body")[0];
    links = container.getElementsByTagName("a");

    for (i = 0, len = links.length; i < len; i++) {
        if (hocwpIsExternalLink(links[i].href)) {
            links[i].setAttribute("rel", "nofollow");
            links[i].setAttribute("target", "_self");
            links[i].href = hocwpTheme.homeUrl + '?goto=' + links[i].href;
        }
    }
})();