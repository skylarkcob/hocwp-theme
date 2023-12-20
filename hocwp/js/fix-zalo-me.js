// Fix Zalo Me link
jQuery(document).ready(function ($) {
    window.fixZaloMe = window.fixZaloMe || {};

    let zaloAccounts = fixZaloMe.zaloAccounts;

    function isiOS() {
        return [
                "iPad Simulator",
                "iPhone Simulator",
                "iPod Simulator",
                "iPad",
                "iPhone",
                "iPod"
            ].includes(navigator.platform)
            // iPad on iOS 13 detection
            || (navigator.userAgent.includes("Mac") && "ontouchend" in document)
    }

    function checkZaloLink(link, successCallback, errorCallback) {
        let hiddenIframe = document.querySelector("#hiddenIframe");

        if (!hiddenIframe) {
            hiddenIframe = document.createElement("iframe");
            hiddenIframe.id = "hiddenIframe";
            hiddenIframe.style.display = "none";
            document.body.appendChild(hiddenIframe);
        }

        let timeout = setTimeout(function () {
            errorCallback(fixZaloMe.text.not_support);
            window.removeEventListener("blur", handleBlur);
        }, 2000);

        let result = {};

        function handleMouseMove(event) {
            if (!result.x) {
                result = {
                    x: event.clientX,
                    y: event.clientY,
                };
            }
        }

        function handleBlur() {
            clearTimeout(timeout);
            window.addEventListener("mousemove", handleMouseMove);
        }

        window.addEventListener("blur", handleBlur);

        window.addEventListener(
            "focus",
            function onFocus() {
                setTimeout(function () {
                    if (document.hasFocus()) {
                        successCallback(function (pos) {
                            if (!pos.x) {
                                return true;
                            }

                            let screenWidth = hocwpTheme.screenWidth(),
                                alertWidth = 300,
                                alertHeight = 100,
                                isXInRange = pos.x - 100 < 0.5 * (screenWidth + alertWidth) && pos.x + 100 > 0.5 * (screenWidth + alertWidth),
                                isYInRange = pos.y - 40 < alertHeight && pos.y + 40 > alertHeight;

                            return isXInRange && isYInRange ? fixZaloMe.text.can_open : fixZaloMe.text.not_support;
                        }(result));
                    } else {
                        successCallback(fixZaloMe.text.can_open);
                    }

                    window.removeEventListener("focus", onFocus);
                    window.removeEventListener("blur", handleBlur);
                    window.removeEventListener("mousemove", handleMouseMove);
                }, 500);
            },
            {once: true}
        );

        hiddenIframe.contentWindow.location.href = link;
    }

    $.each(zaloAccounts, function (index, value) {
        $("body").on("click", "a[href*='zalo.me/" + index + "']", function (e) {
            e.preventDefault();

            let that = this,
                element = $(that),
                userAgent = navigator.userAgent.toLowerCase(),
                isAndroid = /android/.test(userAgent),
                redirectURL = null;

            if (isiOS()) {
                redirectURL = "zalo://qr/p/" + value;
                window.open(redirectURL, "_blank");
            } else if (isAndroid) {
                redirectURL = "zalo://zaloapp.com/qr/p/" + value;
                window.open(redirectURL, "_blank");
            } else {
                redirectURL = "zalo://conversation?phone=" + index;
                element.addClass("zalo_loading");

                checkZaloLink(
                    redirectURL,
                    function (result) {
                        element.removeClass("zalo_loading");
                    },
                    function (error) {
                        element.removeClass("zalo_loading");
                        redirectURL = "https://chat.zalo.me/?phone=" + index;
                        window.open(redirectURL, "_blank");
                    }
                );
            }
        });
    });

    let styleElement = document.createElement("style");
    styleElement.innerHTML = ".zalo_loading { pointer-events: none; }";
    document.head.appendChild(styleElement);
});