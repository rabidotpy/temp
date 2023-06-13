<?php
/**
 * Flatsome functions and definitions
 *
 * @package flatsome
 */

require get_template_directory() . '/inc/init.php';

/**
 * Note: It's not recommended to add any custom code here. Please use a child theme so that your customizations aren't lost during updates.
 * Learn more here: http://codex.wordpress.org/Child_Themes
 */





// The product custom field - Frontend - Product Page - Woocommerce
add_action( 'woocommerce_before_add_to_cart_button', 'custom_discount_price_product_field' );
function custom_discount_price_product_field() {
    global $product;

    $curs = get_woocommerce_currency_symbol(); // Currency symbol

    // Get the discounted value (from product backend)
    $discount = (float) get_post_meta( $product->get_id(), '_price_discount', true );

    // jQuery will get the discount here for calculations
    echo '<input type="hidden" name="price_discount" value="'.$discount.'">';

    echo '<div>';

    // This field will be used to send the calculated price
    // jQuery will set the calculated price on this field
    echo '<input type="hidden" id="custom_price" name="custom_price" value="1">'; // 52 is a fake value for testing purpose

    echo '</div>';

    // BELOW your jquery code to calculate price and update "custom_price" hidden field
    ?>
    <script type="text/javascript">
    jQuery( function($){
        // Here
    });
    </script>
    <?php
}



// Save product custom field to database when submitted in Backend
add_action( 'woocommerce_process_product_meta', 'save_product_options_custom_fields', 30, 1 );
function save_product_options_custom_fields( $post_id ){
    // Saving custom field value
    if( isset( $_POST['_price_discount'] ) ){
        update_post_meta( $post_id, '_price_discount', sanitize_text_field( $_POST['_price_discount'] ) );
    }
}

// 
// 
add_filter( 'woocommerce_add_cart_item_data', 'add_custom_price_to_cart_item_data', 20, 2 );
function add_custom_price_to_cart_item_data( $cart_item_data, $product_id ){
    if( ! isset($_POST['custom_price']) )
        return $cart_item_data;

    $cart_item_data['custom_price'] = (float) sanitize_text_field( $_POST['custom_price'] );
    $cart_item_data['unique_key'] = md5( microtime() . rand() ); // Make each item unique

    return $cart_item_data;
	
}

// Set conditionally a custom item price
// add_action('woocommerce_before_calculate_totals', 'set_cutom_cart_item_price', 20, 1);
// function set_cutom_cart_item_price( $cart ) {
//     if ( is_admin() && ! defined( 'DOING_AJAX' ) )
//         return;

//     if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
//         return;

//     foreach (  $cart->get_cart() as $cart_item ) {
//         if ( isset( $cart_item['custom_price'] ) ){
// 			$cart_item['data']->set_price( $cart_item['custom_price'] );
// 		}
//     }
	
// }


//Execute shortcodes and other css for stock stickers product category page
add_action("flatsome_after_product_page", "code_for_stockstickers_product", 20, 1);
function code_for_stockstickers_product() {
	
	if( has_term( "Stock Stickers", 'product_cat' ) ) {
		echo do_shortcode( '[sc name="stock_sticker_product_layout"][/sc]' );
	}
	
}

function custom_empty_cart() {
    global $woocommerce;

    if ( isset( $_GET['empty-cart'] ) ) {
        $woocommerce->cart->empty_cart();
    }
}
add_action( 'init', 'custom_empty_cart' );






//---------------------------------------------REORDER------------------------------------------------------//
//---------------------------------------------REORDER------------------------------------------------------//
//---------------------------------------------REORDER------------------------------------------------------//
//---------------------------------------------REORDER------------------------------------------------------//
//---------------------------------------------REORDER------------------------------------------------------//
//---------------------------------------------REORDER------------------------------------------------------//
//---------------------------------------------REORDER------------------------------------------------------//
//---------------------------------------------REORDER------------------------------------------------------//
//---------------------------------------------REORDER------------------------------------------------------//
//---------------------------------------------REORDER------------------------------------------------------//
//---------------------------------------------REORDER------------------------------------------------------//
//---------------------------------------------REORDER------------------------------------------------------//
//---------------------------------------------REORDER------------------------------------------------------//
//---REORGANIZATION SECTION STARTS---

//---REORGANIZATION SECTION ENDS---

// Deactivate the original WooCommerce hook
// remove_action( 'woocommerce_cart_loaded_from_session', 'wc_load_order_again_data' );

