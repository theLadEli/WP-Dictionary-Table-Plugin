<?php
/*
Plugin Name: Simple Dictionary Table Plugin
Description: A simple plugin for a WordPress Dictionary Table
Version: 2.0
Author: Eli
*/

// Register the shortcode
function simple_table_shortcode() {
    $rows = get_option('simple_table_rows', array());
    ob_start();
    ?>

    <div class="table-wrapper">
        <table class="simple-table">
            <tr>
                <th>Term</th>
                <th>Definition</th>
            </tr>
            <?php foreach ($rows as $row) { ?>
                <tr>
                    <td><?php echo esc_html($row['term']); ?></td>
                    <td><?php echo esc_html($row['definition']); ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('simple_table', 'simple_table_shortcode');

// Register the admin menu
function simple_table_plugin_menu() {
    add_menu_page(
        'Simple Table Plugin',
        'Simple Table',
        'manage_options',
        'simple-table-plugin',
        'simple_table_plugin_settings_page',
        'dashicons-editor-table',
        30
    );
}
add_action('admin_menu', 'simple_table_plugin_menu');

// Enqueue the plugin CSS
function simple_table_plugin_styles() {
    wp_enqueue_style('simple-table-plugin', plugin_dir_url(__FILE__) . 'css/simple-table.css');
}
add_action('wp_enqueue_scripts', 'simple_table_plugin_styles');

// Enqueue the admin CSS and JavaScript
function simple_table_plugin_admin_scripts() {
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_style('simple-table-plugin', plugin_dir_url(__FILE__) . 'css/simple-table.css');
    wp_enqueue_script('simple-table-plugin', plugin_dir_url(__FILE__) . 'js/simple-table.js', array('jquery', 'jquery-ui-sortable'));
}
add_action('admin_enqueue_scripts', 'simple_table_plugin_admin_scripts');

// Localize the admin JavaScript
function simple_table_plugin_localize_scripts() {
    wp_localize_script('simple-table-plugin', 'simpleTablePlugin', array(
        'security' => wp_create_nonce('simple-table-plugin'),
        'deleteNonce' => wp_create_nonce('simple-table-plugin-delete-row') // new nonce for delete
    ));
}
add_action('admin_enqueue_scripts', 'simple_table_plugin_localize_scripts');

function simple_table_plugin_update_nonce() {
    wp_send_json_success(array(
        'security' => wp_create_nonce('simple-table-plugin'),
    ));
}
add_action('wp_ajax_simple_table_plugin_update_nonce', 'simple_table_plugin_update_nonce');


// Handle AJAX reorder
function simple_table_plugin_reorder_rows() {
    check_ajax_referer('simple-table-plugin-reorder-rows', 'security');

    $rows = $_POST['rows'];
    update_option('simple_table_rows', $rows);

    wp_send_json_success();
}
add_action('wp_ajax_simple_table_plugin_reorder_rows', 'simple_table_plugin_reorder_rows');

// Handle AJAX delete
function simple_table_plugin_delete_row() {
    check_ajax_referer('simple-table-plugin-delete-row', 'security');

    $rowIndex = $_POST['rowIndex'];
    $rows = get_option('simple_table_rows', array());
    
    if(isset($rows[$rowIndex])) {
        unset($rows[$rowIndex]);
        $rows = array_values($rows); // Re-index the array
        update_option('simple_table_rows', $rows);
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}
add_action('wp_ajax_simple_table_plugin_delete_row', 'simple_table_plugin_delete_row');

// Handle AJAX CSV export
function simple_table_plugin_export_csv() {
    check_ajax_referer('simple-table-plugin', 'security');

    // Get the rows
    $rows = get_option('simple_table_rows', array());

    // Generate CSV content
    $csv = "Term,Definition\n"; // header
    foreach ($rows as $row) {
        $csv .= esc_html($row['term']) . ',' . esc_html($row['definition']) . "\n";
    }

    // Send the CSV to the browser
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="simple-table.csv"');
    echo $csv;

    // End execution to prevent any unwanted additional output
    die();
}
add_action('wp_ajax_simple_table_plugin_export_csv', 'simple_table_plugin_export_csv');


// Render the settings page
function simple_table_plugin_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    // Save new row if submitted
    if (isset($_POST['submit'])) {
        $term = sanitize_text_field($_POST['term']);
        $definition = sanitize_text_field($_POST['definition']);
        $rows = get_option('simple_table_rows', array());
        $rows[] = array('term' => $term, 'definition' => $definition);
        update_option('simple_table_rows', $rows);
    }

    // Get existing rows
    $rows = get_option('simple_table_rows', array());

    // Generate the shortcode
    $shortcode = '[simple_table]';
    ?>
    <div class="wrap">
        <h1>Simple Table Plugin Settings</h1>
        <form method="POST" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Term</th>
                    <td><input type="text" name="term" class="regular-text"></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Definition</th>
                    <td><input type="text" name="definition" class="regular-text"></td>
                </tr>
            </table>
            <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Add Row"></p>
        </form>
        <h2>Table Rows</h2>
        <?php if (!empty($rows)) { ?>
        
            <table class="wp-list-table widefat fixed striped">

                <thead>
                    <tr>
                        <th>Term</th>
                        <th>Definition</th>
                    </tr>
                </thead>
                    <tbody id="simple-table-rows">
                        <?php foreach ($rows as $index => $row) { ?>
                            <tr data-row="<?php echo $index; ?>">
                                <td class="drag-handle">
                                    <img src="<?php echo plugin_dir_url(__FILE__) . 'assets/drag-icon.svg'; ?>" width="20">
                                </td>
                                <td><?php echo esc_html($row['term']); ?></td>
                                <td><?php echo esc_html($row['definition']); ?></td>
                                <td><button class="simple-table-delete-row button">Delete</button></td>
                            </tr>
                        <?php } ?>
                    </tbody>
            </table>

        <?php } else { ?>
            <p>No rows found.</p>
        <?php } ?>
        <h2>Embedding the Table</h2>
        <pre><code id="simple-table-shortcode"><?php echo esc_html($shortcode); ?></code></pre>
        <button id="simple-table-copy-shortcode">Copy Shortcode</button>

        
        <button id="simple-table-export-csv" class="button button-primary">Export as CSV</button>


    </div>
    <?php



    
}
?>