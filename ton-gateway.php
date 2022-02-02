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

/*
 * Класс внутри хука plugins_loaded
 */
add_action( 'plugins_loaded', 'ton_gateway_class' );
function ton_gateway_class() {
 
	class WC_TON_Gateway extends WC_Payment_Gateway {
 
/**
 * Конструктор класса
 */
    public function __construct() {
 
    $this->id = 'ton';
    $this->icon = 'https://ton.org/download/ton_symbol.svg'; // URL иконки TON с сайта ton.org
    $this->has_fields = false; // Для доп полей
    $this->method_title = 'Платёжный шлюз TON';
    $this->method_description = 'Платёжный шлюз TON'; // будет отображаться в админке
    $this->supports = array(
    'products'
    );
 
    // поля настроек
    $this->init_form_fields();

    // инициализируем настройки
    $this->init_settings();

    // название шлюза
    $this->title = $this->get_option( 'title' );
    
    // описание
    $this->description = $this->get_option( 'description' );
    
    // включен или выключен
    $this->enabled = $this->get_option( 'enabled' );
   
    // работает в тестовом режиме (sandbox) или нет
    $this->testmode = 'yes' === $this->get_option( 'testmode' );
   
    // ключи для тестового и рабочего режима шлюза
    $this->private_key = $this->testmode ? $this->get_option( 'test_private_key' ) : $this->get_option( 'private_key' );
    $this->publishable_key = $this->testmode ? $this->get_option( 'test_publishable_key' ) : $this->get_option( 'publishable_key' );
    
    // Хук сохранения всех настроек
    add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
   
    // Для генерирации токена из данных кошелька TON, нужно подключить JS
    add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
    
    // add_action( 'woocommerce_api_{webhook name}', array( $this, 'ton_webhook' ) );
}
 
/**
* Опции шлюза  TON
*/
    public function init_form_fields(){
 
    $this->form_fields = array(
        'enabled' => array(
            'title'       => 'Включен/Выключен',
            'label'       => 'Включить оплату с помощью TON',
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'no'
        ),
        'title' => array(
            'title'       => 'Заголовок',
            'type'        => 'text',
            'description' => 'Это то, что пользователь увидит как название метода оплаты на странице оформления заказа.',
            'default'     => 'Оплатить с помощью TON',
            'desc_tip'    => true,
        ),
        'description' => array(
            'title'       => 'Описание',
            'type'        => 'textarea',
            'description' => 'Описание этого метода оплаты, которое будет отображаться пользователю на странице оформления заказа.',
            'default'     => 'Оплатите c помощью TON легко и быстро.',
        ),
        'testmode' => array(
            'title'       => 'Тестовый режим',
            'label'       => 'Включить тестовый режим',
            'type'        => 'checkbox',
            'description' => 'Хотите сначала протестировать с тестовыми ключами API?',
            'default'     => 'yes',
            'desc_tip'    => true,
        ),
        'test_publishable_key' => array(
            'title'       => 'Тестовый публичный ключ',
            'type'        => 'text'
        ),
        'test_private_key' => array(
            'title'       => 'Тестовый приватный ключ',
            'type'        => 'password',
        ),
        'publishable_key' => array(
            'title'       => 'Публичный ключ',
            'type'        => 'text'
        ),
        'private_key' => array(
            'title'       => 'Приватный ключ',
            'type'        => 'password'
        )
            );
 
	 	}
 
/**
 * Метод добавления формы ввода данных на сайт
 */
// public function payment_fields() {
// ...
// }
 
/*
 * Формы ввода данных и создания токена для них
 */
    public function payment_scripts() {
 
    // выходим, если находимся не на странице оформления заказа
	if ( ! is_cart() && ! is_checkout() && ! isset( $_GET['pay_for_order'] ) ) {
		return;
	}
 
	// плагин отключен? ничего не делаем
	if ( 'no' === $this->enabled ) {
		return;
	}
 
	// нет смысла подключать JS, если плагин не настроен, не указаны API ключи
	if ( empty( $this->private_key ) || empty( $this->publishable_key ) ) {
		return;
	}
 
	// проверяем ssl, если плагин работает не в тестовом режиме
	if ( ! $this->testmode && ! is_ssl() ) {
		return;
	}
 
	// JS TON для обработки данных кошелька
	wp_enqueue_script( 'ton_js', 'token.js' );
 
	// произвольный JavaScript, дополняющий token.js
	wp_register_script( 'woocommerce_ton', plugins_url( 'ton.js', __FILE__ ), array( 'jquery', 'ton_js' ) );
 
	// Если в JavaScript понадобится публичный API ключ, передаём его
	wp_localize_script( 'woocommerce_ton', 'ton_params', array(
		'publishableKey' => $this->publishable_key
	) );
 
	wp_enqueue_script( 'woocommerce_ton' );
 
	 	}
 
		/*
 * Валидация полей
 */
// public function validate_fields() {
 
// ...
 
// }
 
/*
* Обрабаботка платёжа
 */
    public function process_payment( $order_id ) {
 
	// объект заказа
	$order = wc_get_order( $order_id );
 
 
	/*
 	 * Массив с параметрами для взаимодействия с API
	 */
	// $args = array(
 
		// ...
 
	// );
 
	/*
	 * Взаимодействие с API при помощи функции wp_remote_post()
 	 */
	$response = wp_remote_post( '{payment processor endpoint}', $args );
 
	// если нет ошибки при подключении
	if( ! is_wp_error( $response ) ) {
	$body = json_decode( $response['body'], true );
 
	// коды ответа
	if ( $body['response']['responseCode'] == 'APPROVED' ) {
 
	// получили платёж
	$order->payment_complete();
 
	// заметки для пользователя
	// изменить второй параметр на false, чтобы заметка была для админа
	$order->add_order_note( 'Заказ оплачен, спасибо!', true );
 
	// очищаем корзину
	WC()->cart->empty_cart();
 
	// редиректим на страницу спасибо
	return array(
		'result' => 'success',
		'redirect' => $this->get_return_url( $order )
	);
 
	} else {
		wc_add_notice(  'Попробуйте ещё раз, оплата не прошла.', 'error' );
		return;
	}
 
	} else {
		wc_add_notice(  'Ошибка с подключением к API.', 'error' );
		return;
	}
 
}
 
/*
 * Хук, к которому будет обращаться банк
 */
	public function ton_webhook() {
 
        $order = wc_get_order( $_GET[ 'id' ] ); // ID заказа приходят из кошелька
        $order->payment_complete(); // платёж совершён
        $order->reduce_order_stock(); // уменьшаем количество товаров в запасах
         
        update_option( 'webhook_debug', $_GET );
 
	 	}
 	}
}