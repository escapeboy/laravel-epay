## Epay API Payment Wrapper Laravel 5.* Package

## Installation
```json
"escapeboy/epay": "1.*"
```

### In config/app.php
```php
Escapeboy\Epay\EpayServiceProvider::class,
```
```php
'Epay' => Escapeboy\Epay\EpayServiceProvider::class,
```

## Publish config file
```bash
php artisan vendor:publish --provider="Escapeboy\Epay\EpayServiceProvider" --tag="config" 
```
--

## Usage

Edit config/epay.php
```php
return [
	'submit_url' => 'https://devep2.datamax.bg/ep2/epay2_demo/', // test submit url
	// 'submit_url' => 'https://www.epay.bg/', // production submit url
	'secret' => REQUIRED, // client secret
	'client_id' => REQUIRED, // client id
	'expire_days' => 1 // expire time for transations in days
	'success_url' => 'epay/success', // return url for success
	'cancel_url' => 'epay/cancel', // return url for cancel
];
```

Generate hidden input fields to submit to Epay
```php
echo \Epay::generateInputFields([
				'invoice' => '000001', // invoice ID
				'amount' => 100, // amount
				'descr' => 'Some info about order' // info about order
]);
```

Receive epay notification (url is filled in merchant's profile)
```php
Route::post('epay/notification', function(){

	$notification_data = \Epay::receiveNotification(request()->all());
		/**
        * $notification_data contains array with data:
        *
        *    array (
        *      'invoice' => 'your_order_id',
        *      'status' => 'PAID',
        *      'pay_date' => '20162304154530',
        *      'stan' => '045138',
        *      'bcode' => '045138'
        *    ),
        *
        **/
	return response()->make($notification_data['response']);
});
```
