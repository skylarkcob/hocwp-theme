jQuery(document).ready(function ($) {
    const body = $("body"),
        introPlay = $("#introPlayer"),
        bgPlay = $("#bgPlayer"),
        playerInterval = 5000;

    var soundEnabled = false,
        HTEVR = null;

    let HTE_VR = function () {
        this.hideLoading = function (element) {
            if (HTEVR.validSelector(element)) {
                if (!element.hasClass("loading-cover")) {
                    element = element.closest(".loading-cover");
                }

                element.fadeOut().addClass("hidden");
                return true;
            }

            let timeout = Math.floor(Math.random() * 5000) + 1000;

            setTimeout(function () {
                HTEVR.hideLoading(body.find(".loading-cover:not(.manual)"));
            }, timeout);
        };

        this.validSelector = function (element) {
            return (element && element.length);
        };

        this.playerControl = function (id, control) {
            if (HTEVR.validSelector(id)) {
                id = id.attr("id");
            }

            let player = document.getElementById(id);

            if ("object" === typeof player) {
                control = control || "play";

                if ("play" === control) {
                    player.play();
                } else if ("pause" === control) {
                    player.pause();
                } else if ("stop" === control) {
                    player.pause();
                    player.currentTime = 0;
                }
            }
        };

        this.soundEnabled = function () {
            let loop = parseInt(body.attr("data-sound-enabled"));
            return (soundEnabled || 1 === loop);
        };

        this.play = function (player) {
            if (HTEVR.validSelector(player)) {
                let players = document.getElementsByClassName("player");

                // Stop on playing players
                for (let i = 0; i < players.length; i++) {
                    HTEVR.playerControl($(players.item(i)), "stop");
                }

                // Play current player
                HTEVR.playerControl(player);

                player.on("ended", function () {
                    if (player.hasClass("loop") && HTEVR.soundEnabled()) {
                        let time = parseInt(player.attr("data-interval"));

                        if (!time) {
                            time = playerInterval;
                        }

                        // Play current player again
                        setTimeout(function () {
                            HTEVR.playerControl(player);
                        }, time);
                    }
                });
            }
        };

        this.onOffSound = function () {
            body.on("click", ".sound-confirm-cover .confirm-box button", function (e) {
                e.preventDefault();

                let that = this,
                    element = $(that),
                    yes = element.hasClass("yes");

                HTEVR.hideLoading(element);

                if (yes) {
                    soundEnabled = true;
                    body.attr("data-sound-enabled", 1);

                    let player = null;

                    if (HTEVR.validSelector(introPlay)) {
                        player = introPlay;
                    } else if (HTEVR.validSelector(bgPlay)) {
                        player = bgPlay;
                    }

                    HTEVR.play(player);
                }
            });
        };

        this.intro = function () {
            body.on("click", ".loading-cover.intro-cover .close-box", function (e) {
                e.preventDefault();

                HTEVR.playerControl(introPlay, "stop");
                HTEVR.hideLoading($(this));

                if (HTEVR.soundEnabled()) {
                    let player = null;

                    if (HTEVR.validSelector(bgPlay)) {
                        player = bgPlay;
                    }

                    HTEVR.play(player);
                }
            });
        };

        this.hideMirror = function () {
            setTimeout(function () {
                HTEVR.hideLoading(body.find(".loading-mirror"));
            }, 3000);
        };

        this.fancybox = function () {
            if ($.fn.fancybox) {
                body.on("click", ".primary-menus a.fancybox, .primary-menus li.fancybox > a, .primary-menus a.popup, .primary-menus li.popup > a", function (e) {
                    e.preventDefault();

                    var that = this,
                        element = $(that),
                        id = element.attr("href"),
                        target = $(id);

                    if (HTEVR.validSelector(target)) {
                        target.trigger("click");
                        // Hide mobile menu
                        $("#toggle-main-menu[aria-expanded='true']").trigger("click")
                    }
                });
            }
        };

        this.lozad = function () {
            setTimeout(function () {
                if ("function" === typeof lozad) {
                    let observer = lozad();
                    observer.observe();
                }
            }, 500);
        };

        this.isFullScreen = function () {
            return !(!document.fullscreenElement && !document.mozFullScreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement);
        };

        this.toggleFullScreen = function () {
            if (!HTEVR.isFullScreen()) {
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen();
                } else if (document.documentElement.msRequestFullscreen) {
                    document.documentElement.msRequestFullscreen();
                } else if (document.documentElement.mozRequestFullScreen) {
                    document.documentElement.mozRequestFullScreen();
                } else if (document.documentElement.webkitRequestFullscreen) {
                    document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
            }
        };

        this.controlMenuTools = function () {
            let enterFs = body.find("img[data-action='full_screen']"),
                exitFs = body.find("img[data-action='exit_full_screen']");

            body.on("click", ".menu-toggle img", function (e) {
                e.preventDefault();

                let that = this,
                    element = $(that),
                    action = element.attr("data-action"),
                    container = element.closest(".menu-toggle"),
                    other = element.parent().children("*:not(.active)");

                element.removeClass("active");
                other.addClass("active");

                if ("open_menu" === action) {
                    container.children("*:not(:first-child)").show();
                } else if ("close_menu" === action) {
                    container.children("*:not(:first-child)").hide();
                } else if ("full_screen" === action || "exit_full_screen" === action) {
                    HTEVR.toggleFullScreen();
                } else if ("sound_on" === action) {
                    HTEVR.playerControl(bgPlay, "pause");
                } else if ("sound_off" === action) {
                    HTEVR.playerControl(bgPlay, "play");
                }
            });
        };

        this.init = function () {
            this.controlMenuTools();
            this.hideLoading();
            this.onOffSound();
            this.intro();
            this.hideMirror();
            this.fancybox();
            this.lozad();
        };
    };

    HTEVR = new HTE_VR();
    HTEVR.init();
});