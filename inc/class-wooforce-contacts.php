<?php


require_once WOOFORCE_PLUGIN_PATH . '/helpers/class-wooforce-helpers.php';


class WooForceContacts {

    public static function insert_contacts($contact_data, $sf_account_id, $wc_user_id) {

        global $wpdb;

        $body = [
            'FirstName' => $contact_data['first_name'],
            'LastName' => $contact_data['last_name'],
            'Email' => $contact_data['email'],
            'Phone' => $contact_data['billing_phone'],
            'MobilePhone' => $contact_data['billing_phone'],
            'AccountId' => $sf_account_id,
            'Subsidiary__c' => 'AlphaProMed LLC',
            'Company__c' => $contact_data['billing_company'],
            'MailingStreet' => $contact_data['billing_address_1'],
            'MailingCity' => $contact_data['billing_city'],
            'MailingState' => $contact_data['billing_state'],
            'MailingPostalCode' => $contact_data['billing_postcode'],
            'MailingCountry' => $contact_data['billing_country'],
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
        $insert_contact = wp_remote_post(BASE_URL . $sf_version . '/sobjects/Contact/', $args);
        $response_body = json_decode(wp_remote_retrieve_body($insert_contact));
        $http_code = wp_remote_retrieve_response_code($insert_contact);

        if($http_code == 201) {
            $table_name = $wpdb->prefix . "sf_contacts";
            $wpdb->insert($table_name, [
                'sf_account_id' => $sf_account_id,
                'sf_contact_id' => $response_body->id,
                'wc_user_id' => $wc_user_id,
                'contact_first_name' => $contact_data['first_name'],
                'contact_last_name' => $contact_data['last_name'],
                'contact_email' => $contact_data['email'],
                'contact_phone' => $contact_data['billing_phone'],
                'contact_subsidiary__c' => 'AlphaProMed LLC',
                'contact_salutation' => null,
                'contact_middle_name' => null,
                'contact_suffix' => null,
                'contact_owner_id' => null,
                'contact_reports_to_id' => null,
                'contact_title' => null,
                'contact_department' => null,
                'contact_fax' => null,
                'contact_mobile_phone' => $contact_data['billing_phone'],
                'contact_job_title__c' => null,
                'contact_company__c' => $contact_data['billing_company'],
                'contact_mailing_street' => $contact_data['billing_address_1'],
                'contact_mailing_city' => $contact_data['billing_city'],
                'contact_mailing_state' => $contact_data['billing_state'],
                'contact_mailing_postal_code' => $contact_data['billing_postcode'],
                'contact_mailing_country' => $contact_data['billing_country'],
            ]);
    
            update_user_meta($wc_user_id, 'salutation', null);
            update_user_meta($wc_user_id, 'middle_name', null);
            update_user_meta($wc_user_id, 'suffix', null);
            update_user_meta($wc_user_id, 'reports_to_id', null);
            update_user_meta($wc_user_id, 'title', null);
            update_user_meta($wc_user_id, 'department', null);
            update_user_meta($wc_user_id, 'fax', null);
            update_user_meta($wc_user_id, 'job_title__c', null);
            update_user_meta($wc_user_id, 'mailing_street', $contact_data['billing_address_1']);
            update_user_meta($wc_user_id, 'mailing_city', $contact_data['billing_city']);
            update_user_meta($wc_user_id, 'mailing_state', $contact_data['billing_state']);
            update_user_meta($wc_user_id, 'mailing_postal_code', $contact_data['billing_postcode']);
            update_user_meta($wc_user_id, 'mailing_country', $contact_data['billing_country']);
        }        
    }
}