<?php


require_once WOOFORCE_PLUGIN_PATH . '/helpers/class-wooforce-helpers.php';


class WooForceNotes
{

    public static function insert_notes($post_id)
    {
        
        global $wpdb;
        $order = new WC_Order($post_id);

        
        $user_id = get_post_meta($post_id, 'csp_customer_id', true);
        $order_status = $_POST['order_status'];

       
        if( isset($user_id) && !empty($user_id)) {
            

                // QUERY TO GET sf_account_id
                $qry = "SELECT * FROM " . $wpdb->prefix . "sf_accounts WHERE wc_user_id=" . $user_id;
    
                // GETTING BACK sf_account_id
                $sf_accounts = $wpdb->get_results($qry);
                   
                if (count($sf_accounts) > 0) {
    
    
                    $products_details = "";
                    $order = wc_get_order($order->get_order_number());
                    $index = 1;
                    foreach ($order->get_items() as $item) {
                        $product = wc_get_product($item['product_id']);
                        $item_image = $product->get_image();
                        //$item_ref_id = $item['product_id'];
                        $item_name = $item['name'];
                        $item_total = $item['total'];
                        $item_quantity = $item['quantity'];
    
    
                        $variation_id = $item->get_variation_id();
                        $variation = new WC_Product_Variation($variation_id);
    
                        $backorder = 0;
                        $_remaining_stock = $variation->get_stock_quantity() - $item_quantity;
    
                        if ($variation->get_stock_quantity() > 0) {
                            if ($_remaining_stock < 0) {
                                $backorder = $_remaining_stock * -1;
                            }
                        } else {
                            $backorder = $item_quantity;
                        }
    
                        $pv = wc_get_product($variation_id);
                        $item_ref_id = "";
                        $dimension = "-";
                        if($pv){
                           
                            $item_ref_id = $pv->get_sku();
                            // $length = $pv->get_length();
                            // $width = $pv->get_width();
                            // $height = $pv->get_height();
                            
                            // $dimension = $length .'x' . $width .'x' .$height .' (cm)';
                        }
                        $variationName = $variation->get_variation_attributes();
    
                        $products_details .= '
                <tr>
                    <td style="border:1px solid black" colspan="2"><b>' . $index . ') ' . $item_name . '</b></td>
                </tr>
                
                <tr>
                    <td style="border:1px solid black">Ref</td>
                    <td style="border:1px solid black">' . $item_ref_id . '</td>
                </tr>
    
                <tr>
                    <td style="border:1px solid black">Item quantity</td>
                    <td style="border:1px solid black">' . $item_quantity . '</td>
                </tr>
    
                <tr>
                    <td style="border:1px solid black">Item Total</td>
                    <td style="border:1px solid black">' . $item_total . '</td>
                </tr>
                
                ';
    
    
                        foreach ($variationName as $key => $value) {
    
                            // Get attribute taxonomy name
                            $taxonomy   = str_replace('attribute_', '', $key);
                            // Get attribute label name
                            $label_name = wc_attribute_label($taxonomy);
    
                            if ($label_name == 'Packing Level') {
                                $label_name = 'Unit of measure';
                            }
    
                            // Get attribute term name value
                            $term_name  = get_term_by('slug', $value, $taxonomy)->name;
    
    
                            $products_details .= '
                    <tr>
                        <td style="border:1px solid black">' . $label_name . '</td>
                        <td style="border:1px solid black">' . $term_name . '</td>
                    </tr>
                    ';
    
                            if ($backorder > 0) {
                                $products_details .= '
                        <tr>
                            <td style="border:1px solid black">backordered</td>
                            <td style="border:1px solid black">' . $backorder . '</td>
                        </tr>
                        ';
                            }
                        }
    
                        $index++;
                    }
    
    
                    $order_details = '<table>
        <tr>
        <td style="border:1px solid black">Order number</td>
        <td style="border:1px solid black">' . $order->get_order_number() . '</td>
        </tr>
    
        <tr>
        <td style="border:1px solid black">Customer name</td>
        <td style="border:1px solid black">' . $order->get_billing_first_name() . " " . $order->get_billing_last_name() . '</td>
        </tr>
    
        <tr>
        <td style="border:1px solid black">Order status</td>
        <td style="border:1px solid black">' . $order_status . '</td>
        </tr>
    
        
            ' . $products_details . '
    
            <tr>
                 <td style="border:1px solid black" colspan="2"><b>Summary</b></td>
            </tr>
    
            <tr>
            <td style="border:1px solid black">Sub Total</td>
            <td style="border:1px solid black">' . get_woocommerce_currency_symbol() . number_format($order->get_subtotal(), 2) . '</td>
            </tr>  
    
            <tr>
            <td style="border:1px solid black">Shipping</td>
            <td style="border:1px solid black">' . get_woocommerce_currency_symbol() . number_format($order->get_shipping_total(), 2) . '</td>
            </tr>  
            
            <tr>
            <td style="border:1px solid black">Discount</td>
            <td style="border:1px solid black">' . get_woocommerce_currency_symbol() . number_format($order->get_total_discount(), 2) . '</td>
            </tr>  
    
            <tr>
            <td style="border:1px solid black">Tax</td>
            <td style="border:1px solid black">' . get_woocommerce_currency_symbol() . number_format($order->get_total_tax(), 2) . '</td>
            </tr>  
    
            <tr>
            <td style="border:1px solid black">Additional Fee</td>
            <td style="border:1px solid black">' . get_woocommerce_currency_symbol() . number_format($order->get_total_fees(), 2) . '</td>
            </tr> 
            
            <tr>
            <td style="border:1px solid black">Payment Method</td>
            <td style="border:1px solid black">' . $order->get_payment_method_title() . '</td>
            </tr> 
    
            <tr>
            <td style="border:1px solid black">Total</td>
            <td style="border:1px solid black">' . get_woocommerce_currency_symbol() . number_format($order->get_total(), 2) . '</td>
            </tr> 
    
    
            <tr>
            <td style="border:1px solid black">Billing Detail</td>
            <td style="border:1px solid black">' .
                        $order->get_billing_first_name() . " " . $order->get_billing_last_name() . '<br>' .
                        $order->get_billing_company() . '<br>' .
                        $order->get_billing_address_1() . '<br>' .
                        $order->get_billing_address_2() . '<br>' .
                        $order->get_billing_city() . ',' . $order->get_billing_country() . '<br>' .
                        $order->get_billing_phone() . '<br>' .
                        $order->get_billing_email() .
                        '</td>
            </tr> 
    
    
            <tr>
            <td style="border:1px solid black">Shipping Detail</td>
            <td style="border:1px solid black">' .
                        $order->get_shipping_first_name() . " " . $order->get_shipping_last_name() . '<br>' .
                        $order->get_shipping_company() . '<br>' .
                        $order->get_shipping_address_1() . '<br>' .
                        $order->get_shipping_address_2() . '<br>' .
                        $order->get_shipping_city() . ',' . $order->get_shipping_country() . '<br>' .
                        '</td>
            </tr> 
    
        </table>';
    
    
                    $sf_version = WooForceHelpers::get_sf_version_url();
    
                    $body = [
                        'Content' => base64_encode($order_details),
                        'Title' => "Order #" . $order->get_order_number() .', status: '. $order_status
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
    
                    $insert_note = wp_remote_post(BASE_URL . $sf_version . '/sobjects/ContentNote/', $args);
                    $response_body = json_decode(wp_remote_retrieve_body($insert_note));
                    $http_code = wp_remote_retrieve_response_code($insert_note);
                    // echo "<h1>" . $http_code . " response 1</h1>";
                    // print_r($response_body->id);die;
                    if ($http_code == '201') {
    
                        $link_body = [
                            'ContentDocumentId' => $response_body->id,
                            'LinkedEntityId' => $sf_accounts[0]->sf_account_id, //salesforce id
                        ];
    
                        $link_args = [
                            'body' => json_encode($link_body),
                            'timeout' => '5',
                            'redirection' => '5',
                            'httpversion' => '1.0',
                            'headers' => [
                                'Authorization' => 'Bearer ' . get_option('wf_salesforce_token'),
                                'Content-Type' => 'application/json'
                            ],
                        ];
    
                        $link_note = wp_remote_post(BASE_URL . $sf_version . '/sobjects/ContentDocumentLink/', $link_args);
                        $link_response_body = json_decode(wp_remote_retrieve_body($link_note));
                        $http_code = wp_remote_retrieve_response_code($link_note);
    
                        // echo "<h1>" . $http_code . " ssss 2</h1>";
                        // print_r([$link_response_body, $sf_accounts[0]->sf_account_id]);die;
                    }
                }
            


        }

       
    }


