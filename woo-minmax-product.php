<?php

/**
 * Plugin Name: Woo Min/Max Quantity Product
 * Description: Establecer cantidades mínimas y máximas de productos simples y variables en WooCommerce.
 * Version: 1.0
 * Author: Juan Carlos Rodríguez Roman (a.k.a ju4ncaa)
 * Author URI: https://github.com/ju4ncaa
 * Requires Plugins: woocommerce
 */



if (!defined('ABSPATH'))

    exit;



/** ===============================
 *  CAMPOS EN PRODUCTOS SIMPLES
 *  =============================== */

add_action('woocommerce_product_options_inventory_product_data', function () {

    global $product_object;



    if ($product_object->is_type('simple')) {

        woocommerce_wp_text_input([

            'id' => '_min_qty',

            'label' => 'Cantidad mínima',

            'type' => 'number',

            'desc_tip' => true,

            'description' => 'Cantidad mínima permitida para este producto.',

            'custom_attributes' => ['min' => '1']

        ]);

        woocommerce_wp_text_input([

            'id' => '_max_qty',

            'label' => 'Cantidad máxima',

            'type' => 'number',

            'desc_tip' => true,

            'description' => 'Cantidad máxima permitida para este producto.',

            'custom_attributes' => ['min' => '1']

        ]);

    }

});



add_action('woocommerce_process_product_meta', function ($post_id) {

    if (isset($_POST['_min_qty'])) {

        update_post_meta($post_id, '_min_qty', sanitize_text_field($_POST['_min_qty']));

    }

    if (isset($_POST['_max_qty'])) {

        update_post_meta($post_id, '_max_qty', sanitize_text_field($_POST['_max_qty']));

    }

});



/** ===============================
 *  CAMPOS EN VARIACIONES
 *  =============================== */

add_action('woocommerce_variation_options_pricing', function ($loop, $variation_data, $variation) {

    woocommerce_wp_text_input([

        'id' => "_min_qty[$loop]",

        'label' => 'Cantidad mínima',

        'type' => 'number',

        'value' => get_post_meta($variation->ID, '_min_qty', true),

        'custom_attributes' => ['min' => '1']

    ]);

    woocommerce_wp_text_input([

        'id' => "_max_qty[$loop]",

        'label' => 'Cantidad máxima',

        'type' => 'number',

        'value' => get_post_meta($variation->ID, '_max_qty', true),

        'custom_attributes' => ['min' => '1']

    ]);

}, 10, 3);



add_action('woocommerce_save_product_variation', function ($variation_id, $i) {

    if (isset($_POST['_min_qty'][$i])) {

        update_post_meta($variation_id, '_min_qty', sanitize_text_field($_POST['_min_qty'][$i]));

    }

    if (isset($_POST['_max_qty'][$i])) {

        update_post_meta($variation_id, '_max_qty', sanitize_text_field($_POST['_max_qty'][$i]));

    }

}, 10, 2);



/** ===============================
 *  VALIDACIÓN AL AÑADIR AL CARRITO
 *  =============================== */

add_filter('woocommerce_add_to_cart_validation', function ($passed, $product_id, $quantity, $variation_id = 0) {

    $target_id = $variation_id > 0 ? $variation_id : $product_id;

    $min = (int)get_post_meta($target_id, '_min_qty', true);

    $max = (int)get_post_meta($target_id, '_max_qty', true);



    $current_qty_in_cart = 0;

    foreach (WC()->cart->get_cart() as $cart_item) {

        if ($cart_item['product_id'] == $product_id) {

            if ($variation_id > 0) {

                if ($cart_item['variation_id'] == $variation_id) {

                    $current_qty_in_cart += $cart_item['quantity'];

                }

            }
            else {

                if ($cart_item['variation_id'] == 0) {

                    $current_qty_in_cart += $cart_item['quantity'];

                }

            }

        }

    }



    if ($min && ($quantity + $current_qty_in_cart) < $min) {

        wc_add_notice("La cantidad mínima para este producto es $min.", 'error');

        return false;

    }



    if ($max && ($quantity + $current_qty_in_cart) > $max) {

        wc_add_notice("No puedes añadir más de $max unidades de este producto (ya tienes $current_qty_in_cart en el carrito).", 'error');

        return false;

    }



    return $passed;

}, 10, 4);



/** ===============================
 *  VALIDACIÓN EN CARRITO
 *  =============================== */

add_action('woocommerce_check_cart_items', function () {

    foreach (WC()->cart->get_cart() as $cart_item) {

        $product = $cart_item['data'];

        $product_id = $product->get_id();

        $qty = $cart_item['quantity'];



        $min = (int)get_post_meta($product_id, '_min_qty', true);

        $max = (int)get_post_meta($product_id, '_max_qty', true);



        $name = $product->get_name();

        if ($min && $qty < $min) {

            wc_add_notice("$name: cantidad mínima permitida es $min unidades.", 'error');

        }

        if ($max && $qty > $max) {

            wc_add_notice("$name: cantidad máxima permitida es $max unidades.", 'error');

        }

    }

});



/** ===============================
 *  VALIDACIÓN EN CHECKOUT
 *  =============================== */

add_action('woocommerce_checkout_process', function () {

    foreach (WC()->cart->get_cart() as $cart_item) {

        $product = $cart_item['data'];

        $product_id = $product->get_id();

        $qty = $cart_item['quantity'];



        $min = (int)get_post_meta($product_id, '_min_qty', true);

        $max = (int)get_post_meta($product_id, '_max_qty', true);



        $name = $product->get_name();

        if ($min && $qty < $min) {

            wc_add_notice("$name: cantidad mínima permitida es $min unidades.", 'error');

        }

        if ($max && $qty > $max) {

            wc_add_notice("$name: cantidad máxima permitida es $max unidades.", 'error');

        }

    }

});



