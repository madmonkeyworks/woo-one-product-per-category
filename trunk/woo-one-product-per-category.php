<?php
/**
 * Plugin Name: Woo One Product per Category
 * Description: Plugin made for WooCommerce. Allows only one product per category in the cart. The latest added item stays, items of the same category that are already in cart are removed.
 * Version: 1.0
 * Author: MadMonkey
 * Author URI: https://madmonkey.works
 * Text Domain: woo-one-product-per-category
 * Domain Path: /languages
 * Requires at least: 4.6
 * Tested up to: 5.1
 * WC requires at least: 3.1
 * WC tested up to: 3.5
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Add option to product category create form
add_action( 'product_cat_add_form_fields', 'woppc_add_option_field_add_form', 100, 1 );

function woppc_add_option_field_add_form($taxonomy) {
    
    woocommerce_wp_checkbox( array(
            'id' => 'woppc_one_product_per_category',
            'label' => __( 'One Product per Category', 'woo-one-product-per-category' ),
            'description' => __( 'When checked, will allow only one product per category in the cart. The latest added item will stay, all other products of this category already in cart will be removed.', 'woo-one-product-per-category' ),
            'type' => 'checkbox',
            'value' => 'yes'
        )
    );
}

// Add option to product category edit form
add_action('product_cat_edit_form_fields', 'woppc_add_option_field_edit_form', 100, 1);

function woppc_add_option_field_edit_form($term, $taxonomy) {
    
    //getting term ID
    $term_id = $term->term_id;
  
    // retrieve the existing value for this meta field.
    $value = get_term_meta($term_id, 'woppc_one_product_per_category', true);  
        
    ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="one-product-per-category"><?php _e('One Product per Category?', 'one-product-per-category'); ?></label></th>
        <td> 
            <input type="checkbox" id="woppc_one_product_per_category" name="woppc_one_product_per_category" value="<?php echo $value; ?>" <?php echo $value == "yes" ? checked( $value, 'yes' ) : ''; ?> />
            <span class="description"><?php echo __( 'When checked, will allow only one product per category in the cart. The latest added item will stay, all other products of this category already in cart will be removed.', 'woo-one-product-per-category' ); ?></span>
        </td>
    </tr>
    <?php
}

// Save the field 
add_action('edited_product_cat', 'woppc_save_option_field', 10, 1);
add_action('create_product_cat', 'woppc_save_option_field', 10, 1);

function woppc_save_option_field($term_id) {

    $value = isset($_POST['woppc_one_product_per_category']) ? 'yes' : 'no';
    update_term_meta($term_id, 'woppc_one_product_per_category', $value);
}


// Validate add-to-cart action.
add_filter( 'woocommerce_add_to_cart_validation', 'woppc_add_to_cart_validation', 10, 3 );

function woppc_add_to_cart_validation( $passed, $product_id, $quantity) {
    
    // Getting the product categories slugs in an array for the current product
    $product_cats_object = (array) get_the_terms( $product_id, 'product_cat' );
    $product_cats = array();
    
    foreach($product_cats_object as $obj_prod_cat) {

        if ( get_term_meta( $obj_prod_cat->term_id, 'woppc_one_product_per_category', true) == 'yes' ) {
            
            $product_cats[] = $obj_prod_cat->slug;
        }
    }

    if (count($product_cats)) {
    
        // Iterating through each cart item
        foreach ( (array) WC()->cart->get_cart() as $cart_item_key => $cart_item ){

            // When the product category of the current product match with a cart item
            if( has_term( $product_cats, 'product_cat', $cart_item['product_id'] ))
            {
                // Removing the cart item
                WC()->cart->remove_cart_item($cart_item_key);
            }
        }
    }
    return $passed;
}

