jQuery(function ($) {
    var ser_opts = $('#all-options .form-table input[type="text"][value="SERIALIZED DATA"]');

    if (! OPTIONINS) {
        return;
    }

    ser_opts.each(function (){
        var header, dashicon,
            input = $(this),
            icon_html = '<span class="dashicons dashicons-editor-expand"></span>';

        dashicon = input.wrap('<a class="inspector"></div>').parent().append(icon_html);
        header = input.parent().parent().prev();
        $.merge(dashicon, header).click(function () {
            var name = input.attr('name');
            tb_show(name, 'admin-ajax.php?action=o1_inspect_option&option_name=' + name + '&width=1100&height=760&_nonce=' + OPTIONINS.nonce);
        });
    });
});