function find_closest_lower_quantity($quantity, $discounts) {
    // Sort the discount array by key in ascending order
    if (is_array($array)) {
        ksort($array);
    }
    
    // Initialize the discount variable
    $discount = 0;

    // Iterate through each discount quantity
    foreach($discounts as $qty => $disc) {
        // If the current discount quantity is greater than the input quantity, return the last found discount
        if($qty > $quantity) {
            break;
        }
        // Otherwise, update the discount
        $discount = $disc;
    }
    
    return $discount;
}


function get_all_meta_values($data) {
    $meta_values = array();

    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $meta_values = array_merge($meta_values, get_all_meta_values($value));
        } else {
            if (in_array($key, array('Width', 'Height', 'Quantity', 'Custom Price'))) {
                $numeric_value = preg_replace('/[^0-9.]/', '', $value);
                $meta_values[$key] = $numeric_value !== '' ? (float)$numeric_value : 0;
            }
        }
    }

    return $meta_values;
}

function calculate_order_again_price($custom_meta){
    // Prices and discounts data
    /* Note */
    //Change prices here or if you want to include another product just add an array like this here
    $labelsMaterialPrices = array(
        'Vinyl' => 0.084,
        'Matte' => 0.086,
        'Holographic' => 0.123,
        'Clear' => 0.086,
        'Heavy Duty' => 0.112,
        'Reflective' => 0.232,
        'Prismatic' => 0.158,
        'Glitter' => 0.158
    );

    $stickersMaterialPrices = array(
        'Vinyl Stickers' => 0.108,
        'Matte' => 0.108,
        'Holographic' => 0.139,
        'Clear' => 0.128,
        'Heavy Duty' => 0.175,
        'Reflective' => 0.175,
        'Prismatic' => 0.142,
        'Glitter' => 0.142
    );
    
    /* Note */
    //If you have different discount for a different product then just copy this discount array, rename variable and use it in product's very own respective condition

    $discounts = array(
        1 => array('10' => 0, '50' => 0, '100' => 0, '200' => 0, '300' => 0, '500' => 0, '800' => 0, '1000' => 26, '1500' => 27, '2000' => 47, '5000' => 60),
        2 => array('10' => 0, '50' => 0, '100' => 0, '200' => 27, '300' => 27, '500' => 48, '800' => 55, '1000' => 55, '1500' => 62, '2000' => 69, '5000' => 75),
        3 => array('10' => 0, '50' => 0, '100' => 27, '200' => 48, '300' => 48, '500' => 55, '800' => 62, '1000' => 68, '1500' => 69, '2000' => 73, '5000' => 76),
        4 => array('10' => 0, '50' => 26, '100' => 53, '200' => 55, '300' => 62, '500' => 69, '800' => 69, '1000' => 74, '1500' => 74, '2000' => 77, '5000' => 81),
        5 => array('10' => 0, '50' => 25, '100' => 45, '200' => 59, '300' => 60, '500' => 67, '800' => 72, '1000' => 73, '1500' => 76, '2000' => 78, '5000' => 79),
        6 => array('10' => 0, '50' => 46, '100' => 53, '200' => 59, '300' => 67, '500' => 72, '800' => 72, '1000' => 76, '1500' => 78, '2000' => 78, '5000' => 81),
        7 => array('10' => 0, '50' => 44, '100' => 58, '200' => 67, '300' => 67, '500' => 72, '800' => 75, '1000' => 78, '1500' => 78, '2000' => 79, '5000' => 80),
        8 => array('10' => 0, '50' => 53, '100' => 59, '200' => 66, '300' => 72, '500' => 75, '800' => 78, '1000' => 78, '1500' => 79, '2000' => 79, '5000' => 80),
    );

    
    $product_name = $custom_meta['order_item_name_'];
    $orderItemTotal = $custom_meta['order_item_total_'];
    $quantity = $custom_meta['Quantity'];
    $width = $custom_meta['Width'];
    $height = $custom_meta['Height'];
    $material = $custom_meta['Material'];
    $custom_price = $custom_meta['Custom Price'];
    
    
    
    // echo "custom_price ".$custom_price;
    $calculatedVals = $orderItemTotal;
    $widthClosestDiscount = intval(find_closest_lower_quantity(intval($quantity), $discounts[intval($width)]));
    $heightClosestDiscount = intval(find_closest_lower_quantity(intval($quantity), $discounts[intval($height)]));
    // echo "widthClosestDiscount ".$widthClosestDiscount;
    // echo "heightClosestDiscount ".$heightClosestDiscount;
    // echo "this is fuck ".intval($widthClosestDiscount > $heightClosestDiscount ? $widthClosestDiscount : $heightClosestDiscount );
    if ($product_name == "Print Custom Labels") {
        $calculatedVals = floatval($width) * floatval($height) * intval($quantity) * $labelsMaterialPrices[$material] * (((100 - intval($widthClosestDiscount > $heightClosestDiscount ? $widthClosestDiscount : $heightClosestDiscount )) / 100));
    } elseif ($product_name == "Print Custom Stickers") {
        $calculatedVals = floatval($width) * floatval($height) * intval($quantity) * $stickersMaterialPrices[$material] * (((100 - intval($widthClosestDiscount > $heightClosestDiscount ? $widthClosestDiscount : $heightClosestDiscount)) / 100));
    }
    
    
        $logger = wc_get_logger();
        $context = array( 'source' => 'rabi_logs' );
        $logger->error( "----------------------------------------" , $context );
        $logger->error( print_r($product_name, true) , $context );
        $logger->error( print_r($orderItemTotal, true) , $context );
        $logger->error( print_r($quantity, true) , $context );
        $logger->error( print_r($width, true) , $context );
        $logger->error( print_r($height, true) , $context );
        $logger->error( print_r($material, true) , $context );
        $logger->error( print_r($widthClosestDiscount, true) , $context );
        $logger->error( print_r($heightClosestDiscount, true) , $context );
        $logger->error( print_r($calculatedVals, true) , $context );
        $logger->error( print_r($labelsMaterialPrices[$material], true) , $context );
        $logger->error( print_r($stickersMaterialPrices[$material], true) , $context );
        $logger->error( "----------------------------------------" , $context );
    

    return $calculatedVals;
    
}




