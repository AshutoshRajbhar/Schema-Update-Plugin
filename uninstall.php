<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete all stored schema data
global $wpdb;
$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key = '_json_ld_schemas'");
?>