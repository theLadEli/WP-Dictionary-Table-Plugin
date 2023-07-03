jQuery(document).ready(function($) {
    // Make rows sortable
    $('#simple-table-rows').sortable({
        update: function(event, ui) {
            var rows = $('#simple-table-rows').sortable('toArray', { attribute: 'data-row' });

            $.post(ajaxurl, {
                action: 'simple_table_plugin_reorder_rows',
                rows: rows,
                security: simpleTablePlugin.security
            });
        }
    });

    // Delete row button
    $('.simple-table-delete-row').click(function() {
        var row = $(this).data('row');

        $.post(ajaxurl, {
            action: 'simple_table_plugin_delete_row',
            row: row,
            security: simpleTablePlugin.security
        }, function() {
            location.reload();
        });

        return false;
    });

    // Copy shortcode button
    $('#simple-table-copy-shortcode').click(function() {
        var $temp = $('<input>');
        $('body').append($temp);
        $temp.val($('#simple-table-shortcode').text()).select();
        document.execCommand('copy');
        $temp.remove();
    });
});