/** ===============================
 *  INPUT DE CANTIDAD PERSONALIZADO
 *  =============================== */

add_filter('woocommerce_quantity_input_args', function ($args, $product) {

    $product_id = $product->get_id();

    $min = (int)get_post_meta($product_id, '_min_qty', true);



    if ($min > 0) {

        $args['input_value'] = max($min, 1);

        $args['min_value'] = $min;

    }



    $max = (int)get_post_meta($product_id, '_max_qty', true);

    if ($max > 0) {

        $args['max_value'] = $max;

    }



    return $args;

}, 10, 2);



/** ===============================
 *  VARIACIONES: MIN/MAX DINÁMICOS
 *  =============================== */

add_filter('woocommerce_available_variation', function ($variation_data, $product, $variation) {

    $min = (int)get_post_meta($variation->get_id(), '_min_qty', true);

    if ($min > 0) {

        $variation_data['min_qty'] = $min;

        $variation_data['input_value'] = max($min, 1);

    }



    $max = (int)get_post_meta($variation->get_id(), '_max_qty', true);

    if ($max > 0) {

        $variation_data['max_qty'] = $max;

    }



    return $variation_data;

}, 10, 3);



/** ===============================
 * VALIDACIÓN AL ACTUALIZAR CANTIDAD EN CARRITO
 * =============================== */

add_filter('woocommerce_update_cart_validation', function ($passed, $cart_item_key, $values, $quantity) {

    $product_id = $values['product_id'];

    $variation_id = $values['variation_id'];

    $target_id = $variation_id > 0 ? $variation_id : $product_id;



    $min = (int)get_post_meta($target_id, '_min_qty', true);

    $max = (int)get_post_meta($target_id, '_max_qty', true);

    $product_name = $values['data']->get_name();



    if ($min && $quantity < $min) {

        wc_add_notice("La cantidad mínima para '$product_name' es $min unidades.", 'error');

        return false;

    }



    if ($max && $quantity > $max) {

        wc_add_notice("La cantidad máxima para '$product_name' es $max unidades.", 'error');

        return false;

    }



    return $passed;

}, 10, 4);



// Agrega enlace de "Información del Plugin" en la pantalla de plugins

add_filter('plugin_row_meta', function ($links, $file) {

    if ($file === plugin_basename(__FILE__)) {

        $links[] = '<a href="#" class="wmmq-info-link">Ver detalles</a>';

    }

    return $links;

}, 10, 2);



// Inyecta el modal también en la pantalla de plugins

add_action('admin_footer', function () {

    $screen = get_current_screen();

    if ($screen && $screen->base !== 'plugins')

        return;

?>

    <style>

        #wmmq-modal-overlay {

            display: none;

            position: fixed;

            top: 0;

            left: 0;

            width: 100%;

            height: 100%;

            background: rgba(0, 0, 0, 0.5);

            z-index: 1000;

        }



        #wmmq-modal {

            background: #fff;

            width: 600px;

            max-width: 90%;

            margin: 100px auto;

            padding: 20px;

            position: relative;

            border-radius: 8px;

            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);

        }



        #wmmq-modal h2 {

            margin-top: 0;

        }



        #wmmq-modal button.close {

            position: absolute;

            top: 10px;

            right: 10px;

            background: none;

            border: none;

            font-size: 20px;

            cursor: pointer;

        }

    </style>



    <div id="wmmq-modal-overlay">

        <div id="wmmq-modal">

            <button class="close" aria-label="Cerrar">&times;</button>


            <h2>Woo Min/Max Quantity Product</h2>

            <p><strong>Versión:</strong> 1.0</p>

            <p><strong>Autor:</strong> <a href="https://github.com/ju4ncaa" target="_blank">ju4ncaa</a></p>

            <p>Este plugin permite establecer límites de cantidad mínimos y máximos para productos simples y variables en WooCommerce. Incluye validaciones en producto, carrito y checkout, y actualiza dinámicamente los inputs según la variación.</p>

            <p>Si necesitas soporte o mejoras, contáctanos a través de nuestro sitio web.</p>

        </div>

    </div>



    <script>

        jQuery(function ($) {

            $('.wmmq-info-link').on('click', function (e) {

                e.preventDefault();

                $('#wmmq-modal-overlay').fadeIn();

            });



            $('#wmmq-modal-overlay .close').on('click', function () {

                $('#wmmq-modal-overlay').fadeOut();

            });



            $(document).on('click', function (e) {

                if ($(e.target).is('#wmmq-modal-overlay')) {

                    $('#wmmq-modal-overlay').fadeOut();

                }

            });

        });

    </script>



    /** ===============================

    * SCRIPT DINÁMICO PARA VARIACIONES

    * =============================== */

    add_action('wp_footer', function () {

    if (!is_product())

    return;

    ?>

    <script>

        jQuery(function ($) {

            $('form.variations_form').on('found_variation', function (event, variation) {

                const $form = $(this);

                const $qtyInput = $form.find('input.qty');



                const min = parseInt(variation.min_qty) || 1;

                const max = parseInt(variation.max_qty) || '';



                $qtyInput.attr('min', min);

                if (max) {

                    $qtyInput.attr('max', max);

                } else {

                    $qtyInput.removeAttr('max');

                }



                const currentVal = parseInt($qtyInput.val()) || 0;

                if (currentVal < min) {

                    $qtyInput.val(min).trigger('change');

                }

            });



            $('form.variations_form').on('reset_data', function () {

                const $qtyInput = $(this).find('input.qty');

                $qtyInput.removeAttr('min').removeAttr('max');

            });

        });

    </script>

    <?php

});