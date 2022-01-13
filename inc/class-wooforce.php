<?php

require_once WOOFORCE_PLUGIN_PATH . 'inc/class-wooforce-contacts.php';
require_once WOOFORCE_PLUGIN_PATH . 'inc/class-wooforce-accounts.php';
require_once WOOFORCE_PLUGIN_PATH . 'inc/class-wooforce-opportunities.php';
require_once WOOFORCE_PLUGIN_PATH . 'inc/class-wooforce-quotes.php';
require_once WOOFORCE_PLUGIN_PATH . 'inc/class-wooforce-notes.php';

class WooForce {

    public static function activate() {

        //Add SalesForce options fields and save authentication token
        add_option('wf_salesforce_client_id');
        add_option('wf_salesforce_client_secret');
        add_option('wf_salesforce_callback_url');
        add_option('wf_salesforce_token');
        add_option('wf_salesforce_refresh_token');
        add_option('wf_salesforce_instance_url');
        self::create_tables();
        flush_rewrite_rules();

    }

    public static function deactivate() {
        flush_rewrite_rules();
    }

    public static function register() {

        //Actions
        add_action('admin_menu', [self::class, 'add_admin_pages']);
        add_action('user_register', [WooForceAccounts::class, 'insert_accounts']);
        add_action('profile_update', [WooForceAccounts::class, 'update_accounts']);
        // add_action('save_post', [WooForceOpportunities::class, 'create_update_quote'], 10, 3);
        add_action('wpcf7_before_send_mail', [WooForceOpportunities::class, 'insert_opportunities'], 10, 2);
        add_action('woocommerce_new_order', [WooForceNotes::class, 'insert_notes'], 10, 1);
        // add_action( 'woocommerce_thankyou', [WooForceNotes::class, 'fe_checkout_insert_notes'], 10, 1 );
        add_action('woocommerce_order_status_changed', [WooForceNotes::class, 'on_status_change_create_notes'], 10, 4); 
       
    }

    //Function to create admin menu
    public static function add_admin_pages() {

        add_menu_page('WooForce Settings', 'WooForce Settings', 'manage_options', 'wooforce', [self::class, 'admin_index'], 'dashicons-admin-settings', 100);
    }

    //Function to render admin menu page template
    public static function admin_index() {

        require_once WOOFORCE_PLUGIN_PATH . 'templates/admin-index.php';

    }

    public static function create_tables() {

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "
            CREATE TABLE {$wpdb->prefix}sf_accounts (
                id int(10) NOT NULL AUTO_INCREMENT,
                sf_account_id varchar(255) NULL,
                wc_user_id int(10) NULL,
                account_name varchar(255) NULL,
                account_phone varchar(255) NULL,
                account_billing_city varchar(255) NULL,
                account_billing_country varchar(255) NULL,
                account_billing_postal_code int(12) NULL,
                account_billing_state varchar(255) NULL,
                account_billing_street varchar(255) NULL,
                account_shipping_street varchar(255) NULL,
                account_shipping_city varchar(255) NULL,
                account_shipping_country varchar(255) NULL,
                account_shipping_postal_code int(12) NULL,
                account_shipping_state varchar(255) NULL,
                account_subsidiary__c varchar(255) NULL,
                account_entity__c varchar(255) NULL,
                account_owner_id varchar(255) NULL,
                account_record_type_id varchar(255) NULL,
                account_parent_id varchar(255) NULL,
                account_company_number__c varchar(255) NULL,
                account_number varchar(255) NULL,
                account_number_of_employees int(12) NULL,
                account_jigsaw varchar(255) NULL,
                account_market varchar(255) NULL,
                account_website varchar(255) NULL,
                account_industry varchar(255) NULL,
                account_description text NULL,
                account_sector__c varchar(255) NULL,
                account_user_id__c varchar(255) NULL,
                account_vendor__c varchar(255) NULL,
                account_supplier_portal_link__c varchar(255) NULL,
                account_type__c varchar(255) NULL,
                account_star_rating__c varchar(255) NULL,
                account_status__c varchar(255) NULL,
                account_shipping_email__c varchar(255) NULL,
                account_shipping_phone__c varchar(255) NULL,           
                UNIQUE (wc_user_id),
                PRIMARY KEY  (id)
            )$charset_collate;

            CREATE TABLE {$wpdb->prefix}sf_contacts (
                id int(10) NOT NULL AUTO_INCREMENT,
                sf_account_id varchar(255) NULL,
                sf_contact_id varchar(255) NULL,
                wc_user_id int(10) NULL,
                contact_first_name varchar(255) NULL,
                contact_last_name varchar(255) NULL,
                contact_email varchar(255) NULL,
                contact_phone varchar(255) NULL,
                contact_subsidiary__c varchar(255) NULL,
                contact_salutation varchar(255) NULL,
                contact_middle_name varchar(255) NULL,
                contact_suffix varchar(255) NULL,
                contact_owner_id varchar(255) NULL,
                contact_reports_to_id varchar(255) NULL,
                contact_customer_role__c varchar(255) NULL,
                contact_title varchar(255) NULL,
                contact_department varchar(255) NULL,
                contact_fax varchar(255) NULL,
                contact_mobile_phone varchar(255) NULL,
                contact_job_title__c varchar(255) NULL,
                contact_company__c varchar(255) NULL,
                contact_mailing_street varchar(255) NULL,
                contact_mailing_city varchar(255) NULL,
                contact_mailing_state varchar(255) NULL,
                contact_mailing_postal_code int(12) NULL,
                contact_mailing_country varchar(255) NULL,                
                UNIQUE (sf_contact_id, wc_user_id),
                PRIMARY KEY  (id)
            )$charset_collate;
            
            CREATE TABLE {$wpdb->prefix}sf_opportunities (
                id int(10) NOT NULL AUTO_INCREMENT,
                sf_account_id varchar(255) NULL,
                sf_opportunity_id varchar(255) NULL,
                opportunity_name varchar(255) NULL,
                opportunity_close_date date DEFAULT NULl,
                opportunity_stage_name varchar(255) NULL,
                opportunity_amount decimal (16,2),
                opportunity_subsidiary__c varchar(255) NULL,
                opportunity_type_of_bid__c varchar(255) NULL,
                UNIQUE (sf_opportunity_id),
                PRIMARY KEY  (id)
            )$charset_collate;

            CREATE TABLE {$wpdb->prefix}sf_quotes (
                id int(10) NOT NULL AUTO_INCREMENT,
                sf_opportunity_id varchar(255) NULL,
                sf_quote_id varchar(255) NULL,
                quote_name varchar(255) NULL,
                quote_expiration_date date DEFAULT NULL,
                quote_total_price decimal (16,2) NULL,
                quote_subtotal decimal (16,2) NULL,
                UNIQUE (sf_quote_id),
                PRIMARY KEY  (id)
            )$charset_collate;
            
            CREATE TABLE {$wpdb->prefix}sf_products (
                id int(10) NOT NULL AUTO_INCREMENT,
                sf_product_id varchar(255) NULL,
                product_name varchar(255) NULL,
                UNIQUE (sf_product_id),
                PRIMARY KEY  (id)
            )$charset_collate;
            
            ";


        require_once(ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);

    }

}

WooForce::register();