# Платёжный шлюз TON💎
Принимайте оплату с помощью TON на сайте.

![screen](screen.png)

1. Добавьте TON в качестве валюты
Поместите код в functions.php

```php
add_filter( 'woocommerce_currencies', 'add_my_currency' );

function add_my_currency( $currencies ) {
     $currencies['TON'] = __( 'The Open Network', 'woocommerce' );
     return $currencies;
}

add_filter('woocommerce_currency_symbol', 'add_my_currency_symbol', 10, 2);

function add_my_currency_symbol( $currency_symbol, $currency ) {
     switch( $currency ) {
          case 'TON': $currency_symbol = '💎'; break;
     }
     return $currency_symbol;
}
```

---
💸Пожертвование на разработку платежного шлюза

[Оплатить с помощью @CryptoBot](http://t.me/CryptoBot?start=IVrdwXr7sfOl)
