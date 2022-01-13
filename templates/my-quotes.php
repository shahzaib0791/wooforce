<?php
/**
 * My Quotes
 *
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="woocommerce-MyAccount-content">
<table class="shop_table shop_table_responsive my_account_orders">

<thead>
    <tr>
        <?php foreach ( ['col1','col2','col3'] as $column_id => $column_name ) : ?>
            <th class="<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
        <?php endforeach; ?>
    </tr>
</thead>

<tbody>
<tr class="order">
            <td>val 1</td>
            <td>val 1</td>
            <td>
                <a href="" class="button">View Orders</a>
            </td>
        </tr>
</tbody>
</table>
</div>
