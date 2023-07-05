jQuery(document).ready(function($) {

    // Make rows sortable
    $('#simple-table-rows').sortable({
        handle: '.drag-handle',
        update: function(event, ui) {
            var rows = $('#simple-table-rows').sortable('toArray', { attribute: 'data-row' });

            $.post(ajaxurl, {
                action: 'simple_table_plugin_reorder_rows',
                rows: rows,
                security: simpleTablePlugin.security
            }, function(response) {
                if (response.success) {
                    // nonce needs to be updated here
                    $.post(ajaxurl, {
                        action: 'simple_table_plugin_update_nonce',
                    }, function(response) {
                        if (response.success) {
                            simpleTablePlugin.security = response.data.security; // update the nonce
                        } else {
                            alert('Failed to reorder the rows. Please try again.');
                        }
                    });
                } else {
                    alert('Failed to reorder the rows. Please try again.');
                }
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

    // Export to CSV button
    $('#simple-table-export-csv').click(function() {
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'simple_table_plugin_export_csv',
                security: simpleTablePlugin.security
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function(blob) {
                // Use blob to create an object URL
                var url = window.URL || window.webkitURL;
                var downloadUrl = url.createObjectURL(blob);

                // Create a link and trigger a click to download the file
                var a = document.createElement('a');
                a.href = downloadUrl;
                a.download = 'simple-table.csv';
                document.body.appendChild(a);
                a.click();

                // Clean up
                setTimeout(function() {
                    url.revokeObjectURL(downloadUrl);
                    document.body.removeChild(a);
                }, 100);
            },
            error: function() {
                alert('Failed to export the table. Please try again.');
            }
        });
    });
});