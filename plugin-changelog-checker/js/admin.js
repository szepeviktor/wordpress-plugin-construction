jQuery(function ($) {

    $('#wpbody #the-list tr').each(function () {
        var action_link = $(this).find('.plugin-title span.watch a'),
            plugin_file = action_link.data('plugin-file');

        if (!plugin_file) console.error('No plugin data for' + String($(this).prop('id')));

        action_link.click(function (e) {
            var postdata,
                action_link = $(this);

            e.preventDefault();
            postdata = {
                action: "o1_plugin_changelog_watch",
                _nonce: O1_PluginChangelog_nonce,
                plugin: plugin_file
            };
            $.post(
                ajaxurl,
                postdata,
                function (result) {
                    action_link.html($.parseJSON(result));
                }
            );
        });
    });

});
