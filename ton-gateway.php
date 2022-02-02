<?php
/*
 * Plugin Name: Платёжный шлюз TON
 * Plugin URI: https://github.com/mir-one/ton-gateway
 * Description: Принимайте оплату с помощью TON у себя на сайте!
 * Author: Roman Inozemtsev
 * Author URI: https://t.me/inozemtsev_roman
 * Version: 0.0.1
 *
/*
 * Фильтр-хук регистрации PHP-класса в качестве платёжного шлюза WooCommerce
 */
add_filter( 'woocommerce_payment_gateways', 'ton_register_gateway_class' );
 
function ton_register_gateway_class( $gateways ) {
	$gateways[] = 'WC_TON_Gateway'; // название класса TON, добавляем в общий массив
	return $gateways;
}