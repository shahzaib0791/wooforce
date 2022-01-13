<?php


require_once WOOFORCE_PLUGIN_PATH . '/helpers/class-wooforce-helpers.php';


class WooForceProducts {

    public static function insert_products() {

        global $wpdb;

        $body = [
            'Name' => $_POST['product_name'],
        ];

        $args = [
            'body' => json_encode($body),
            'timeout' => '5',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => [
                'Authorization' => 'Bearer ' . get_option('wf_salesforce_token'),
                'Content-Type' => 'application/json'
            ],
        ];

        $sf_version = WooForceHelpers::get_sf_version_url();
        $insert_product = wp_remote_post(BASE_URL . $sf_version . '/sobjects/Product2/', $args);
        $response_body = json_decode(wp_remote_retrieve_body($insert_product));

        $table_name = $wpdb->prefix . "sf_products";
        $wpdb->insert($table_name, [
            'sf_product_id' => $response_body->id,
            'product_name' => $_POST['product_name'],
        ]);
    }
}