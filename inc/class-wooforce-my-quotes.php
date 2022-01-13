<?php


require_once WOOFORCE_PLUGIN_PATH . '/helpers/class-wooforce-helpers.php';


class WooForceMyQuotes
{
    static function my_quotes_link( $menu_links ){
	
        $menu_links = array_slice( $menu_links, 0, 5, true ) 
        + array( 'my-quotes' => 'My Quotes' )
        + array_slice( $menu_links, 5, NULL, true );
        
        return $menu_links;
    
    }

    static function my_quotes_add_endpoint() {

        // WP_Rewrite is my Achilles' heel, so please do not ask me for detailed explanation
        add_rewrite_endpoint( 'my-quotes', EP_PAGES );
    
    }

    static function my_quotes_my_account_endpoint_content() {

        // of course you can print dynamic content here, one of the most useful functions here is get_current_user_id()
        require_once WOOFORCE_PLUGIN_PATH . 'templates/my-quotes.php';
    }
    
}