// Integrate your own hook
add_action( 'woocommerce_cart_loaded_from_session', 'customized_wc_load_order_again_data' );
function customized_wc_load_order_again_data() {
    global $woocommerce;
    if ( ! empty( $_GET['order_again'] ) && is_user_logged_in() && ( $order = wc_get_order( $_GET['order_again'] ) ) && $order->get_id() == $_GET['order_again'] ) {
        // Empty the current cart
        $woocommerce->cart->empty_cart();
        // Populate the cart with all products from the previous order
        foreach ( $order->get_items() as $item ) {
            if ( ( $product = $item->get_product() ) && $product->exists() ) {
                $productName = $product->get_name(); // Here is where you get the product name.
                $itemTotal = $item->get_total(); // get the item total
                $quantity = $item->get_quantity();
                // Load all product attributes
                $variation = array();
                $custom_meta = array();
                foreach ( $item->get_meta_data() as $meta ) {
                    $key = $meta->key;
                    if ( taxonomy_is_product_attribute( $key ) ) {
                        $variation[$key] = $item->get_meta( $key, true );
                    }
                    $custom_meta[$key] = $item->get_meta( $key, true );
                }
                $custom_meta["order_item_total_"] = $itemTotal;
                $custom_meta["order_item_name_"] = $productName;
                $custom_meta["Material"] = $item->get_meta('Material');
                $custom_meta["Custom Price"] = $item->get_meta('Custom Price');
                $calculatedPrice = calculate_order_again_price($custom_meta);
                $custom_meta["_order_again_price"] = $calculatedPrice;
                // $logger = wc_get_logger();
                // $context = array( 'source' => 'rabi_logs' );
                // $logger->error( "----------------------------------------" , $context );
                // $logger->error( print_r($custom_meta, true) , $context );
                // $logger->error( "----------------------------------------" , $context );
                // Add the product to the cart with the new price and custom meta data
                $woocommerce->cart->add_to_cart( $product->get_id(), $quantity, $product instanceof WC_Product_Variation ? $product->get_parent_id() : 0, $variation, array('_order_again_price' => $calculatedPrice, 'custom_meta' => $custom_meta) );
            }
        }
    }
}

add_action('woocommerce_admin_order_item_values', 'populate_wapf_fields', 10, 3);
function populate_wapf_fields($product, $item, $item_id) {
    // Retrieve the saved meta data
    $custom_meta = wc_get_order_item_meta($item_id, 'custom_meta', true);
    $logger = wc_get_logger();
    $context = array( 'source' => 'rabi_logs' );
    $logger->error( print_r($custom_meta, true) , $context );
    // Check if the custom_meta was saved and if the WAPF fields should be populated
    if ($custom_meta && isset($custom_meta['wapf_order_again']) && $custom_meta['wapf_order_again'] == 1) {
        $logger = wc_get_logger();
        $context = array( 'source' => 'rabi_logs' );
        $logger->error( "----------------------------------------" , $context );
        $logger->error( print_r($custom_meta, true) , $context );
    }
}



