<?php


require_once WOOFORCE_PLUGIN_PATH . '/helpers/class-wooforce-helpers.php';
require_once WOOFORCE_PLUGIN_PATH . '/inc/class-wooforce-contacts.php';
require_once WOOFORCE_PLUGIN_PATH . '/inc/class-wooforce-opportunities.php';


class WooForceAccounts {

    public static function insert_accounts($wc_user_id) {
        
        if(!isset($_POST['register'])) {
            return;
        }
      
        global $wpdb;

        $body = [
            'Name' => $_POST['billing_company'],
            'Phone' => $_POST['billing_phone'],
            'BillingStreet' => $_POST['billing_address_1'],
            'BillingCity' => $_POST['billing_city'],
            'BillingState' => $_POST['billing_state'],
            'BillingPostalCode' => $_POST['billing_postcode'],
            'BillingCountry' => $_POST['billing_country'],
            'Billing_Email__c' => $_POST['email'],
            'ShippingStreet' => $_POST['billing_address_1'],
            'ShippingCity' => $_POST['billing_city'],
            'ShippingState' => $_POST['billing_state'],
            'ShippingPostalCode' => $_POST['billing_postcode'],
            'ShippingCountry' => $_POST['billing_country'],
            'Shipping_Email__c' => $_POST['email'],
            'Shipping_Phone__c' => $_POST['billing_phone'],
            'Subsidiary__c' => 'AlphaProMed LLC',
        ];

        $args = [
            'body' => json_encode($body),
            'timeout' => '5',
            'redirection' => '5',
            'httpversion' => '1.0',
            'headers' => [
                'Authorization' => 'Bearer ' . get_option('wf_salesforce_token'),
                'Content-Type' => 'application/json'
            ],
        ];
        
        $sf_version = WooForceHelpers::get_sf_version_url();
        $insert_account = wp_remote_post(BASE_URL . $sf_version . '/sobjects/Account/', $args);
        $response_body = json_decode(wp_remote_retrieve_body($insert_account));
        $http_code = wp_remote_retrieve_response_code($insert_account);
        
        if($http_code == 201) {
            $table_name = $wpdb->prefix . "sf_accounts";
            $wpdb->insert($table_name, [
                'sf_account_id' => $response_body->id,
                'wc_user_id' => $wc_user_id,
                'account_name' => $_POST['billing_company'],
                'account_phone' => $_POST['billing_phone'],
                'account_billing_city' => $_POST['billing_city'],
                'account_billing_country' => $_POST['billing_country'],
                'account_billing_postal_code' => $_POST['billing_postcode'],
                'account_billing_state' => $_POST['billing_state'],
                'account_billing_street' => $_POST['billing_address_1'],
                'account_shipping_street' => $_POST['billing_address_1'],
                'account_shipping_city' => $_POST['billing_city'],
                'account_shipping_country' => $_POST['billing_country'],
                'account_shipping_postal_code' => $_POST['billing_postcode'],
                'account_shipping_state' => $_POST['billing_state'],
                'account_subsidiary__c' => 'AlphaProMed LLC',
                'account_entity__c' => 'AlphaPro Med',
                'account_owner_id' => null,
                'account_record_type_id' => null,
                'account_parent_id' => null,
                'account_company_number__c' => null,
                'account_number' => null,
                'account_number_of_employees' => null,
                'account_jigsaw' => null,
                'account_market' => null,
                'account_website' => null,
                'account_industry' => null,
                'account_description' => null,
                'account_sector__c' => null,
                'account_user_id__c' => null,
                'account_vendor__c' => null,
                'account_supplier_portal_link__c' => null,
                'account_type__c' => null,
                'account_star_rating__c' => null,
                'account_status__c' => null,
                'account_shipping_email__c' => $_POST['email'],
                'account_shipping_phone__c' => $_POST['billing_phone'],
            ]);
   
            update_user_meta($wc_user_id, 'shipping_address_1', $_POST['billing_address_1']);
            update_user_meta($wc_user_id, 'shipping_city', $_POST['billing_city']);
            update_user_meta($wc_user_id, 'shipping_country', $_POST['billing_country']);
            update_user_meta($wc_user_id, 'shipping_postcode', $_POST['billing_postcode']);
            update_user_meta($wc_user_id, 'shipping_state', $_POST['billing_state']);
            update_user_meta($wc_user_id, 'shipping_company', $_POST['billing_company']);
            update_user_meta($wc_user_id, 'shipping_first_name', $_POST['first_name']);
            update_user_meta($wc_user_id, 'shipping_first_name', $_POST['last_name']);
            update_user_meta($wc_user_id, 'subsidiary__c', 'AlphaProMed LLC');
            update_user_meta($wc_user_id, 'entity__c', 'AlphaPro Med');
            update_user_meta($wc_user_id, 'owner_id', null);
            update_user_meta($wc_user_id, 'record_type_id', null);
            update_user_meta($wc_user_id, 'parent_id', null);
            update_user_meta($wc_user_id, 'company_number__c', null);
            update_user_meta($wc_user_id, 'number', null);
            update_user_meta($wc_user_id, 'number_of_employees', null);
            update_user_meta($wc_user_id, 'jigsaw', null);
            update_user_meta($wc_user_id, 'market', null);
            update_user_meta($wc_user_id, 'website', null);
            update_user_meta($wc_user_id, 'industry', null);
            update_user_meta($wc_user_id, 'description', null);
            update_user_meta($wc_user_id, 'sector__c', null);
            update_user_meta($wc_user_id, 'user_id__c', null);
            update_user_meta($wc_user_id, 'vendor__c', null);
            update_user_meta($wc_user_id, 'supplier_portal_link__c', null);
            update_user_meta($wc_user_id, 'type__c', null);
            update_user_meta($wc_user_id, 'star_rating__c', null);
            update_user_meta($wc_user_id, 'status__c', null);
            update_user_meta($wc_user_id, 'shipping_email__c', $_POST['email']);
            update_user_meta($wc_user_id, 'shipping_phone__c', $_POST['billing_phone']);
        
            WooForceContacts::insert_contacts($_POST, $response_body->id, $wc_user_id);
            WooForceOpportunities::insert_opportunities($response_body, $response_body->id);
        }
        
        return $response_body->id;
    }

