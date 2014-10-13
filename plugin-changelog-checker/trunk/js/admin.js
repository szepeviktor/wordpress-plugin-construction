jQuery(function ($) {

    $('#wpbody #the-list tr.active, #wpbody #the-list tr.inactive').each(function () {
        var action_link = $(this).find('.plugin-title span.watch a'),
            plugin_file = action_link.data('plugin-file');

        if (!plugin_file) console.error("No plugin data for" + String($(this).prop('id')));

        action_link.click(function (e) {
            var postdata,
                action_link = $(this);

            e.preventDefault();
            postdata = {
                action: 'o1_plugin_changelog_watch',
                _nonce: O1_PluginChangelog_nonce,
                plugin: plugin_file
            };
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: postdata,
                error: function (jqXHR, status, errormessage) {
                    console.error("AJAX error! " + status + "/" + errormessage);
                },
                success: function (response, status, jqXHR) {
                    action_link.html(response.data);
                }
            });
        });
    });

});