add_action( 'woocommerce_after_cart_item_name', 'display_customized_item_data', 10, 2 );
function display_customized_item_data( $cart_item, $cart_item_key ) {
    $fields_to_display = array('cutline', 'width', 'height', 'material', 'quantity', 'custom price');
    // Display only specified custom meta data
    foreach ( $cart_item["custom_meta"] as $key => $value ) {
        if ( in_array( strtolower($key), $fields_to_display ) && ! empty( $value ) ) {
            if (strtolower($key) == 'quantity' && $value == $cart_item['quantity']) {
                continue; // If quantity field matches the product quantity, skip it.
            }
        }
    }
}

add_filter( 'woocommerce_add_cart_item_data', 'add_custom_data_to_cart_item', 10, 2 );
function add_custom_data_to_cart_item( $cart_item_data, $product_id ) {
    $unique_cart_item_key = md5( microtime().rand() );
    $cart_item_data['unique_key'] = $unique_cart_item_key;
    return $cart_item_data;
}

add_action( 'woocommerce_after_cart_item_name', 'display_custom_item_data', 10, 2 );
function display_custom_item_data( $cart_item, $cart_item_key ) {
    $fields_to_display = array('cutline', 'width', 'height', 'material', 'quantity', 'custom price', 'image'); // specify keys here

    // Check if the cart item has the meta data
    if( isset( $cart_item['custom_meta'] ) ) {
        // Display only specified custom meta data
        foreach ( $cart_item['custom_meta'] as $key => $value ) {
            echo "<p hidden>". $key ."</p>";
            echo "<p hidden>". $value ."</p>";
            if ( in_array( strtolower($key), $fields_to_display ) && ! empty( $value ) ) {
                if (strtolower($key) == 'quantity' && $value == $cart_item['quantity']) {
                    continue; // skip this quantity field if it matches the product quantity (i.e., the actual quantity of the item in the cart)
                }
                if($key === "Custom Price"){
                    continue;
                }
                if($key === "Image"){
                    echo '<div class="product-' . esc_attr( $key ) . '">' . ucfirst(esc_html( $key )) . ': ' . ( "<a href='".$value."'> Click here to see </a>" ) . '</div>';
                } else {
                    echo '<div class="product-' . esc_attr( $key ) . '">' . ucfirst(esc_html( $key )) . ': ' . esc_html( $value ) . '</div>';
                }
                
            }
        }
    }
}


// Use the 'woocommerce_before_calculate_totals' hook to adjust the price
add_action('woocommerce_before_calculate_totals', 'calculate_custom_price_for_the_order_again', 10);
function calculate_custom_price_for_the_order_again($cart_object) {
    
    // echo "<pre>";
    // print_r($cart_object);
    // echo "</pre>";   
    if ( ! $cart_object instanceof WC_Cart ) {
        return;
    }
    //  echo "<pre>";
    // print_r($cart_object);
    // echo "</pre>";   
  

    foreach ($cart_object->get_cart() as $cart_item_key => $value) {
        if ( isset( $value['custom_meta']['_order_again_price'] ) ) {
            $value['data']->set_price( floatval( $value['custom_meta']['_order_again_price'] ) );
        }
    }
}


// //Use custom Image on cart

add_filter('woocommerce_cart_item_thumbnail', 'custom_cart_item_thumbnail', 10, 3);
function custom_cart_item_thumbnail($thumbnail, $cart_item, $cart_item_key) {
    if (isset($cart_item['custom_meta']['Image'])) {
        $image_url = $cart_item['custom_meta']['Image'];
        return '<img src="' . esc_url($image_url) . '" alt="">';
    } else {
        return $thumbnail;
    }
}

//Use custom image on checkout page as well
add_filter('woocommerce_checkout_cart_item_quantity', 'custom_checkout_cart_item_thumbnail', 10, 3);
function custom_checkout_cart_item_thumbnail($quantity_html, $cart_item, $cart_item_key) {
    if (isset($cart_item['custom_meta']['Image'])) {
        $image_url = $cart_item['custom_meta']['Image'];
        $image_html = '<img src="' . esc_url($image_url) . '" alt="" class="checkout-product-thumbnail">';
        return $image_html . $quantity_html;
    } else {
        return $quantity_html;
    }
}

