# Платёжный шлюз TON💎
Принимайте оплату с помощью TON на сайте.

![screen](screen.png)

# Сниппет TON
Поместите код в functions.php

```
add_filter( 'woocommerce_currencies', 'add_my_currency' );

function add_my_currency( $currencies ) {
     $currencies['TON'] = __( 'The Open Network', 'woocommerce' );
     return $currencies;
}

add_filter('woocommerce_currency_symbol', 'add_my_currency_symbol', 10, 2);

function add_my_currency_symbol( $currency_symbol, $currency ) {
     switch( $currency ) {
          case 'TON': $currency_symbol = 'TON'; break;
     }
     return $currency_symbol;
}
```

💸Donation
<br>
![qr](qr.png)
<br>
[EQBondcvD2_aOFADXSWJHs4ZazQDuEl9_wNvGGPxI8hGuOFU](ton://transfer/EQBondcvD2_aOFADXSWJHs4ZazQDuEl9_wNvGGPxI8hGuOFU)
