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


    // Handle delete button click
    $('#simple-table-rows').on('click', '.simple-table-delete-row', function() {
        console.log("Delete button clicked.");  // debug log
        if (confirm('Are you sure you want to delete this row?')) {
            var row = $(this).closest('tr');
            var rowIndex = row.data('row');

            console.log("Row index: " + rowIndex);  // debug log

            $.post(ajaxurl, {
                action: 'simple_table_plugin_delete_row',
                rowIndex: rowIndex,
                security: simpleTablePlugin.deleteNonce  // use the delete nonce
            }, function(response) {
                if (response.success) {
                    console.log("Row deletion successful.");  // debug log
                    row.remove();
                } else {
                    console.log("Failed to delete row: " + response);  // debug log
                    alert('Failed to delete the row.');
                }
            });
        }
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