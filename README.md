# SMSOnlineGH Laravel Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/turndale/smsonline.svg?style=flat-square)](https://packagist.org/packages/turndale/smsonline)
[![Total Downloads](https://img.shields.io/packagist/dt/turndale/smsonline.svg?style=flat-square)](https://packagist.org/packages/turndale/smsonline)
[![License](https://img.shields.io/packagist/l/turndale/smsonline.svg?style=flat-square)](https://packagist.org/packages/turndale/smsonline)

A comprehensive Laravel package for integrating with the [SMSOnlineGH](https://smsonlinegh.com/) API. This package provides a fluent interface for sending SMS, scheduling messages, checking account balance, and managing scheduled campaigns.

## Features

- **Fluent Interface:** Easy-to-use chainable methods for constructing messages.
- **Helper Function:** specific `sms()` helper for quick access.
- **Scheduling:** Native support for scheduling messages with timezone offsets.
- **Account Management:** Check balance and cancel scheduled batches easily.
- **Laravel Support:** Compatible with Laravel 11.x and 12.x.
- **PHP 8.1+ Support**

## Installation

You can install the package via composer:

```bash
composer require turndale/smsonline
```

### Configuration

Publish the configuration file to your application:

```bash
php artisan vendor:publish --provider="Turndale\SmsOnline\SmsOnlineServiceProvider" --tag="config"
```

This will create a `config/smsonline.php` file. You should then add your SMSOnlineGH credentials to your `.env` file:

```env
SMSONLINE_API_KEY=your_api_key_here
SMSONLINE_DEFAULT_SENDER=YourSenderID
```

## Usage

### Sending a Basic SMS

You can use the `sms()` helper function or the Facade to send messages.

```php
use Turndale\SmsOnline\Facades\SmsOnline;

// Using the Helper
$response = sms('MyCompany')
    ->message('Hello from Helper!')
    ->destinations(['0244123456'])
    ->send();

// Using the Facade
$response = SmsOnline::sender('MyCompany')
    ->message('Hello from Facade!')
    ->destinations(['0244123456'])
    ->send();

if ($response->successful()) {
    echo "Message sent successfully!";
} else {
    echo "Failed to send message: " . $response->body();
}
```

### Sending SMS with Default Sender

If you've set the `SMSONLINE_DEFAULT_SENDER` environment variable in your `.env` file, you don't need to explicitly pass the sender when sending messages. This makes your code cleaner and allows you to manage the sender globally.

```env
SMSONLINE_DEFAULT_SENDER=MyCompany
```

Then you can send messages without specifying a sender:

```php
use Turndale\SmsOnline\Facades\SmsOnline;

// Using the Helper (no sender parameter needed)
$response = sms()
    ->message('Hello using default sender!')
    ->destinations(['0244000000'])
    ->send();

// Using the Facade (no sender() method needed)
$response = SmsOnline::message('Hello using default sender!')
    ->destinations(['0244000000'])
    ->send();

if ($response->successful()) {
    echo "Message sent successfully!";
}
```

### Method Chaining

The package is designed to be fluent:

```php
// Helper
sms()
    ->sender('MyBrand')
    ->message('Code: 1234')
    ->destinations(['0571234567'])
    ->send();

// Facade
SmsOnline::sender('MyBrand')
    ->message('Code: 1234')
    ->destinations(['0571234567'])
    ->send();
```

### Scheduling Messages

To schedule a message for later delivery, use the `schedule()` method. Pass the date/time string (YYYY-MM-DD HH:MM) and optionally a timezone offset.

```php
// Helper
sms('EventOrg')
    ->message('Event starts in 1 hour.')
    ->destinations(['0244123456'])
    ->schedule('2026-12-25 08:00', '+00:00')
    ->send();

// Facade
SmsOnline::sender('EventOrg')
    ->message('Event starts in 1 hour.')
    ->destinations(['0244123456'])
    ->schedule('2026-12-25 08:00', '+00:00')
    ->send();
```

### Checking Account Balance

Retrieve your current account balance.

```php
// Helper
$response = sms()->balance();

// Facade
$response = SmsOnline::balance();

$data = $response->json();
$balance = $data['data']['balance'] ?? 0;
echo "Current Balance: " . $balance;
```

### Cancelling a Scheduled Message

If you need to cancel a scheduled batch, use the `cancelScheduled` method with the Batch ID returned from the sending response.

```php
// $batchId = 'cfa19ba67f94fbd6b19c067b0c87ed4f'; // ID from the send response

// Helper
$response = sms()->cancelScheduled($batchId);

// Facade
$response = SmsOnline::cancelScheduled($batchId);

if ($response->successful()) {
    echo "Scheduled message cancelled.";
}
```

## Testing

You can run the tests with:

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email me@stephenasare.dev instead of using the issue tracker.

## Credits

- [Stephen Asare](https://github.com/stephenasaredev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
