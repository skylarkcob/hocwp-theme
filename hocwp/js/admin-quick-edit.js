jQuery(document).ready(function ($) {
    var inlineEditor = inlineEditPost.edit;

    inlineEditPost.edit = function (id) {
        inlineEditor.apply(this, arguments);

        var post_id = 0;

        if ("object" === typeof id) {
            post_id = parseInt(this.getId(id));
        }

        if (0 !== post_id) {
            // Code set meta field value here
        }
    }
});