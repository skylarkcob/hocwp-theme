/* global hocwpThemeCustomizer, hocwpThemeColor, jQuery, wp, _ */
/**
 * Customizer enhancements for a better user experience.
 *
 * Contains extra logic for our Customizer controls & settings.
 */

window.hocwpThemeCustomizer = window.hocwpThemeCustomizer || {};

( function () {
    // Wait until the customizer has finished loading.
    wp.customize.bind("ready", function () {
        // Add a listener for accent-color changes.
        wp.customize("accent_hue", function (value) {
            value.bind(function (to) {
                // Update the value for our accessible colors for all areas.
                Object.keys(hocwpThemeCustomizer.colors).forEach(function (context) {
                    var backgroundColorValue;

                    if (hocwpThemeCustomizer.colors[context].color) {
                        backgroundColorValue = hocwpThemeCustomizer.colors[context].color;
                    } else {
                        var setting = wp.customize(hocwpThemeCustomizer.colors[context].setting);

                        if (setting) {
                            backgroundColorValue = setting.get();
                        }
                    }

                    if (backgroundColorValue) {
                        hocwpThemeSetAccessibleColorsValue(context, backgroundColorValue, to);
                    }
                });
            });
        });

        // Add a listener for background-color changes.
        var colors;

        Object.keys(hocwpThemeCustomizer.colors).forEach(function (context) {
            if ("custom-color" != context) {
                var setting = hocwpThemeCustomizer.colors[context].setting;

                if (setting) {
                    wp.customize(setting, function (value) {
                        value.bind(function (to) {
                            // Update the value for our accessible colors for this area.
                            hocwpThemeSetAccessibleColorsValue(context, to, wp.customize("accent_hue").get());
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
                                hocwpThemeSetCustomAccessibleColorsValue(context, to);
                            });
                        });
                    }
                });
            }
        });
    });

    /**
     * Updates the value of the "accent_accessible_colors" setting.
     *
     * @param {string} context The area for which we want to get colors. Can be for example "content", "header" etc.
     * @param {string} backgroundColor The background color (HEX value).
     * @param {number} accentHue Numeric representation of the selected hue (0 - 359).
     *
     * @return {void}
     */
    function hocwpThemeSetAccessibleColorsValue(context, backgroundColor, accentHue) {
        var value, colors;

        // Get the current value for our accessible colors, and make sure it's an object.
        value = wp.customize("accent_accessible_colors").get();

        value = ( _.isObject(value) && !_.isArray(value) ) ? value : {};

        // Get accessible colors for the defined background-color and hue.
        colors = hocwpThemeColor(backgroundColor, accentHue);

        // Sanity check.
        if (colors.getAccentColor() && "function" === typeof colors.getAccentColor().toCSS) {
            // Update the value for this context.
            value[context] = {
                text: colors.getTextColor(),
                accent: colors.getAccentColor().toCSS(),
                background: backgroundColor
            };

            // Get borders color.
            value[context].borders = colors.bgColorObj
                .clone()
                .getReadableContrastingColor(colors.bgColorObj, 1.36)
                .toCSS();

            // Get secondary color.
            value[context].secondary = colors.bgColorObj
                .clone()
                .getReadableContrastingColor(colors.bgColorObj)
                .s(colors.bgColorObj.s() / 2)
                .toCSS();
        }

        // Change the value.
        wp.customize("accent_accessible_colors").set(value);

        // Small hack to save the option.
        wp.customize("accent_accessible_colors")._dirty = true;
    }

    /**
     * Updates the value of the "custom_accessible_colors" setting.
     *
     * @param {string} context The type for which we want to get colors. Can be for example "primary", "secondary" etc.
     * @param {string} color The custom color (HEX value).
     *
     * @return {void}
     */
    function hocwpThemeSetCustomAccessibleColorsValue(context, color) {
        var value;

        // Get the current value for our custom accessible colors, and make sure it's an object.
        value = wp.customize("custom_accessible_colors").get();

        value = ( _.isObject(value) && !_.isArray(value) ) ? value : {};

        if (!value["custom-color"]) {
            value["custom-color"] = {};
        }

        value["custom-color"][context] = color;

        // Change the value.
        wp.customize("custom_accessible_colors").set(value);

        // Small hack to save the option.
        wp.customize("custom_accessible_colors")._dirty = true;
    }
}(jQuery) );