<?php

require_once WOOFORCE_PLUGIN_PATH . '/helpers/class-wooforce-helpers.php';
require_once WOOFORCE_PLUGIN_PATH . '/inc/class-wooforce-quotes.php';
require_once WOOFORCE_PLUGIN_PATH . '/inc/class-wooforce-contacts.php';
require_once WOOFORCE_PLUGIN_PATH . '/inc/class-wooforce-accounts.php';

class WooForceOpportunities {

    public static function create_update_quote($id,$post,$updated)
    {
        if(get_post_type($id)=='shop_order') {
            global $wpdb;

            // $order = wc_get_order($id);
            // $user_id = $order->get_user_id();

            if (isset($user_id) && $user_id > 0) {

                $user = get_userdata($user_id);
                $company_name = get_user_meta($user_id, 'billing_company', true);
                $account_id = '';

                $acc = $wpdb->get_row("select sf_account_id from " . $wpdb->prefix . "sf_contacts where wc_user_id = '" . $user_id. "'");

                if ($wpdb->num_rows > 0) {
                    $account_id = $acc->sf_account_id;
                } else {
                    $_POST['first_name'] = $user->first_name;
                    $_POST['last_name'] = $user->last_name;
                    $_POST['email'] = $user->user_email;
                    $_POST['billing_company'] = $company_name;
                    $_POST['billing_phone'] = get_user_meta($user_id, 'billing_phone', true);;
                    $_POST['billing_city'] = get_user_meta($user_id, 'billing_city', true);;
                    $_POST['billing_country'] = get_user_meta($user_id, 'billing_country', true);;
                    $_POST['billing_postcode'] = get_user_meta($user_id, 'billing_postcode', true);;
                    $_POST['billing_state'] = get_user_meta($user_id, 'billing_state', true);;
                    $_POST['billing_address_1'] = get_user_meta($user_id, 'billing_address_1', true);;
                    $account_id = WooForceAccounts::insert_accounts($user_id);
                }

                $order_id = $id;

                get_post_meta( $order_id, '_ywcm_request_expire',true);

                $sf_version = WooForceHelpers::get_sf_version_url();
                $args = [
                    'headers' => [
                        'Authorization' => 'Bearer ' . get_option('wf_salesforce_token'),
                        'Content-Type' => 'application/json'
                    ],
                ];

                $body = [
                    'Name' => $company_name,
                    'CloseDate' => date('Y-m-d', strtotime('+1 month')),//get_post_meta( $order_id, '_ywcm_request_expire',true),
                    'StageName' => 'New',
                    'AccountId' => $account_id,
                    'Subsidiary__c' => 'AlphaProMed LLC',
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
                $insert_opportunity = wp_remote_post(BASE_URL . $sf_version . '/sobjects/Opportunity/', $args);
                $response_body = json_decode(wp_remote_retrieve_body($insert_opportunity));

                print_r($response_body);

                $http_code = wp_remote_retrieve_response_code($insert_opportunity);
                if ($http_code == 200 || $http_code == 201) {
                    if (isset($response_body->id) && !empty($response_body->id) && $account_id != '') {
                        WooForceQuotes::insert_quotes($response_body->id, $company_name, $order_id);
                    }
                }
            }
        }
    }

    public static function insert_opportunities($contact_form, $account_id) {

        //stripslahes does not strupslahes
        global $wpdb;

        $opp_name = 'New opportunity from ' . get_option('blogname');
        $opp_duration = date('Y-m-d', strtotime('+1 month'));
        $opp_subsidiary = 'AlphaProMed LLC';
        $opp_stagename = 'New';
        $page_referrer = str_replace("/", "", parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH));
        if(!$account_id) {
            $account_id = NULL;
            $opp_name = 'New opportunity from ' . get_option('blogname') . ' at ' . $page_referrer;
        }

        $sf_version = WooForceHelpers::get_sf_version_url();
        $body = [
            'Name' => $opp_name,
            'CloseDate' => $opp_duration,
            'StageName' => $opp_stagename,
            'Subsidiary__c' => $opp_subsidiary,
        ];
        if($account_id && $account_id !== NULL) {
            $body['AccountId'] = $account_id;
        }

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



        $insert_opportunity = wp_remote_post(BASE_URL . $sf_version . '/sobjects/Opportunity/', $args);
        $response_body = json_decode(wp_remote_retrieve_body($insert_opportunity));
        $http_code = wp_remote_retrieve_response_code($insert_opportunity);


        if($http_code == 201) {
            $table_name = $wpdb->prefix . "sf_opportunities";
            $wpdb->insert($table_name, [
                'sf_account_id' => $account_id,
                'sf_opportunity_id' => $response_body->id,
                'opportunity_name' => $opp_name,
                'opportunity_close_date' => $opp_duration,
                'opportunity_stage_name' => $opp_stagename,
                'opportunity_subsidiary__c' => $opp_subsidiary,
                'opportunity_type_of_bid__c' => null
            ]);
        }
    }

    /*
    public static function insert_opportunities() {

        global $wpdb;

        $user_id = get_current_user_id();
        $company_name = get_user_meta($user_id, 'billing_company', true);
        $next_month = date('Y-m-d', strtotime('+1 month'));
        $sf_version = WooForceHelpers::get_sf_version_url();


        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . get_option('wf_salesforce_token'),
                'Content-Type' => 'application/json'
            ],
        ];
        $account_id = wp_remote_get(BASE_URL . $sf_version . '/query/?q=SELECT+Id+from+Account+WHERE+name+LIKE+'. "'test company'", $args);
        $account_id = json_decode(wp_remote_retrieve_body($account_id));
        $account_id = $account_id->records->id;

        $body = [
            'Name' => $company_name,
            'CloseDate' => $next_month,
            'StageName' => 'New',
            'AccountId' => $account_id,
            'Subsidiary__c' => 'AlphaProMed LLC',
            'Type_of_Bid__c' => 'Outbound'
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

        $insert_opportunity = wp_remote_post(BASE_URL . $sf_version . '/sobjects/Opportunity/', $args);
        $response_body = json_decode(wp_remote_retrieve_body($insert_opportunity));

        $table_name = $wpdb->prefix . "sf_opportunities";
        $wpdb->insert($table_name, [
            'sf_account_id' => $account_id,
            'sf_opportunity_id' => $response_body->id,
            'opportunity_name' => $company_name,
            'opportunity_stage_name' => 'New',
            'opportunity_subsidiary__c' => 'AlphaProMed LLC',
            'opportunity_type_of_bid__c' => 'Outbound'
        ]);

        WooForceQuotes::insert_quotes($response_body->id);

    }*/
}

// New oppurnity from website {site-url}

//landing page name in the oppurtunity name
// the oppurtunity should also be created as the user registers on website