    public static function update_accounts($wc_user_id) {

        if(!isset($_POST['submit'])) {
            return;
        }

        global $wpdb;
        $table_name_sf_accounts = $wpdb->prefix . "sf_accounts";
        $table_name_sf_contacts = $wpdb->prefix . "sf_contacts";
        $sf_version = WooForceHelpers::get_sf_version_url();
        $get_sf_ids = $wpdb->get_results("SELECT sf_contact_id, sf_account_id FROM $table_name_sf_contacts WHERE wc_user_id = '$wc_user_id'");
        $sf_account_id = $get_sf_ids[0]->sf_account_id;
        $sf_contact_id = $get_sf_ids[0]->sf_contact_id;

        if(!isset($sf_account_id) || !isset($sf_contact_id)) {
            echo "Account not updated in salesforce, please resynchronize the data";
        }

        if($_POST['role'] !== "healthcare_customer" || $_POST['role'] !== "distributor_customer") {
            wp_insert_user([
                'ID' => $wc_user_id,
                'role' => 'healthcare_customer'
            ]);
        }

        //Update the contact, user and contact user_meta information
        $wpdb->update($table_name_sf_contacts, [
            'contact_first_name' => $_POST['first_name'],
            'contact_last_name' => $_POST['last_name'],
            'contact_email' => $_POST['email'],
            'contact_mailing_street' => $_POST['billing_address_1'],
            'contact_mailing_city' => $_POST['billing_city'],
            'contact_mailing_state' => $_POST['billing_state'],
            'contact_mailing_postal_code' => $_POST['billing_postcode'],
            'contact_mailing_country' => $_POST['billing_country'],
        ], ['sf_contact_id' => $sf_contact_id]);

        $contact_body = [
            'FirstName' => $_POST['first_name'],
            'LastName' => $_POST['last_name'],
            'Email' => $_POST['email'],
            'MailingStreet' => $_POST['billing_address_1'],
            'MailingCity' => $_POST['billing_city'],
            'MailingState' => $_POST['billing_state'],
            'MailingPostalCode' => $_POST['billing_postcode'],
            'MailingCountry' => $_POST['billing_country'],
            'Customer_Role__c' => $_POST['role'] == 'distributor_customer'? 'Distributor' : 'Government / Healthcare / Industrial / Manufacturing'
        ];



        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => BASE_URL . $sf_version . '/sobjects/Contact/' . $sf_contact_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS => json_encode($contact_body),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . get_option('wf_salesforce_token'),
            ]
        ]);
        curl_exec($curl);
        curl_close($curl);

        update_user_meta($wc_user_id, 'mailing_street', $_POST['billing_address_1']);
        update_user_meta($wc_user_id, 'mailing_city', $_POST['billing_city']);
        update_user_meta($wc_user_id, 'mailing_state', $_POST['billing_state']);
        update_user_meta($wc_user_id, 'mailing_postal_code', $_POST['billing_postcode]']);
        update_user_meta($wc_user_id, 'mailing_country', $_POST['billing_country']);

        //Update the account and account user_meta information
        $wpdb->update($table_name_sf_accounts, [
            'account_billing_city' => $_POST['billing_city'],
            'account_billing_country' => $_POST['billing_country'],
            'account_billing_postal_code' => $_POST['billing_postcode'],
            'account_billing_state' => $_POST['billing_state'],
            'account_billing_street' => $_POST['billing_address_1'],
            'account_shipping_street' => $_POST['shipping_address_1'],
            'account_shipping_city' => $_POST['shipping_city'],
            'account_shipping_country' => $_POST['shipping_country'],
            'account_shipping_postal_code' => $_POST['shipping_postcode'],
            'account_shipping_state' => $_POST['shipping_state'],
            'account_shipping_email__c' => $_POST['billing_email'],
            'account_shipping_phone__c' => $_POST['billing_phone'],
        ], ['sf_account_id' => $sf_account_id]);

        $account_body = [
            'BillingCity' => $_POST['billing_city'],
            'BillingCountry' => $_POST['billing_country'],
            'BillingPostalCode' => $_POST['billing_postcode'],
            'BillingState' => $_POST['billing_state'],
            'BillingStreet' => $_POST['billing_address_1'],
            'ShippingStreet' => $_POST['shipping_address_1'],
            'ShippingCity' => $_POST['shipping_city'],
            'ShippingCountry' => $_POST['shipping_country'],
            'ShippingPostalCode' => $_POST['shipping_postcode'],
            'ShippingState' => $_POST['shipping_state'],
            'Shipping_Email__c' => $_POST['email'],
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => BASE_URL . $sf_version . '/sobjects/Account/' . $sf_account_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS => json_encode($account_body),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . get_option('wf_salesforce_token'),
            ]
        ]);
        curl_exec($curl);
        curl_close($curl);

        update_user_meta($wc_user_id, 'billing_company', $_POST['billing_company']);
        update_user_meta($wc_user_id, 'billing_phone', $_POST['billing_phone']);
        update_user_meta($wc_user_id, 'billing_first_name', $_POST['first_name']);
        update_user_meta($wc_user_id, 'billing_last_name', $_POST['last_name']);
        update_user_meta($wc_user_id, 'billing_city', $_POST['billing_city']);
        update_user_meta($wc_user_id, 'billing_country', $_POST['billing_postcode']);
        update_user_meta($wc_user_id, 'billing_postcode', $_POST['billing_postcode']);
        update_user_meta($wc_user_id, 'billing_state', $_POST['billing_state']);
        update_user_meta($wc_user_id, 'billing_address_1', $_POST['billing_address_1']);
        update_user_meta($wc_user_id, 'shipping_address_1', $_POST['shipping_address_1']);
        update_user_meta($wc_user_id, 'shipping_city', $_POST['shipping_city']);
        update_user_meta($wc_user_id, 'shipping_first_name', $_POST['first_name']);
        update_user_meta($wc_user_id, 'shipping_last_name', $_POST['last_name']);
        update_user_meta($wc_user_id, 'shipping_country', $_POST['shipping_country']);
        update_user_meta($wc_user_id, 'shipping_postcode', $_POST['shipping_postcode']);
        update_user_meta($wc_user_id, 'shipping_state', $_POST['shipping_state']);
        update_user_meta($wc_user_id, 'shipping_email__c', $_POST['email']);
    }
}