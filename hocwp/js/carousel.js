jQuery(document).ready(function ($) {
    $.fn.hocwpCarousel = function (options, type) {
        var settings = {},
            sliderType = "";

        options = options || {};

        switch (type) {
            case "slick":
                if ("function" === typeof $.fn.slick) {
                    sliderType = "slick";
                }

                break;
            case "flickity":
                if ("flickity" === sliderType) {
                    sliderType = "flickity"
                }

                break;
        }

        if (!$.trim(sliderType)) {
            if ("function" === typeof Flickity) {
                sliderType = "flickity";
            } else if ("function" === typeof $.fn.slick) {
                sliderType = "slick";
            }
        }

        return this.each(function () {
            var that = this,
                element = $(that),
                customSettings = JSON.parse(element.attr("data-settings")),
                initialized = false;

            if ("flickity" === sliderType) {
                if (customSettings) {
                    settings.prevNextButtons = (1 === customSettings.arrows);
                    settings.autoPlay = (1 === customSettings.autoplay);
                    settings.wrapAround = (1 === customSettings.infinity);
                    settings.pageDots = ("dots" === customSettings.navigation);
                    settings.adaptiveHeight = (1 === customSettings.adaptive_height);
                }

                options = $.extend({}, settings, options);

                element.flickity(options);
                initialized = true;
            } else if ("slick" === sliderType) {
                if (customSettings) {
                    settings.arrows = (1 === customSettings.arrows);
                    settings.autoplay = (1 === customSettings.autoplay);
                    settings.infinite = (1 === customSettings.infinity);
                    settings.dots = ("dots" === customSettings.navigation);
                    settings.adaptiveHeight = (1 === customSettings.adaptive_height);
                }

                options = $.extend({}, settings, options);

                element.slick(options);
                initialized = true;
            }

            if (initialized) {
                element.addClass("hocwp-carousel-enabled").attr("data-carousel-type", sliderType);
            }
        });
    };
});