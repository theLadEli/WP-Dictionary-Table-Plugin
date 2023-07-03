<?php
/*
Plugin Name: Simple Dictionary Table Plugin
Description: A simple plugin for a WordPress Dictionary Table
Version: 1.0
Author: Eli
*/

// Register the shortcode
function simple_table_shortcode() {
    $rows = get_option('simple_table_rows', array());
    ob_start();
    ?>
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

    // Delete row if requested
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['row'])) {
        $row_index = intval($_GET['row']);

        $rows = get_option('simple_table_rows', array());
        if (isset($rows[$row_index])) {
            unset($rows[$row_index]);
            update_option('simple_table_rows', array_values($rows));
        }
    }

    // Get existing rows
    $rows = get_option('simple_table_rows', array());

    // Generate the shortcode
    $shortcode = '[simple_table]';
    foreach ($rows as $row) {
        $shortcode .= PHP_EOL . '[row term="' . esc_attr($row['term']) . '" definition="' . esc_attr($row['definition']) . '"]';
    }
    $shortcode .= PHP_EOL . '[/simple_table]';
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
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $index => $row) { ?>
                        <tr>
                            <td><?php echo esc_html($row['term']); ?></td>
                            <td><?php echo esc_html($row['definition']); ?></td>
                            <td>
                                <a href="<?php echo esc_url(add_query_arg(array('action' => 'delete', 'row' => $index))); ?>">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No rows found.</p>
        <?php } ?>
        <h2>Embedding the Table</h2>
        <pre><code><?php echo esc_html($shortcode); ?></code></pre>
    </div>
    <?php
}
