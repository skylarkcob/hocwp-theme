var html = document.getElementsByTagName("html")[0],
    screenWidth = parseFloat(html.getAttribute("data-screen-width"));

if (isNaN(screenWidth) || window.screen.width != screenWidth) {
    var xhr = new XMLHttpRequest(),
        ajaxUrl = hocwpTheme.ajaxUrl + "?action=hocwp_theme_detect_client_info";

    ajaxUrl += "&screen_width=" + window.screen.width;

    xhr.open("GET", ajaxUrl, true);
    xhr.send();
}
