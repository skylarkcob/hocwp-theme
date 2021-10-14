var html = document.getElementsByTagName("html")[0],
    screenWidth = parseFloat(html.getAttribute("data-screen-width")),
    windowWidth = window.innerWidth || window.screen.width;

if (isNaN(screenWidth) || windowWidth != screenWidth) {
    var xhr = new XMLHttpRequest(),
        ajaxUrl = hocwpTheme.ajaxUrl + "?action=hocwp_theme_detect_client_info";

    ajaxUrl += "&screen_width=" + windowWidth;

    xhr.open("GET", ajaxUrl, true);
    xhr.send();
}