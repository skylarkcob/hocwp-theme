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
        let rtn = sourceURL.split("?")[0],
            param,
            params_arr = [],
            queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";

        if (queryString !== "") {
            params_arr = queryString.split("&");

            for (let i = params_arr.length - 1; i >= 0; i -= 1) {
                param = params_arr[i].split("=")[0];

                if (param === key) {
                    params_arr.splice(i, 1);
                }
            }

            rtn = rtn + "?" + params_arr.join("&");
        }

        let lastChar = rtn.substr(rtn.length - 2);

        if ("/?" === lastChar) {
            rtn = rtn.substr(0, rtn.length - 1);
        }

        return rtn;
    };

    this.remove_params = function (url) {
        return url.split(/[?#]/)[0];
    };

    this.add_param = function (key, value, url) {
        let re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        let separator = url.indexOf("?") !== -1 ? "&" : "?";

        if (url.match(re)) {
            return url.replace(re, "$1" + key + "=" + value + "$2");
        } else {
            return url + separator + key + "=" + value;
        }
    };

    this.filter_list = function (input) {
        let filter, ul, li, a, i, txtValue;

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
            let close = document.getElementById("sc-gdpr-close"),
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
    };

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
            let scroll = $(window).scrollTop();

            bottom = bottom || 0;

            if ("object" !== typeof element) {
                element = $(element);
            }

            if (!element || !element.length) {
                return false;
            }

            htmlClass = htmlClass || "is-sticky fixed";

            if (scroll >= top) {
                let scrollHeight = $(document).height(),
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

    this.dateToCountdown = function (dateString) {
        let countDownDate = new Date(dateString).getTime(),
            date = new Date(),
            now = date.getTime(),
            distance = countDownDate - now,
            days = Math.floor(distance / (1000 * 60 * 60 * 24)),
            hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
            minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)),
            seconds = Math.floor((distance % (1000 * 60)) / 1000);

        return {
            "diff": distance,
            "d": days,
            "h": hours,
            "m": minutes,
            "s": seconds
        };
    };

    this.loader = function (fade = false) {
        function fadeOut(element) {
            if (!element) {
                return false;
            }

            let opacity = 1,
                timer = setInterval(function () {
                    if (opacity <= 0.2) {
                        clearInterval(timer);
                        element.style.display = "none";
                        let body = document.getElementsByTagName("body")[0];
                        body.classList.remove("loading");
                    }

                    element.style.opacity = opacity;
                    element.style.filter = "alpha(opacity=" + opacity * 100 + ")";
                    opacity -= opacity * 0.1;
                }, 100);
        }

        if (fade) {
            fadeOut(document.getElementById("loaderIcon"));
        }

        window.onload = function () {
            fadeOut(document.getElementById("loaderIcon"));
        };
    };

    this.init = function () {
        this.loader();
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

let log = console.log.bind(document);

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

jQuery(document).ready(function ($) {
    const BODY = $("body");

    hocwpTheme.GLOBAL = {
        init: function () {
            this.delayLoad();
            this.carousel();
        },
        delayLoad: function () {
            $(".delay-load").each(function () {
                let that = this,
                    element = $(that),
                    module = element.attr("data-module"),
                    delay = element.attr("data-delay"),
                    url = window.location.href.split("#")[0];

                url = hocwpTheme.GLOBAL.addParamToURL("do_action", "delay_load", url);
                url = hocwpTheme.GLOBAL.addParamToURL("module", module, url);

                if ("number" === typeof delay) {
                    if (1 > delay) {
                        delay = 100;
                    }

                    setTimeout(function () {
                        $.get(url, function (response) {
                            element.html(response);
                            BODY.trigger("hocwpTheme:delayLoaded", [element, response]);
                        });
                    }, delay);
                } else {
                    $.get(url, function (response) {
                        element.html(response);
                        element.addClass("data-loaded");

                        if (element.children().length) {
                            element.addClass("has-data");
                        }

                        BODY.trigger("hocwpTheme:delayLoaded", [element, response]);
                    });
                }
            });

            BODY.on("hocwpTheme:delayLoaded", function () {
                if (document.readyState === "complete") {
                    hocwpTheme.object.loader(true);
                }
            });
        },
        addParamToURL: function (key, value, url) {
            return hocwpTheme.object.add_param(key, value, url);
        },
        carousel: function () {
            if ("function" === typeof Swiper) {
                $(".hocwp-slider").each(function () {
                    let element = $(this),
                        params = {},
                        slidesPerView = parseInt(element.attr("data-slides-per-view")),
                        settings = element.attr("data-settings"),
                        advancedSettings = element.attr("data-advanced-settings");

                    if ("number" !== typeof slidesPerView || isNaN(slidesPerView)) {
                        slidesPerView = 1;
                    }

                    params.slidesPerView = slidesPerView;

                    if ($.trim(settings)) {
                        settings = JSON.parse(settings);

                        if (settings.arrows) {
                            params.navigation = {
                                nextEl: ".swiper-button-next",
                                prevEl: ".swiper-button-prev",
                            }
                        }

                        if (settings.navigation && "dots" === settings.navigation) {
                            params.pagination = {
                                el: ".swiper-pagination",
                                type: "bullets",
                                clickable: true
                            };
                        }

                        if (settings.autoplay) {
                            let speed = parseInt(settings.autoplay_speed);

                            if ("number" === typeof speed && !isNaN(speed)) {
                                params.autoplay = {
                                    delay: speed
                                };
                            } else {
                                params.autoplay = settings.autoplay;
                            }
                        }

                        params.loop = settings.infinity;
                        params.autoHeight = settings.adaptive_height;
                    }

                    if ($.trim(advancedSettings)) {
                        advancedSettings = JSON.parse(advancedSettings);

                        if ("object" === typeof advancedSettings) {
                            params = $.extend(params, advancedSettings);
                        }
                    }

                    new Swiper("#" + element.attr("id"), params);
                });
            }
        }
    };

    hocwpTheme.GLOBAL.init();
});