    public static function on_status_change_create_notes($order_id,$old_status,$new_status)
    {

        global $wpdb;
        $order = new WC_Order($order_id);
        
        
        $user_id = $order->get_user_id();
        $order_status = $new_status;
       
        if( isset($user_id) && !empty($user_id)) {
            
            
                // QUERY TO GET sf_account_id
                $qry = "SELECT * FROM " . $wpdb->prefix . "sf_accounts WHERE wc_user_id=" . $user_id;
    
                // GETTING BACK sf_account_id
                $sf_accounts = $wpdb->get_results($qry);
                   
                if (count($sf_accounts) > 0) {
    
    
                    $products_details = "";
                    $order = wc_get_order($order->get_order_number());
                    $index = 1;
                    foreach ($order->get_items() as $item) {
                        $product = wc_get_product($item['product_id']);
                        if(!$product){
                            continue;
                        }
                        $item_image = $product->get_image();
                        //$item_ref_id = $item['product_id'];
                        $item_name = $item['name'];
                        $item_total = $item['total'];
                        $item_quantity = $item['quantity'];
    
    
                        $variation_id = $item->get_variation_id();
                        $variation = new WC_Product_Variation($variation_id);
    
                        $backorder = 0;
                        $_remaining_stock = $variation->get_stock_quantity() - $item_quantity;
    
                        if ($variation->get_stock_quantity() > 0) {
                            if ($_remaining_stock < 0) {
                                $backorder = $_remaining_stock * -1;
                            }
                        } else {
                            $backorder = $item_quantity;
                        }
    
                        $pv = wc_get_product($variation_id);
                        $item_ref_id = "";
                        $dimension = "-";
                        if($pv){
                           
                            $item_ref_id = $pv->get_sku();
                            // $length = $pv->get_length();
                            // $width = $pv->get_width();
                            // $height = $pv->get_height();
                            
                            // $dimension = $length .'x' . $width .'x' .$height .' (cm)';
                        }
                        $variationName = $variation->get_variation_attributes();
    
                        $products_details .= '
                <tr>
                    <td style="border:1px solid black" colspan="2"><b>' . $index . ') ' . $item_name . '</b></td>
                </tr>
                
                <tr>
                    <td style="border:1px solid black">Ref</td>
                    <td style="border:1px solid black">' . $item_ref_id . '</td>
                </tr>
    
                <tr>
                    <td style="border:1px solid black">Item quantity</td>
                    <td style="border:1px solid black">' . $item_quantity . '</td>
                </tr>
    
                <tr>
                    <td style="border:1px solid black">Item Total</td>
                    <td style="border:1px solid black">' . $item_total . '</td>
                </tr>
                
                ';
    
    
                        foreach ($variationName as $key => $value) {
    
                            // Get attribute taxonomy name
                            $taxonomy   = str_replace('attribute_', '', $key);
                            // Get attribute label name
                            $label_name = wc_attribute_label($taxonomy);
    
                            if ($label_name == 'Packing Level') {
                                $label_name = 'Unit of measure';
                            }
    
                            // Get attribute term name value
                            $term_name  = get_term_by('slug', $value, $taxonomy)->name;
    
    
                            $products_details .= '
                    <tr>
                        <td style="border:1px solid black">' . $label_name . '</td>
                        <td style="border:1px solid black">' . $term_name . '</td>
                    </tr>
                    ';
    
                            if ($backorder > 0) {
                                $products_details .= '
                        <tr>
                            <td style="border:1px solid black">backordered</td>
                            <td style="border:1px solid black">' . $backorder . '</td>
                        </tr>
                        ';
                            }
                        }
    
                        $index++;
                    }
    
    
                    $order_details = '<table>
        <tr>
        <td style="border:1px solid black">Order number</td>
        <td style="border:1px solid black">' . $order->get_order_number() . '</td>
        </tr>
    
        <tr>
        <td style="border:1px solid black">Customer name</td>
        <td style="border:1px solid black">' . $order->get_billing_first_name() . " " . $order->get_billing_last_name() . '</td>
        </tr>
    
        <tr>
        <td style="border:1px solid black">Order status</td>
        <td style="border:1px solid black">' . $order_status . '</td>
        </tr>
    
        
            ' . $products_details . '
    
            <tr>
                 <td style="border:1px solid black" colspan="2"><b>Summary</b></td>
            </tr>
    
            <tr>
            <td style="border:1px solid black">Sub Total</td>
            <td style="border:1px solid black">' . get_woocommerce_currency_symbol() . number_format($order->get_subtotal(), 2) . '</td>
            </tr>  
    
            <tr>
            <td style="border:1px solid black">Shipping</td>
            <td style="border:1px solid black">' . get_woocommerce_currency_symbol() . number_format($order->get_shipping_total(), 2) . '</td>
            </tr>  
            
            <tr>
            <td style="border:1px solid black">Discount</td>
            <td style="border:1px solid black">' . get_woocommerce_currency_symbol() . number_format($order->get_total_discount(), 2) . '</td>
            </tr>  
    
            <tr>
            <td style="border:1px solid black">Tax</td>
            <td style="border:1px solid black">' . get_woocommerce_currency_symbol() . number_format($order->get_total_tax(), 2) . '</td>
            </tr>  
    
            <tr>
            <td style="border:1px solid black">Additional Fee</td>
            <td style="border:1px solid black">' . get_woocommerce_currency_symbol() . number_format($order->get_total_fees(), 2) . '</td>
            </tr> 
            
            <tr>
            <td style="border:1px solid black">Payment Method</td>
            <td style="border:1px solid black">' . $order->get_payment_method_title() . '</td>
            </tr> 
    
            <tr>
            <td style="border:1px solid black">Total</td>
            <td style="border:1px solid black">' . get_woocommerce_currency_symbol() . number_format($order->get_total(), 2) . '</td>
            </tr> 
    
    
            <tr>
            <td style="border:1px solid black">Billing Detail</td>
            <td style="border:1px solid black">' .
                        $order->get_billing_first_name() . " " . $order->get_billing_last_name() . '<br>' .
                        $order->get_billing_company() . '<br>' .
                        $order->get_billing_address_1() . '<br>' .
                        $order->get_billing_address_2() . '<br>' .
                        $order->get_billing_city() . ',' . $order->get_billing_country() . '<br>' .
                        $order->get_billing_phone() . '<br>' .
                        $order->get_billing_email() .
                        '</td>
            </tr> 
    
    
            <tr>
            <td style="border:1px solid black">Shipping Detail</td>
            <td style="border:1px solid black">' .
                        $order->get_shipping_first_name() . " " . $order->get_shipping_last_name() . '<br>' .
                        $order->get_shipping_company() . '<br>' .
                        $order->get_shipping_address_1() . '<br>' .
                        $order->get_shipping_address_2() . '<br>' .
                        $order->get_shipping_city() . ',' . $order->get_shipping_country() . '<br>' .
                        '</td>
            </tr> 
    
        </table>';
    
    
                    $sf_version = WooForceHelpers::get_sf_version_url();
    
                    $body = [
                        'Content' => base64_encode($order_details),
                        'Title' => "Order #" . $order->get_order_number() .', status: '. $order_status
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
    
                    $insert_note = wp_remote_post(BASE_URL . $sf_version . '/sobjects/ContentNote/', $args);
                    $response_body = json_decode(wp_remote_retrieve_body($insert_note));
                    $http_code = wp_remote_retrieve_response_code($insert_note);
                    // echo "<h1>" . $http_code . " response 1</h1>";
                    // print_r($response_body->id);die;
                    if ($http_code == '201') {
    
                        $link_body = [
                            'ContentDocumentId' => $response_body->id,
                            'LinkedEntityId' => $sf_accounts[0]->sf_account_id, //salesforce id
                        ];
    
                        $link_args = [
                            'body' => json_encode($link_body),
                            'timeout' => '5',
                            'redirection' => '5',
                            'httpversion' => '1.0',
                            'headers' => [
                                'Authorization' => 'Bearer ' . get_option('wf_salesforce_token'),
                                'Content-Type' => 'application/json'
                            ],
                        ];
    
                        $link_note = wp_remote_post(BASE_URL . $sf_version . '/sobjects/ContentDocumentLink/', $link_args);
                        $link_response_body = json_decode(wp_remote_retrieve_body($link_note));
                        $http_code = wp_remote_retrieve_response_code($link_note);
    
                        // echo "<h1>" . $http_code . " ssss 2</h1>";
                        // print_r([$link_response_body, $sf_accounts[0]->sf_account_id]);die;
                    }
                }
            


        }

       
    }


   
    public static function fe_checkout_insert_notes($order_id)
    {

        global $wpdb;
        $order = new WC_Order($order_id);
       
        
        $user_id = $order->get_user_id();
        $order_status = $order->get_status();
        // print_r([
        //     'user_id' => $user_id,
        //     'order_status' => $$order->get_status(),
        //     'order' => $order
        // ]);
        // die;
        if( isset($user_id) && !empty($user_id)) {
            
            
                // QUERY TO GET sf_account_id
                $qry = "SELECT * FROM " . $wpdb->prefix . "sf_accounts WHERE wc_user_id=" . $user_id;
    
                // GETTING BACK sf_account_id
                $sf_accounts = $wpdb->get_results($qry);
                   
                if (count($sf_accounts) > 0) {
    
    
                    $products_details = "";
                    $order = wc_get_order($order->get_order_number());
                    $index = 1;
                    foreach ($order->get_items() as $item) {
                        $product = wc_get_product($item['product_id']);
                        
                        if(!$product){
                            continue;
                        }

                        $item_image = $product->get_image();
                        //$item_ref_id = $item['product_id'];
                        $item_name = $item['name'];
                        $item_total = $item['total'];
                        $item_quantity = $item['quantity'];
    
    
                        $variation_id = $item->get_variation_id();
                        $variation = new WC_Product_Variation($variation_id);
    
                        $backorder = 0;
                        $_remaining_stock = $variation->get_stock_quantity() - $item_quantity;
    
                        if ($variation->get_stock_quantity() > 0) {
                            if ($_remaining_stock < 0) {
                                $backorder = $_remaining_stock * -1;
                            }
                        } else {
                            $backorder = $item_quantity;
                        }
    
                        $pv = wc_get_product($variation_id);
                        $item_ref_id = "";
                        $dimension = "-";
                        if($pv){
                           
                            $item_ref_id = $pv->get_sku();
                            // $length = $pv->get_length();
                            // $width = $pv->get_width();
                            // $height = $pv->get_height();
                            
                            // $dimension = $length .'x' . $width .'x' .$height .' (cm)';
                        }
                        $variationName = $variation->get_variation_attributes();
    
                        $products_details .= '
                <tr>
                    <td style="border:1px solid black" colspan="2"><b>' . $index . ') ' . $item_name . '</b></td>
                </tr>
                
                <tr>
                    <td style="border:1px solid black">Ref</td>
                    <td style="border:1px solid black">' . $item_ref_id . '</td>
                </tr>
    
                <tr>
                    <td style="border:1px solid black">Item quantity</td>
                    <td style="border:1px solid black">' . $item_quantity . '</td>
                </tr>
    
                <tr>
                    <td style="border:1px solid black">Item Total</td>
                    <td style="border:1px solid black">' . $item_total . '</td>
                </tr>
                
                ';
    
    
                        foreach ($variationName as $key => $value) {
    
                            // Get attribute taxonomy name
                            $taxonomy   = str_replace('attribute_', '', $key);
                            // Get attribute label name
                            $label_name = wc_attribute_label($taxonomy);
    
                            if ($label_name == 'Packing Level') {
                                $label_name = 'Unit of measure';
                            }
    
                            // Get attribute term name value
                            $term_name  = get_term_by('slug', $value, $taxonomy)->name;
    
    
                            $products_details .= '
                    <tr>
                        <td style="border:1px solid black">' . $label_name . '</td>
                        <td style="border:1px solid black">' . $term_name . '</td>
                    </tr>
                    ';
    
                            if ($backorder > 0) {
                                $products_details .= '
                        <tr>
                            <td style="border:1px solid black">backordered</td>
                            <td style="border:1px solid black">' . $backorder . '</td>
                        </tr>
                        ';
                            }
                        }
    
                        $index++;
                    }
    
    
                    $order_details = '<table>
        <tr>
        <td style="border:1px solid black">Order number</td>
        <td style="border:1px solid black">' . $order->get_order_number() . '</td>
        </tr>
    
        <tr>
        <td style="border:1px solid black">Customer name</td>
        <td style="border:1px solid black">' . $order->get_billing_first_name() . " " . $order->get_billing_last_name() . '</td>
        </tr>
    
        <tr>
        <td style="border:1px solid black">Order status</td>
        <td style="border:1px solid black">' . $order_status . '</td>
        </tr>
    
        
            ' . $products_details . '
    
            <tr>
                 <td style="border:1px solid black" colspan="2"><b>Summary</b></td>
            </tr>
    
            <tr>
            <td style="border:1px solid black">Sub Total</td>
            <td style="border:1px solid black">' . get_woocommerce_currency_symbol() . number_format($order->get_subtotal(), 2) . '</td>
            </tr>  
    
            <tr>
            <td style="border:1px solid black">Shipping</td>
            <td style="border:1px solid black">' . get_woocommerce_currency_symbol() . number_format($order->get_shipping_total(), 2) . '</td>
            </tr>  
            
            <tr>
            <td style="border:1px solid black">Discount</td>
            <td style="border:1px solid black">' . get_woocommerce_currency_symbol() . number_format($order->get_total_discount(), 2) . '</td>
            </tr>  
    
            <tr>
            <td style="border:1px solid black">Tax</td>
            <td style="border:1px solid black">' . get_woocommerce_currency_symbol() . number_format($order->get_total_tax(), 2) . '</td>
            </tr>  
    
            <tr>
            <td style="border:1px solid black">Additional Fee</td>
            <td style="border:1px solid black">' . get_woocommerce_currency_symbol() . number_format($order->get_total_fees(), 2) . '</td>
            </tr> 
            
            <tr>
            <td style="border:1px solid black">Payment Method</td>
            <td style="border:1px solid black">' . $order->get_payment_method_title() . '</td>
            </tr> 
    
            <tr>
            <td style="border:1px solid black">Total</td>
            <td style="border:1px solid black">' . get_woocommerce_currency_symbol() . number_format($order->get_total(), 2) . '</td>
            </tr> 
    
    
            <tr>
            <td style="border:1px solid black">Billing Detail</td>
            <td style="border:1px solid black">' .
                        $order->get_billing_first_name() . " " . $order->get_billing_last_name() . '<br>' .
                        $order->get_billing_company() . '<br>' .
                        $order->get_billing_address_1() . '<br>' .
                        $order->get_billing_address_2() . '<br>' .
                        $order->get_billing_city() . ',' . $order->get_billing_country() . '<br>' .
                        $order->get_billing_phone() . '<br>' .
                        $order->get_billing_email() .
                        '</td>
            </tr> 
    
    
            <tr>
            <td style="border:1px solid black">Shipping Detail</td>
            <td style="border:1px solid black">' .
                        $order->get_shipping_first_name() . " " . $order->get_shipping_last_name() . '<br>' .
                        $order->get_shipping_company() . '<br>' .
                        $order->get_shipping_address_1() . '<br>' .
                        $order->get_shipping_address_2() . '<br>' .
                        $order->get_shipping_city() . ',' . $order->get_shipping_country() . '<br>' .
                        '</td>
            </tr> 
    
        </table>';
    
    
                    $sf_version = WooForceHelpers::get_sf_version_url();
    
                    $body = [
                        'Content' => base64_encode($order_details),
                        'Title' => "Order #" . $order->get_order_number() .', status: '. $order_status
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
    
                    $insert_note = wp_remote_post(BASE_URL . $sf_version . '/sobjects/ContentNote/', $args);
                    $response_body = json_decode(wp_remote_retrieve_body($insert_note));
                    $http_code = wp_remote_retrieve_response_code($insert_note);
                    // echo "<h1>" . $http_code . " response 1</h1>";
                    // print_r($response_body->id);die;
                    if ($http_code == '201') {
    
                        $link_body = [
                            'ContentDocumentId' => $response_body->id,
                            'LinkedEntityId' => $sf_accounts[0]->sf_account_id, //salesforce id
                        ];
    
                        $link_args = [
                            'body' => json_encode($link_body),
                            'timeout' => '5',
                            'redirection' => '5',
                            'httpversion' => '1.0',
                            'headers' => [
                                'Authorization' => 'Bearer ' . get_option('wf_salesforce_token'),
                                'Content-Type' => 'application/json'
                            ],
                        ];
    
                        $link_note = wp_remote_post(BASE_URL . $sf_version . '/sobjects/ContentDocumentLink/', $link_args);
                        $link_response_body = json_decode(wp_remote_retrieve_body($link_note));
                        $http_code = wp_remote_retrieve_response_code($link_note);
    
                        // echo "<h1>" . $http_code . " ssss 2</h1>";
                        // print_r([$link_response_body, $sf_accounts[0]->sf_account_id]);die;
                    }
                }
            


        }

       
    }
}
