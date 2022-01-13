<?php

/**
 * Trigger this on plugin uninstall
 *
 * @package WooForce
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die();
}

//Delete SalesForce option fields
delete_option('wf_salesforce_client_id');
delete_option('wf_salesforce_client_secret');
delete_option('wf_salesforce_callback_url');
delete_option('wf_salesforce_token');
delete_option('wf_salesforce_refresh_token');
delete_option('wf_salesforce_instance_url');


//Drop the tables
global $wpdb;
$wpdb->query("DROP TABLE {$wpdb->prefix}sf_accounts");
$wpdb->query("DROP TABLE {$wpdb->prefix}sf_contacts");
$wpdb->query("DROP TABLE {$wpdb->prefix}sf_opportunities");
$wpdb->query("DROP TABLE {$wpdb->prefix}sf_quotes");
$wpdb->query("DROP TABLE {$wpdb->prefix}sf_products");