var originalTableHtml;
var timeoutId = null;  // Here's where we'll keep track of the timeout


jQuery(document).ready(function($) {

    originalTableHtml = $(".table-wrapper").html();  // Save the initial table state


    // console.log('Page loaded. jQuery ready.'); // debug log: jQuery loaded correctly

    // Handle Search Functionality
    $('#simple-table-search').on('keyup', function() {
        var searchTerm = jQuery(this).val();

        var nonce = simpleTablePlugin.nonce;

        
        // console.log('The Nonce value is:' + simpleTablePlugin.searchNonce);
        // console.log('Keyup event detected on search bar.'); // debug log: Keyup event detected    
        // console.log('Search term: ' + searchTerm);
        // console.log('Nonce: ' + nonce);

        if (timeoutId) {
            clearTimeout(timeoutId);  // If a timeout is already scheduled, cancel it
        }

        timeoutId = setTimeout(function() {
            if (searchTerm.length === 0) {
                $('.table-wrapper').html(originalTableHtml); 
            } 

            else {
                $.ajax({
                    url: simpleTablePlugin.ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'simple_table_plugin_search',
                        searchTerm: searchTerm,
                        security: simpleTablePlugin.searchNonce
                    },
                    success: function(response) {
                        // console.log('AJAX request successful.'); // debug log: AJAX request was successful
                        $('.table-wrapper').html(response);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('AJAX request failed.'); // debug log: AJAX request failed
                        console.error('Status: ' + textStatus);
                        console.error('Error: ' + errorThrown);
                    }
                });
            }
        }, 500);  // Schedule a new timeout, to execute in 500 ms
    });

    // Handle delete button click
    $('#simple-table-rows').on('click', '.simple-table-delete-row', function() {
        // console.log("Delete button clicked.");  // debug log
        if (confirm('Are you sure you want to delete this row?')) {
            var row = $(this).closest('tr');
            var rowIndex = row.data('row');

            // console.log("Row index: " + rowIndex);  // debug log

            $.post(ajaxurl, {
                action: 'simple_table_plugin_delete_row',
                rowIndex: rowIndex,
                security: simpleTablePlugin.deleteNonce  // use the delete nonce
            }, function(response) {
                if (response.success) {
                    // console.log("Row deletion successful.");  // debug log
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