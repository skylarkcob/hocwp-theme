window.hocwpTheme = window.hocwpTheme || {};

jQuery(document).ready(function ($) {
    (function () {
        var unSave = false,
            formOptions = $(".hocwp-theme #hocwpOptions");

        formOptions.find("input, textarea, select").on("input change paste keyup", function () {
            unSave = true;
        });

        formOptions.on("hocwpTheme:formDataChange", function () {
            unSave = true;
        });

        formOptions.on("submit", function() {
            unSave = false;
        });

        window.addEventListener("beforeunload", function (e) {
            if (!unSave) {
                return;
            }

            var confirmationMessage = hocwpTheme.l10n.beforeUnloadConfirmMessage;

            (e || window.event).returnValue = confirmationMessage;

            return confirmationMessage;
        });
    })();
});