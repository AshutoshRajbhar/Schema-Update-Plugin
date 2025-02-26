<?php
/*
 * Plugin Name: Update Schema Code - Appventurez
 * Author: Ashutosh Rajbhar
 * Author URI: https://ashutoshrajbhar.com
 * Version: 1.0.1
 * Description: This Plugin is for updating multiple Schema Codes.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin Activation Hook
function update_schema_activation() {
    // Placeholder for activation logic if needed
}
register_activation_hook(__FILE__, 'update_schema_activation');

// Plugin Deactivation Hook
function update_schema_deactivation() {
    // Placeholder for deactivation logic if needed
}
register_deactivation_hook(__FILE__, 'update_schema_deactivation');

// Add the Meta Box
function schema_add_meta_box() {
    add_meta_box(
        'schema_meta_box',
        'Add Schema (JSON-LD only)',
        'schema_meta_box_callback',
        ['post', 'page'],
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'schema_add_meta_box');

// Meta Box Callback Function
function schema_meta_box_callback($post) {
    wp_nonce_field('save_schema_meta_box', 'schema_meta_box_nonce');
    $json_ld_schemas = get_post_meta($post->ID, '_json_ld_schemas', true);
    $json_ld_schemas = is_array($json_ld_schemas) ? $json_ld_schemas : [];

    foreach ($json_ld_schemas as $index => $schema) {
        ?>
        <div class="json-ld-schema">
            <label for="json_ld_schema_<?php echo $index; ?>">Schema Code (Entry <?php echo $index + 1; ?>):</label>
            <textarea name="json_ld_schemas[]" id="json_ld_schema_<?php echo $index; ?>" rows="10" cols="151"><?php echo esc_textarea($schema); ?></textarea>
            <button class="remove-schema button">Remove</button>
        </div>
        <?php
    }
    ?>
    <button id="add-schema" class="button">Add Another Schema</button>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('add-schema').addEventListener('click', function(e) {
                e.preventDefault();
                const index = document.querySelectorAll('textarea[name="json_ld_schemas[]"]').length;
                const newField = `
                    <div class="json-ld-schema">
                        <label for="json_ld_schema_${index}">Schema Code (Entry ${index + 1}):</label>
                        <textarea name="json_ld_schemas[]" id="json_ld_schema_${index}" rows="10" cols="151"></textarea>
                        <button class="remove-schema button">Remove</button>
                    </div>
                `;
                this.insertAdjacentHTML('beforebegin', newField);
            });

            document.body.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-schema')) {
                    e.preventDefault();
                    e.target.closest('.json-ld-schema').remove();
                }
            });
        });
    </script>
    <style>
        .json-ld-schema {
            margin-bottom: 10px;
        }
    </style>
<?php
}

// Save Meta Box Data
function save_schema_meta_box_data($post_id) {
    if (!isset($_POST['schema_meta_box_nonce']) || !wp_verify_nonce($_POST['schema_meta_box_nonce'], 'save_schema_meta_box')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_others_posts')) {
        return;
    }
    
    $json_ld_schemas = isset($_POST['json_ld_schemas']) ? array_map('sanitize_textarea_field', $_POST['json_ld_schemas']) : [];
    
    foreach ($json_ld_schemas as $key => $schema) {
        if (json_decode($schema) === null) {
            unset($json_ld_schemas[$key]); // Remove invalid JSON
        }
    }
    
    update_post_meta($post_id, '_json_ld_schemas', $json_ld_schemas);
}
add_action('save_post', 'save_schema_meta_box_data');

// Output JSON-LD Schema in <head>
function add_schema_code_to_head() {
    if (is_singular()) {
        global $post;
        $json_ld_schemas = get_post_meta($post->ID, '_json_ld_schemas', true);
        if (!empty($json_ld_schemas) && is_array($json_ld_schemas)) {
            foreach ($json_ld_schemas as $schema) {
                if (!empty($schema)) {
                    echo '<script type="application/ld+json">' . esc_js($schema) . '</script>';
                }
            }
        }
    }
}
add_action('wp_head', 'add_schema_code_to_head');
