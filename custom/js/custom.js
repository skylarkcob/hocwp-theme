jQuery(document).ready(function ($) {
    (function () {
        var mainSlider = $(".main-slider");

        if (mainSlider.length) {
            mainSlider.slick({
                autoplay: true,
                autoplaySpeed: 20000
            });
        }
    })();
});