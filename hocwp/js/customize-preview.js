window.hocwpThemeCustomizer = window.hocwpThemeCustomizer || {};

( function ($, api, _) {
    // Add listener for the accent color.
    api("accent_hue", function (value) {
        value.bind(function () {
            // Generate the styles.
            // Add a small delay to be sure the accessible colors were generated.
            setTimeout(function () {
                Object.keys(hocwpThemeCustomizer.colors).forEach(function (context) {
                    if ("custom-color" != context) {
                        hocwpThemeGenerateColorA11yPreviewStyles(context, null);
                    }
                });
            }, 50);
        });
    });

    // Add listeners for background-color settings.
    var colors;

    Object.keys(hocwpThemeCustomizer.colors).forEach(function (context) {
        if ("custom-color" != context) {
            var setting = hocwpThemeCustomizer.colors[context].setting;

            if (setting) {
                wp.customize(setting, function (value) {
                    value.bind(function () {
                        // Generate the styles.
                        // Add a small delay to be sure the accessible colors were generated.
                        setTimeout(function () {
                            hocwpThemeGenerateColorA11yPreviewStyles(context, null);
                        }, 50);
                    });
                });
            }
        } else {
            colors = hocwpThemeCustomizer.colors[context];

            Object.keys(colors).forEach(function (context) {
                var setting = colors[context].setting;

                if (setting) {
                    wp.customize(setting, function (value) {
                        value.bind(function (to) {
                            // Update the value for our custom accessible colors for this area.
                            hocwpThemeGenerateColorA11yPreviewStyles("custom-color", "custom_accessible_colors");
                        });
                    });
                }
            });
        }
    });

    /**
     * Add styles to elements in the preview pane.
     *
     * @param {string} context The area for which we want to generate styles. Can be for example "content", "header", "custom-color" etc.
     * @param {string} theme_mod The name of theme mod for get colors.
     *
     * @return {void}
     */
    function hocwpThemeGenerateColorA11yPreviewStyles(context, theme_mod) {
        theme_mod = theme_mod || "accent_accessible_colors";

        var customize = window.parent.wp.customize(theme_mod);

        if (customize) {
            // Get the accessible colors option.
            var a11yColors = customize.get(),
                stylesheedID = "hocwp-theme-custom-customizer-styles-" + context,
                selectorKey = "#" + stylesheedID,
                stylesheet = $(selectorKey),
                styles = "";

            // If the stylesheet doesn't exist, create it and append it to <head>.
            if (!stylesheet.length) {
                $("#hocwp-theme-custom-style-inline-css").after('<style id="' + stylesheedID + '"></style>');
                stylesheet = $(selectorKey);
            }

            if (!_.isUndefined(a11yColors[context])) {
                // Check if we have elements defined.
                if (hocwpThemeCustomizer.elements[context]) {
                    _.each(hocwpThemeCustomizer.elements[context], function (items, setting) {
                        _.each(items, function (elements, property) {
                            if (!_.isUndefined(a11yColors[context][setting])) {
                                styles += elements.join(",") + "{" + property + ":" + a11yColors[context][setting] + ";}";
                            }
                        });
                    });
                }
            }

            if (hocwpThemeCustomizer.inlineCSS) {
                styles += hocwpThemeCustomizer.inlineCSS;
            }

            // Add styles.
            stylesheet.html(styles);
        }
    }

    // Generate styles on load. Handles page-changes on the preview pane.
    $(document).ready(function () {
        var context = wp.customize("accent_hue_active").get();

        if ("custom" == context) {
            hocwpThemeGenerateColorA11yPreviewStyles("custom-color", "custom_accessible_colors");
        } else if ("auto_adjust" == context) {
            hocwpThemeGenerateColorA11yPreviewStyles("content", null);
            hocwpThemeGenerateColorA11yPreviewStyles("header-footer", null);
        }
    });
}(jQuery, wp.customize, _) );