window.wp = window.wp || {};
window.hocwpTheme = window.hocwpTheme || {};

var log = console.log.bind(document);

hocwpTheme.getParamByName = function (url, name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");

    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(url);

    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
};

hocwpTheme.removeParam = function (key, sourceURL) {
    var rtn = sourceURL.split("?")[0],
        param,
        params_arr = [],
        queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";

    if (queryString !== "") {
        params_arr = queryString.split("&");

        for (var i = params_arr.length - 1; i >= 0; i -= 1) {
            param = params_arr[i].split("=")[0];

            if (param === key) {
                params_arr.splice(i, 1);
            }
        }

        rtn = rtn + "?" + params_arr.join("&");
    }

    var lastChar = rtn.substr(rtn.length - 2);

    if ("/?" == lastChar) {
        rtn = rtn.substr(0, rtn.length - 1);
    }

    return rtn;
};

hocwpTheme.removeParams = function (url) {
    return url.split(/[?#]/)[0];
};

hocwpTheme.addParam = function (key, value, url) {
    var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    var separator = url.indexOf("?") !== -1 ? "&" : "?";

    if (url.match(re)) {
        return url.replace(re, "$1" + key + "=" + value + "$2");
    } else {
        return url + separator + key + "=" + value;
    }
};

setTimeout(function () {
    if (hocwpTheme && hocwpTheme.l10n && hocwpTheme.l10n.themeCreatedBy) {
        log("%c" + hocwpTheme.l10n.themeCreatedBy, "font-size:16px;color:red;font-family:tahoma;padding:10px 0");
    }
}, 5000);

function hocwpThemeFilterList(input) {
    var filter, ul, li, a, i, txtValue;

    if ("object" != typeof input) {
        input = document.getElementById(input);
    }

    filter = input.value.toUpperCase();

    ul = input.closest(".filter-box").getElementsByTagName("ul")[0];

    li = ul.getElementsByTagName("li");

    for (i = 0; i < li.length; i++) {
        a = li[i].getElementsByTagName("a")[0];

        txtValue = a.getAttribute("title");

        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            li[i].style.display = "";
        } else {
            li[i].style.display = "none";
        }
    }
}

hocwpTheme.filterList = function (input) {
    hocwpThemeFilterList(input);
};

hocwpTheme.isEmail = function (email) {
    if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) {
        return (true);
    }

    return (false);
};

hocwpTheme.isGooglePagespeed = function () {
    return (navigator.userAgent.indexOf("Speed Insights") !== -1 || navigator.userAgent.indexOf("Chrome-Lighthouse") !== -1 || navigator.userAgent.indexOf("Page Speed") !== -1 || navigator.userAgent.indexOf("Pagespeed") !== -1);
};

(function () {
    var popup = document.getElementById("sc-gdpr-box");

    if (popup) {
        var close = document.getElementById("sc-gdpr-close"),
            accept = document.getElementById("sc-gdpr-accept");

        if (localStorage.getItem("popState") != "shown") {
            popup.style.display = "block";
        }

        close.addEventListener("click", function () {
            popup.style.display = "none";
        });

        accept.addEventListener("click", function () {
            popup.style.display = "none";
            localStorage.setItem("popState", "shown");
        });
    }
})();