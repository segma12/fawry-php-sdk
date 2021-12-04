
# Fawry PHP Plugin

A PHP plugin for Fawry payment gateway.




## Features

- Create and manage card tokens 
- Create a payment with cards with 3D secure
- Create a payment with card tokens with 3D secure
- Refund a payment
- Cancel an unpaid payment




## Installation

Install with `Composer`

```bash
  composer require segma/fawry-php-sdk
```



    
## Usage

### Init new instance

First thing you have to create a new instance from Fawry class.

If you are you are using the staging server you have to set isStaging flag with true to call the staging base URL. \
When you move to live you can set it false or ignore it.

```php 
    <?php
    
    $isStaging = true;
    $fawry = new Fawry($merchant_code, $merchant_key, $isStaging);
```

### Create a card token:

```php
    $card_token = $fawry->createCardToken($customer_id, $customer_mobile, $customer_email, $card_number, $exp_year, $exp_month, $cvv);
```

### Get customer list tokens:

```php
    $card_tokens = $fawry->listCustomerTokens($customerID');
```

### Delete customer card token:

```php
    $deleteCardToken = $fawry->deleteCardToken($customer_id, $card_token);
```

### Create a card payment

```php
    $payment = $fawry->payByCard($merchant_ref , $card_number, $exp_year, $exp_month, $cvv, $customer_id, $customer_name ,$customer_mobile, $customer_email, $amount, $chargeItems);
```

### Create a 3D secure card payment

```php
    $payment = $fawry->payByCard3DS($merchant_ref, $card_number, $exp_year, $exp_month, $cvv, $customer_id, $customer_name, $customer_mobile, $customer_email, $amount, $calbackURL, $chargeItems);
```

### Create a card token payment

```php
    $payment = $fawry->payByCardToken($merchant_ref, $card_token, $customer_id, $customer_mobile, $customer_email, $amount, 'EGP', $chargeItems);
```

### Create a card token payment with 3D secure

```php
    $payment = $fawry->payByCardToken3DS($merchant_ref, $card_token, $cvv, $customer_id, $customer_name, $customer_mobile, $customer_email, $amount, $calbackURL, $chargeItems);
```

### Refund payment

```php
    $refund = $fawry->refund($merchant_ref, $amount, $reason);
```

### Cancel an unpaid payment

```php
    $orderCancelation = $fawry->cancelUnpaidPayment($merchant_ref);
```




## License

Fawry PHP is an open-sourced php package licensed under the MIT license.

