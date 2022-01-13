<?php


require_once WOOFORCE_PLUGIN_PATH . '/helpers/class-wooforce-helpers.php';


class WooForceQuotes {

    public static function insert_quotes($opportunity_id, $company, $order_id) {

        global $wpdb;

        $order = wc_get_order($order_id);

        //3 Add Billing Fields

        $billing_address = ['city' => get_post_meta( $order_id, '_billing_city',true ),
                            'state' => get_post_meta( $order_id, '_billing_state',true ),
                            'postalCode' => get_post_meta( $order_id, '_billing_postcode',true ),
                            'street' => get_post_meta( $order_id, '_billing_address_1',true ),
                            'country' => get_post_meta( $order_id, '_billing_country',true )
                            ];

        //4 Add Shipping Fields

        $shipping_address = ['city' => get_post_meta( $order_id, '_shipping_city',true ),
                                'state' => get_post_meta( $order_id, '_shipping_state',true ),
                                'postalCode' => get_post_meta( $order_id, '_shipping_postcode',true ),
                                'street' => get_post_meta( $order_id, '_shipping_address_1',true ),
                                'country' => get_post_meta( $order_id, '_shipping_country',true )
                            ];

        $contact_id = $wpdb->get_row("select sf_contact_id from " . $wpdb->prefix . "sf_contacts where wc_user_id = '" . $order->get_user_id(). "'");

        $user = get_userdata($order->get_user_id());

        $body = [
            'OpportunityId' => $opportunity_id,
            'Name' => $company.': Quote#'.$order_id,
            'ExpirationDate' => date('Y-m-d', strtotime('+1 month')),//get_post_meta( $order_id, '_ywcm_request_expire',true),
            'Status' => 'Draft',
            'QuoteToName' => get_post_meta( $order_id, 'QuoteToName',true ),
            'Description' => get_post_meta( $order_id, 'Description',true ),
            'Ship_Date__c' => date('Y-m-d', strtotime('+1 month')),//get_post_meta( $order_id, 'Ship_Date__c',true ),
            'Payment_Terms__c' => get_post_meta( $order_id, 'Payment_Terms__c',true ),
            'Terms_and_Conditions__c' => get_post_meta( $order_id, 'Terms_and_Conditions__c',true ),
            'In_Hand_Date__c' => date('Y-m-d', strtotime('+1 month')),//get_post_meta( $order_id, 'In_Hand_Date__c',true ),
            'Approval_Date__c' => date('Y-m-d', strtotime('+1 month')),//get_post_meta( $order_id, 'Approval_Date__c',true ),
            'Notes__c' => get_post_meta( $order_id, 'Notes__c',true ),
            //'Discount' => get_post_meta( $order_id, 'Discount',true ),
            'Additional_Charges__c' => get_post_meta( $order_id, 'Additional_Charges__c',true ),
            'Payment_Received__c' => get_post_meta( $order_id, 'Payment_Received__c',true ),
            'Website__c' => get_post_meta( $order_id, 'Website__c',true ),
            'Fax' =>  get_post_meta( $order_id, 'Fax',true ),
            //'ContactId' => $contact_id,
            'Email' => get_post_meta( $order_id, 'ywraq_customer_email',true ),
            /*'BillingName' => get_user_meta($order->get_user_id(),'BillingName',true),
            'ShippingName' => get_user_meta($order->get_user_id(),'ShippingName',true),
            'BillingAddress' => $billing_address,
            'ShippingAddress' => $shipping_address*/
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

//if salesforce to woocmeorce then status of the order will be "In review"
        // if woocomerce

        //defautl expiry fo qyoteq 3 days

        $sf_version= WooForceHelpers::get_sf_version_url();
        $insert_quote = wp_remote_post(BASE_URL . $sf_version . '/sobjects/Quote/', $args);
        $response_body = json_decode(wp_remote_retrieve_body($insert_quote));

        $http_code = wp_remote_retrieve_response_code($insert_quote);
        if($http_code == 200 || $http_code == 201) {

            if (isset($response_body->id) && !empty($response_body->id)) {
                $table_name = $wpdb->prefix . "sf_quotes";
                $wpdb->update($table_name, [
                    'sf_quote_id' => $response_body->id
                ],['wp_order_id'=>$order_id]);
            }
        }else {
            print_r($response_body);echo $http_code;die;
        }
    }
}