window.wp = window.wp || {};
window.hocwpTheme = window.hocwpTheme || {};

function HOCWP_Theme() {
    this.log = function (string) {
        log(string);
    };

    this.remove_backward_slash = function (string) {
        return string.replace(/\\/g, "");
    };

    this.get_param_by_name = function (url, name) {
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");

        let regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
            results = regex.exec(url);

        return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
    };

    this.remove_param = function (key, sourceURL) {
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

        if ("/?" === lastChar) {
            rtn = rtn.substr(0, rtn.length - 1);
        }

        return rtn;
    };

    this.remove_params = function (url) {
        return url.split(/[?#]/)[0];
    };

    this.add_param = function (key, value, url) {
        var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        var separator = url.indexOf("?") !== -1 ? "&" : "?";

        if (url.match(re)) {
            return url.replace(re, "$1" + key + "=" + value + "$2");
        } else {
            return url + separator + key + "=" + value;
        }
    };

    this.filter_list = function (input) {
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
    };

    this.is_email = function (email) {
        return /^\w+([\\.-]?\w+)*@\w+([\\.-]?\w+)*(\.\w{2,3})+$/.test(email);
    };

    this.is_google_pagespeed = function () {
        return (navigator.userAgent.indexOf("Speed Insights") !== -1 || navigator.userAgent.indexOf("Chrome-Lighthouse") !== -1 || navigator.userAgent.indexOf("Page Speed") !== -1 || navigator.userAgent.indexOf("Pagespeed") !== -1);
    };

    this.popup = function (popup) {
        if (popup) {
            var close = document.getElementById("sc-gdpr-close"),
                accept = document.getElementById("sc-gdpr-accept");

            if (localStorage.getItem("popState") !== "shown") {
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
    };

    this.ajax = function ($, element, data, callback, params) {
        data = data || {};
        params = params || {};

        data = $.extend({}, {
            action: hocwpTheme.ajaxAction,
            callback: hocwpTheme.customAjaxCallback,
            nonce: hocwpTheme.nonce
        }, data);

        params = $.extend({}, {
            type: "POST",
            dataType: "JSON",
            url: hocwpTheme.ajaxUrl,
            cache: true,
            data: data,
            success: function (response) {
                if ("function" === typeof callback) {
                    callback(response);
                }
            },
            complete: function (response) {
                element.closest("body").trigger("hocwpTheme:ajaxComplete", [element, response]);
            }
        }, params);

        $.ajax(params);
    }

    this.screenWidth = function () {
        return window.innerWidth || window.screen.width;
    };

    this.screenHeight = function () {
        return window.innerHeight || window.screen.height;
    };

    this.downloadTextarea = function (id, file_name, file_type) {
        let data = document.querySelector(id).value;
        let file = "data-" + Date.now() + ".txt";

        if (file_name) {
            file = file_name;
        }

        let link = document.createElement("a");
        link.download = file;

        file_type = file_type || "text/plain";

        let blob = new Blob([data], {
            type: file_type
        });

        link.href = URL.createObjectURL(blob);
        link.click();
        URL.revokeObjectURL(link.href);
    };

    this.sticky = function ($, element, top, htmlClass, bottom) {
        if ("number" === typeof top) {
            var scroll = $(window).scrollTop();

            bottom = bottom || 0;

            if ("object" !== typeof element) {
                element = $(element);
            }

            if (!element || !element.length) {
                return false;
            }

            htmlClass = htmlClass || "is-sticky fixed";

            if (scroll >= top) {
                var scrollHeight = $(document).height(),
                    scrollPosition = $(window).height() + scroll,
                    percent = (scrollHeight - scrollPosition) / scrollHeight;

                if (0.04 >= percent) {
                    element.addClass("reached-bottom");
                } else {
                    element.removeClass("reached-bottom");
                }

                if (bottom > top && bottom <= scroll) {
                    element.removeClass(htmlClass);
                    element.addClass("reached-bottom");
                } else if (!element.hasClass(htmlClass)) {
                    element.addClass(htmlClass);

                    if (0.04 < percent) {
                        element.removeClass("reached-bottom");
                    }
                }
            } else {
                element.removeClass(htmlClass);
                element.removeClass("reached-bottom");
            }
        }
    };

    this.showDevLog = function () {
        setTimeout(function () {
            if (hocwpTheme && hocwpTheme.l10n && hocwpTheme.l10n.themeCreatedBy) {
                log("%c" + hocwpTheme.l10n.themeCreatedBy, "font-size:16px;color:red;font-family:tahoma;padding:10px 0");
            }
        }, 5000);
    };

    this.updateBodyAttributes = function () {
        document.getElementsByTagName("body")[0].setAttribute("data-screen-width", this.screenWidth());
    };

    this.init = function () {
        this.showDevLog();
        this.updateBodyAttributes();
        this.popup(document.getElementById("sc-gdpr-box"));

        setTimeout(function () {
            if ("function" === typeof lozad) {
                var observer = lozad();
                observer.observe();
            }
        }, 500);
    }
}

hocwpTheme.object = new HOCWP_Theme();
hocwpTheme.object.init();

var log = console.log.bind(document);

hocwpTheme.getParamByName = function (url, name) {
    return hocwpTheme.object.get_param_by_name(url, name);
};

hocwpTheme.removeParam = function (key, sourceURL) {
    return hocwpTheme.object.remove_param(key, sourceURL);
};

hocwpTheme.removeParams = function (url) {
    return hocwpTheme.object.remove_params(url);
};

hocwpTheme.addParam = function (key, value, url) {
    return hocwpTheme.object.add_param(key, value, url);
};

function hocwpThemeFilterList(input) {
    hocwpTheme.object.filter_list(input);
}

hocwpTheme.filterList = function (input) {
    hocwpThemeFilterList(input);
};

hocwpTheme.isEmail = function (email) {
    return hocwpTheme.object.is_email(email);
};

hocwpTheme.isGooglePagespeed = function () {
    return hocwpTheme.object.is_google_pagespeed();
};

hocwpTheme.screenWidth = function () {
    return hocwpTheme.object.screenWidth();
};

hocwpTheme.ajax = function ($, element, data, callback, params) {
    hocwpTheme.object.ajax($, element, data, callback, params);
};