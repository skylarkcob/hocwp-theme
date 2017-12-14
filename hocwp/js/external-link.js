function hocwpIsExternalLink(url) {
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
            links[i].href = hocwpTheme.homeUrl + '?goto=' + links[i].href;
        }
    }
})();