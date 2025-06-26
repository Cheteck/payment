<p align="center"><img src="resources/images/payment.png?raw=true"></p>



# Laravel Payment Gateway



[![Software License][ico-license]](LICENSE.md)
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads on Packagist][ico-download]][link-packagist]
[![StyleCI](https://github.styleci.io/repos/169948762/shield?branch=master)](https://github.styleci.io/repos/169948762)
[![Maintainability](https://api.codeclimate.com/v1/badges/e6a80b17298cb4fcb56d/maintainability)](https://codeclimate.com/github/shetabit/payment/maintainability)
[![Quality Score][ico-code-quality]][link-code-quality]

This is a Laravel Package for Payment Gateway Integration. This package supports `Laravel 5.8+`.

[Donate me](https://yekpay.me/mahdikhanzadi) if you like this package :sunglasses: :bowtie:

For PHP integration you can use [shetabit/multipay](https://github.com/shetabit/multipay) package.

> This packages works with multiple drivers, and you can create custom drivers if you can't find them in the [current drivers list](#list-of-available-drivers) (below list).

- [داکیومنت فارسی][link-fa]
- [English documents][link-en]
- [中文文档][link-zh]

# List of contents

- [Laravel Payment Gateway](#laravel-payment-gateway)
- [List of contents](#list-of-contents)
- [List of available drivers](#list-of-available-drivers)
  - [Install](#install)
  - [Configure](#configure)
  - [How to use](#how-to-use)
      - [Working with invoices](#working-with-invoices)
      - [Purchase invoice](#purchase-invoice)
      - [Pay invoice](#pay-invoice)
      - [Verify payment](#verify-payment)
      - [Useful methods](#useful-methods)
      - [Create custom drivers:](#create-custom-drivers)
      - [Events](#events)
  - [Change log](#change-log)
  - [Contributing](#contributing)
  - [Security](#security)
  - [Credits](#credits)
  - [License](#license)
- [Intégration des Paiements en Algérie](#intégration-des-paiements-en-algérie)

# List of available drivers

- [asanpardakht](https://asanpardakht.ir/) :heavy_check_mark:
- [aqayepardakht](https://aqayepardakht.ir/) :heavy_check_mark:
- [atipay](https://www.atipay.net/) :heavy_check_mark:
- [azkiVam (Installment payment)](https://www.azkivam.com/) :heavy_check_mark:
- [behpardakht (mellat)](http://www.behpardakht.com/) :heavy_check_mark:
- [bitpay](https://bitpay.ir/) :heavy_check_mark:
- [digipay](https://www.mydigipay.com/) :heavy_check_mark:
- [etebarino (Installment payment)](https://etebarino.com/) :heavy_check_mark:
- [fanavacard](https://www.fanava.com/) :heavy_check_mark:
- [idpay](https://idpay.ir/) :heavy_check_mark:
- [irankish](http://irankish.com/) :heavy_check_mark:
- [local](#local-driver) :heavy_check_mark:
- [jibit](https://jibit.ir/) :heavy_check_mark:
- [nextpay](https://nextpay.ir/) :heavy_check_mark:
- [omidpay](https://omidpayment.ir/) :heavy_check_mark:
- [parsian](https://www.pec.ir/) :heavy_check_mark:
- [pasargad](https://bpi.ir/) :heavy_check_mark:
- [payir](https://pay.ir/) :heavy_check_mark:
- [payfa](https://payfa.com/) :heavy_check_mark:
- [paypal](http://www.paypal.com/) (will be added soon in next version)
- [payping](https://www.payping.ir/) :heavy_check_mark:
- [paystar](http://paystar.ir/) :heavy_check_mark:
- [poolam](https://poolam.ir/) :heavy_check_mark:
- [rayanpay](https://rayanpay.com/) :heavy_check_mark:
- [sadad (melli)](https://sadadpsp.ir/) :heavy_check_mark:
- [saman](https://www.sep.ir) :heavy_check_mark:
- [sep (saman electronic payment) Keshavarzi & Saderat](https://www.sep.ir) :heavy_check_mark:
- [sepehr (saderat)](https://www.sepehrpay.com/) :heavy_check_mark:
- [sepordeh](https://sepordeh.com/) :heavy_check_mark:
- [sizpay](https://www.sizpay.ir/) :heavy_check_mark:
- [toman](https://tomanpay.net/) :heavy_check_mark:
- [vandar](https://vandar.io/) :heavy_check_mark:
- [walleta (Installment payment)](https://walleta.ir/) :heavy_check_mark:
- [yekpay](https://yekpay.com/) :heavy_check_mark:
- [zarinpal](https://www.zarinpal.com/) :heavy_check_mark:
- [zibal](https://www.zibal.ir/) :heavy_check_mark:
- Others are under way.

**Help me to add the gateways below by creating `pull requests`**

- stripe
- authorize
- 2checkout
- braintree
- skrill
- payU
- amazon payments
- wepay
- payoneer
- paysimple

> you can create your own custom drivers if it does not exist in the list, read the `Create custom drivers` section.

## Install

Via Composer

``` bash
$ composer require shetabit/payment
```

## Publish Vendor Files

- **publish configuration files:**
``` bash
php artisan vendor:publish --tag=payment-config
```

 - **publish views for customization:**
``` bash
php artisan vendor:publish --tag=payment-views
```

## Configure

If you are using `Laravel 5.5` or higher then you don't need to add the provider and alias. (Skip to b)

a. In your `config/app.php` file add these two lines.

```php
// In your providers array.
'providers' => [
    ...
    Shetabit\Payment\Provider\PaymentServiceProvider::class,
],

// In your aliases array.
'aliases' => [
    ...
    'Payment' => Shetabit\Payment\Facade\Payment::class,
],
```

In the config file you can set the `default driver` to use for all your payments. But you can also change the driver at runtime.

Choose what gateway you would like to use in your application. Then make that as default driver so that you don't have to specify that everywhere. But, you can also use multiple gateways in a project.

```php
// Eg. if you want to use zarinpal.
'default' => 'zarinpal',
```

Then fill the credentials for that gateway in the drivers array.

```php
'drivers' => [
    'zarinpal' => [
        // Fill in the credentials here.
        'apiPurchaseUrl' => 'https://www.zarinpal.com/pg/rest/WebGate/PaymentRequest.json',
        'apiPaymentUrl' => 'https://www.zarinpal.com/pg/StartPay/',
        'apiVerificationUrl' => 'https://www.zarinpal.com/pg/rest/WebGate/PaymentVerification.json',
        'merchantId' => '',
        'callbackUrl' => 'http://yoursite.com/path/to',
        'description' => 'payment in '.config('app.name'),
    ],
    ...
]
```

## How to use

your `Invoice` holds your payment details, so initially we'll talk about `Invoice` class. 

#### Working with invoices

before doing any thing you need to use `Invoice` class to create an invoice.


In your code, use it like the below:

```php
// At the top of the file.
use Shetabit\Multipay\Invoice;
...

// Create new invoice.
$invoice = new Invoice;

// Set invoice amount.
$invoice->amount(1000);

// Add invoice details: There are 4 syntax available for this.
// 1
$invoice->detail(['detailName' => 'your detail goes here']);
// 2 
$invoice->detail('detailName','your detail goes here');
// 3
$invoice->detail(['name1' => 'detail1','name2' => 'detail2']);
// 4
$invoice->detail('detailName1','your detail1 goes here')
        ->detail('detailName2','your detail2 goes here');

```
available methods:

- `uuid`: set the invoice unique id
- `getUuid`: retrieve the invoice current unique id
- `detail`: attach some custom details into invoice
- `getDetails`: retrieve all custom details 
- `amount`: set the invoice amount
- `getAmount`: retrieve invoice amount
- `transactionId`: set invoice payment transaction id
- `getTransactionId`: retrieve payment transaction id
- `via`: set a driver we use to pay the invoice
- `getDriver`: retrieve the driver

#### Purchase invoice
In order to pay the invoice, we need the payment transactionId.
We purchase the invoice to retrieve transaction id:

```php
// At the top of the file.
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;
...

// Create new invoice.
$invoice = (new Invoice)->amount(1000);

// Purchase the given invoice.
Payment::purchase($invoice,function($driver, $transactionId) {
	// We can store $transactionId in database.
});

// Purchase method accepts a callback function.
Payment::purchase($invoice, function($driver, $transactionId) {
    // We can store $transactionId in database.
});

// You can specify callbackUrl
Payment::callbackUrl('http://yoursite.com/verify')->purchase(
    $invoice, 
    function($driver, $transactionId) {
    	// We can store $transactionId in database.
	}
);
```

#### Pay invoice

After purchasing the invoice, we can redirect the user to the bank payment page:

```php
// At the top of the file.
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;
...

// Create new invoice.
$invoice = (new Invoice)->amount(1000);
// Purchase and pay the given invoice.
// You should use return statement to redirect user to the bank page.
return Payment::purchase($invoice, function($driver, $transactionId) {
    // Store transactionId in database as we need it to verify payment in the future.
})->pay()->render();

// Do all things together in a single line.
return Payment::purchase(
    (new Invoice)->amount(1000), 
    function($driver, $transactionId) {
    	// Store transactionId in database.
        // We need the transactionId to verify payment in the future.
	}
)->pay()->render();

// Retrieve json format of Redirection (in this case you can handle redirection to bank gateway)
return Payment::purchase(
    (new Invoice)->amount(1000), 
    function($driver, $transactionId) {
    	// Store transactionId in database.
        // We need the transactionId to verify payment in the future.
	}
)->pay()->toJson();
```

#### Verify payment

When user has completed the payment, the bank redirects them to your website, then you need to **verify your payment** in order to ensure the `invoice` has been **paid**.

```php
// At the top of the file.
use Shetabit\Payment\Facade\Payment;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
...

// You need to verify the payment to ensure the invoice has been paid successfully.
// We use transaction id to verify payments
// It is a good practice to add invoice amount as well.
try {
	$receipt = Payment::amount(1000)->transactionId($transaction_id)->verify();

    // You can show payment referenceId to the user.
    echo $receipt->getReferenceId();

    ...
} catch (InvalidPaymentException $exception) {
    /**
    	when payment is not verified, it will throw an exception.
    	We can catch the exception to handle invalid payments.
    	getMessage method, returns a suitable message that can be used in user interface.
    **/
    echo $exception->getMessage();
}
```

#### Useful methods

- ###### `callbackUrl`: can be used to change callbackUrl on the runtime.

  ```php
  // At the top of the file.
  use Shetabit\Multipay\Invoice;
  use Shetabit\Payment\Facade\Payment;
  ...
  
  // Create new invoice.
  $invoice = (new Invoice)->amount(1000);
  
  // Purchase the given invoice.
  Payment::callbackUrl($url)->purchase(
      $invoice, 
      function($driver, $transactionId) {
      // We can store $transactionId in database.
  	}
  );
  ```

- ###### `amount`: you can set the invoice amount directly

  ```php
  // At the top of the file.
  use Shetabit\Multipay\Invoice;
  use Shetabit\Payment\Facade\Payment;
  ...
  
  // Purchase (we set invoice to null).
  Payment::callbackUrl($url)->amount(1000)->purchase(
      null, 
      function($driver, $transactionId) {
      // We can store $transactionId in database.
  	}
  );
  ```

- ###### `via`: change driver on the fly

  ```php
  // At the top of the file.
  use Shetabit\Multipay\Invoice;
  use Shetabit\Payment\Facade\Payment;
  ...
  
  // Create new invoice.
  $invoice = (new Invoice)->amount(1000);
  
  // Purchase the given invoice.
  Payment::via('driverName')->purchase(
      $invoice, 
      function($driver, $transactionId) {
      // We can store $transactionId in database.
  	}
  );
  ```
  
- ###### `config`: set driver configs on the fly

  ```php
  // At the top of the file.
  use Shetabit\Multipay\Invoice;
  use Shetabit\Payment\Facade\Payment;
  ...
  
  // Create new invoice.
  $invoice = (new Invoice)->amount(1000);
  
  // Purchase the given invoice with custom driver configs.
  Payment::config('mechandId', 'your mechand id')->purchase(
      $invoice,
      function($driver, $transactionId) {
      // We can store $transactionId in database.
  	}
  );

  // Also we can change multiple configs at the same time.
  Payment::config(['key1' => 'value1', 'key2' => 'value2'])->purchase(
      $invoice,
      function($driver, $transactionId) {
      // We can store $transactionId in database.
  	}
  );
  ```

#### Create custom drivers:

First you have to add the name of your driver, in the drivers array and also you can specify any config parameters you want.

```php
'drivers' => [
    'zarinpal' => [...],
    'my_driver' => [
        ... // Your Config Params here.
    ]
]
```

Now you have to create a Driver Map Class that will be used to pay invoices.
In your driver, You just have to extend `Shetabit\Payment\Abstracts\Driver`.

Eg. You created a class: `App\Packages\PaymentDriver\MyDriver`.

```php
namespace App\Packages\PaymentDriver;

use Shetabit\Multipay\Abstracts\Driver;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Shetabit\Multipay\{Contracts\ReceiptInterface, Invoice, Receipt};

class MyDriver extends Driver
{
    protected $invoice; // Invoice.

    protected $settings; // Driver settings.

    public function __construct(Invoice $invoice, $settings)
    {
        $this->invoice($invoice); // Set the invoice.
        $this->settings = (object) $settings; // Set settings.
    }

    // Purchase the invoice, save its transactionId and finaly return it.
    public function purchase() {
        // Request for a payment transaction id.
        ...
            
        $this->invoice->transactionId($transId);
        
        return $transId;
    }
    
    // Redirect into bank using transactionId, to complete the payment.
    public function pay() {
        // It is better to set bankApiUrl in config/payment.php and retrieve it here:
        $bankUrl = $this->settings->bankApiUrl; // bankApiUrl is the config name.

        // Prepare payment url.
        $payUrl = $bankUrl.$this->invoice->getTransactionId();

        // Redirect to the bank.
        return redirect()->to($payUrl);
    }
    
    // Verify the payment (we must verify to ensure that user has paid the invoice).
    public function verify(): ReceiptInterface {
        $verifyPayment = $this->settings->verifyApiUrl;
        
        $verifyUrl = $verifyPayment.$this->invoice->getTransactionId();
        
        ...
        
        /**
			Then we send a request to $verifyUrl and if payment is not valid we throw an InvalidPaymentException with a suitable message.
        **/
        throw new InvalidPaymentException('a suitable message');
        
        /**
        	We create a receipt for this payment if everything goes normally.
        **/
        return new Receipt('driverName', 'payment_receipt_number');
    }
}
```

Once you create that class you have to specify it in the `payment.php` config file `map` section.

```php
'map' => [
    ...
    'my_driver' => App\Packages\PaymentDriver\MyDriver::class,
]
```

**Note:-** You have to make sure that the key of the `map` array is identical to the key of the `drivers` array.

#### Events

You can listen for 2 events

- **InvoicePurchasedEvent**: Occurs when an invoice is purchased (after purchasing invoice is done successfully).
- **InvoiceVerifiedEvent**: Occurs when an invoice is verified successfully.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has been changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email khanzadimahdi@gmail.com instead of using the issue tracker.

## Credits

- [Mahdi khanzadi][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Intégration des Paiements en Algérie

Cette section décrit comment configurer et utiliser le package `shetabit/payment` pour les moyens de paiement populaires en Algérie : Edahabia (Algérie Poste) et CIB.

**Note Importante Concernant les Passerelles à API Privée (Edahabia, CIB, etc.)**

L'intégration de certaines passerelles de paiement, notamment celles spécifiques à des banques ou des systèmes nationaux comme Edahabia et CIB en Algérie, présente un défi particulier : **leurs API ne sont généralement pas publiques et leur documentation technique n'est pas librement accessible.**

*   **Action Requise par le Développeur**: Vous devrez impérativement contacter directement l'institution financière concernée (Algérie Poste pour Edahabia, votre banque partenaire pour CIB) pour obtenir :
    *   Leur dossier technique d'intégration API.
    *   Vos identifiants marchand, clés API, et tout autre secret nécessaire.
    *   Les URL exactes pour les endpoints de l'API (initiation de paiement, vérification, etc.) et pour la redirection de l'utilisateur.
    *   La liste précise des paramètres attendus par leur API et le format des réponses.

*   **Rôle des Drivers Fournis**: Les classes de driver comme `EdahabiaGateway.php` et `CibGateway.php` fournies dans ce package sont des **squelettes adaptables**. Elles offrent la structure de base et implémentent les interfaces requises par `shetabit/payment`. Cependant, la logique interne pour communiquer avec l'API réelle (les appels HTTP, le mappage des paramètres, l'interprétation des réponses) **devra très probablement être ajustée par vos soins** en fonction de la documentation technique que vous obtiendrez.

*   **Configuration**: Les exemples de configuration dans ce README sont illustratifs. Les noms des clés de configuration (`merchantId`, `apiKey`, etc.) et surtout leurs valeurs doivent correspondre à ce qui est défini dans le dossier technique de la banque.

L'intégration réussie de ces passerelles dépendra de la qualité des informations techniques fournies par l'institution financière et de votre capacité à adapter le driver en conséquence.

### Passerelles Supportées
*   **Edahabia**: Via la classe `Shetabit\Payment\Drivers\EdahabiaGateway`.
*   **CIB**: Via la classe `Shetabit\Payment\Drivers\CibGateway`.

### Configuration

#### 1. Devise Dinar Algérien (DZD)
Assurez-vous que la devise par défaut ou la devise pour vos transactions est configurée sur DZD dans votre fichier `config/payment.php`. Les passerelles Edahabia et CIB utiliseront cette devise.

```php
// config/payment.php
return [
    // ... autres configurations ...
    'currency' => 'DZD',
    // ...
];
```
Si les API des passerelles algériennes requièrent un code devise numérique spécifique pour le DZD (par exemple, `012`), vous devrez vous assurer que la valeur passée correspond à leurs attentes, potentiellement en l'ajustant dans la configuration de la passerelle ou dans la logique du driver si nécessaire.

#### 2. Configuration des Passerelles
Ajoutez la configuration pour Edahabia et CIB dans la section `drivers` de votre fichier `config/payment.php`.

**Important :** Les API pour Edahabia et CIB ne sont pas publiquement documentées. Vous **devez contacter Algérie Poste (pour Edahabia) et votre banque partenaire CIB** pour obtenir les URL d'API réelles, les identifiants marchand, les clés API, et la liste exacte des paramètres requis. Les exemples ci-dessous sont des illustrations.

**Exemple pour EdahabiaGateway:**
```php
// config/payment.php
'drivers' => [
    // ... autres drivers ...
    'edahabia' => [
        'class' => \Shetabit\Payment\Drivers\EdahabiaGateway::class,
        'merchantId' => env('EDAHABIA_MERCHANT_ID', 'VOTRE_ID_MARCHAND_EDAHABIA'),
        'apiKey' => env('EDAHABIA_API_KEY', 'VOTRE_CLE_API_EDAHABIA'),
        'paymentUrl' => env('EDAHABIA_PAYMENT_URL', 'URL_DE_REDIRECTION_EDAHABIA'), // Fourni par Algérie Poste
        'apiUrl' => env('EDAHABIA_API_URL', 'URL_API_EDAHABIA_POUR_VERIFICATION'), // Fourni par Algérie Poste
        'callbackUrl' => env('EDAHABIA_CALLBACK_URL', '/payment/edahabia/callback'), // Votre URL de callback
        'currency' => 'DZD', // ou le code numérique si requis
        'timeout' => env('EDAHABIA_TIMEOUT', 30), // en secondes
        // Ajoutez ici d'autres paramètres spécifiques à Edahabia si documentés par Algérie Poste
    ],
],
```

**Exemple pour CibGateway:**
```php
// config/payment.php
'drivers' => [
    // ... autres drivers ...
    'cib' => [
        'class' => \Shetabit\Payment\Drivers\CibGateway::class,
        'cibMerchantId' => env('CIB_MERCHANT_ID', 'VOTRE_ID_MARCHAND_CIB'),
        'cibAccessKey' => env('CIB_ACCESS_KEY', 'VOTRE_CLE_ACCES_CIB'),
        'cibPaymentUrl' => env('CIB_PAYMENT_URL', 'URL_DE_REDIRECTION_CIB'), // Fourni par votre banque
        'cibApiUrl' => env('CIB_API_URL', 'URL_API_CIB_POUR_VERIFICATION'), // Fourni par votre banque
        'cibCallbackUrlSuccess' => env('CIB_CALLBACK_URL_SUCCESS', '/payment/cib/callback/success'), // Votre URL de callback succès
        'cibCallbackUrlFail' => env('CIB_CALLBACK_URL_FAIL', '/payment/cib/callback/fail'), // Votre URL de callback échec
        'currency' => 'DZD', // ou le code numérique si requis
        'timeout' => env('CIB_TIMEOUT', 30), // en secondes
        // Ajoutez ici d'autres paramètres spécifiques à CIB si documentés par votre banque
    ],
],
```
Le nom du driver (ici `edahabia` et `cib`) est celui que vous utiliserez avec `Payment::via('driver_name')`.
L'option `timeout` (en secondes) peut être utilisée pour spécifier la durée maximale d'attente pour les réponses des API de la passerelle.

### Utilisation

Voici comment initier un paiement et gérer le retour.

**1. Initier le paiement et rediriger :**
```php
use Shetabit\Payment\Facade\Payment;
use Illuminate\Http\Request; // Assurez-vous d'importer Request

// Dans votre contrôleur
public function initiatePayment(Request \$request, \$driverName) // \$driverName peut être 'edahabia' ou 'cib'
{
    \$invoice = Payment::via(\$driverName)
        ->amount(1000.00) // Montant en DZD. Vérifiez si la passerelle attend des unités mineures (ex: centimes).
        ->detail('order_id', 'ORDER_'.uniqid())
        ->detail('description', 'Description de l'achat')
        // Ajoutez d'autres détails requis par la passerelle ou pour votre application
        ->purchase(); // Ceci appelle la méthode purchase() de votre Gateway

    return \$invoice->pay(); // Ceci appelle la méthode pay() et retourne un RedirectionForm
}
```

**2. Gérer le Callback et Vérifier la Transaction :**
Créez les routes dans `routes/web.php` correspondant à vos `callbackUrl`.

```php
// routes/web.php
Route::post('/payment/edahabia/callback', [EdahabiaController::class, 'handleCallback'])->name('payment.edahabia.callback');
Route::post('/payment/cib/callback/success', [CibController::class, 'handleSuccessCallback'])->name('payment.cib.callback.success');
Route::post('/payment/cib/callback/fail', [CibController::class, 'handleFailCallback'])->name('payment.cib.callback.fail');
```

```php
// Dans votre contrôleur de callback (par exemple, EdahabiaController.php)
use Shetabit\Payment\Facade\Payment;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Illuminate\Http\Request; // Assurez-vous d'importer Request
use Illuminate\Support\Facades\Log;

public function handleCallback(Request \$request) // Le nom du driver est implicite ici pour Edahabia
{
    try {
        // Pour Edahabia, le driver est déduit du contexte de la requête ou de la config.
        // Si vous avez plusieurs drivers, vous pourriez avoir besoin de le spécifier.
        // Cependant, la logique de `shetabit/multipay` tente souvent de le résoudre.
        // Pour plus de clarté, on pourrait aussi faire : Payment::driver('edahabia')->verify();

        // La méthode verify() tentera d'utiliser le driver par défaut ou celui qui correspond à la requête.
        // Si Edahabia est le seul driver configuré pour cette route, cela devrait fonctionner.
        // Sinon, pour être explicite, surtout si plusieurs passerelles partagent des endpoints similaires (peu probable)
        // ou si la détection automatique n'est pas fiable pour une raison quelconque :
        // $receipt = Payment::via('edahabia')->verify();
        \$receipt = Payment::verify(); // Appelle la méthode verify() de la passerelle concernée

        // Paiement réussi
        Log::info('Paiement réussi pour la transaction: ' . \$receipt->getReferenceId());
        // Mettez à jour votre base de données, notifiez l'utilisateur, etc.
        // \$receipt->getTransactionId() // ID interne de la facture
        // \$receipt->getReferenceId() // ID de transaction de la passerelle
        return view('payment-success-page', ['transaction_id' => \$receipt->getReferenceId()]);
    } catch (InvalidPaymentException \$e) {
        Log::error('Échec de la vérification du paiement: ' . \$e->getMessage());
        // Paiement échoué, annulé par l'utilisateur, ou données de callback invalides
        // Utiliser trans() pour les messages d'erreur venant des gateways
        return view('payment-failed-page', ['error_message' => \$e->getMessage()]);
    } catch (\Exception \$e) {
        Log::error('Erreur générale lors de la vérification du paiement: ' . \$e->getMessage());
        // Autre erreur technique
        return view('payment-failed-page', ['error_message' => 'Une erreur technique est survenue.']);
    }
}
```
Adaptez la gestion du callback pour CIB en fonction de ses URL de succès et d'échec. La logique de vérification pour CIB sera similaire, potentiellement en spécifiant `Payment::via('cib')->verify()` si nécessaire.

### Localisation (L10N)
Le package supporte la localisation des messages d'erreur pour les passerelles Edahabia et CIB.
*   Les fichiers de traduction sont publiés dans `resource_path('lang/vendor/shetabitPayment/')` lors de l'exécution de `php artisan vendor:publish --tag=payment-lang`. Vous pouvez les personnaliser, notamment les traductions arabes qui sont initialement des placeholders.
*   Les clés de traduction utilisées dans `EdahabiaGateway.php` et `CibGateway.php` suivent le format `shetabitPayment::payment.key_name`.
*   Pour traduire les textes de votre interface (par exemple, dans la vue `redirectForm.blade.php` que vous pouvez publier et modifier via `php artisan vendor:publish --tag=payment-views`), utilisez le système de localisation standard de Laravel.
*   Pour l'arabe, assurez-vous que vos vues supportent l'affichage de droite à gauche (RTL).

### Sécurité et Conformité Réglementaire
*   **HTTPS**: Configurez **impérativement** toutes les URL d'API et de redirection des passerelles avec HTTPS. Ceci inclut vos `callbackUrl`.
*   **Clés API**: Gardez vos identifiants marchand et clés API secrets et sécurisés (utilisez les fichiers `.env` et accédez-y via `env()` dans vos fichiers de configuration).
*   **Conformité Locale**: Il est de **votre responsabilité** de vous assurer que votre intégration respecte toutes les lois et réglementations algériennes concernant les paiements en ligne, la facturation, et la protection des données. Consultez un conseiller juridique si nécessaire.
*   **Vérification Serveur-Serveur**: La méthode `verify()` est conçue pour effectuer une vérification côté serveur avec la passerelle de paiement. Ne vous fiez **jamais** uniquement aux paramètres reçus dans l'URL de retour côté client pour valider un paiement.

### Expérience Utilisateur (UX)
*   Personnalisez la vue `redirectForm.blade.php` (publiable via `php artisan vendor:publish --tag=payment-views`) pour fournir des instructions claires à l'utilisateur avant la redirection vers la page de paiement externe.
*   Affichez les logos d'Edahabia ou de CIB sur votre page de paiement ou de redirection pour rassurer l'utilisateur.
*   Gérez clairement les pages de succès et d'échec après le retour de la passerelle, en informant l'utilisateur du statut de son paiement de manière compréhensible.
*   Si les processus Edahabia ou CIB ont des étapes spécifiques (par exemple, saisie d'un OTP envoyé par SMS), informez-en l'utilisateur en amont si possible, ou assurez-vous que la page de la passerelle est claire.

### Défis Potentiels et Solutions
*   **Documentation des API**: La documentation technique détaillée et les spécifications des API Edahabia et CIB peuvent être difficiles à obtenir ou ne pas être publiques. Vous devrez impérativement contacter Algérie Poste (pour Edahabia) et les banques partenaires CIB (pour CIB) pour obtenir ces informations. Les squelettes de code `EdahabiaGateway.php` et `CibGateway.php` fournis dans ce package sont des implémentations génériques et devront **certainement être adaptés** en fonction des spécifications réelles de ces API (paramètres, endpoints, méthodes d'authentification, formats de réponse, etc.).
*   **Support Technique**: Le support technique des institutions financières peut varier en termes de réactivité et d'expertise. Soyez patient et persévérant dans vos communications.
*   **Tests**: Obtenir des environnements de test (sandbox) fonctionnels et fiables peut être un défi. Renseignez-vous dès le début de votre projet auprès des fournisseurs de services de paiement. Sans sandbox, les tests devront être effectués avec de petites transactions réelles, ce qui n'est pas idéal.
*   **Mises à Jour des API**: Les API des systèmes de paiement peuvent évoluer. Restez en contact avec les fournisseurs pour être informé des changements potentiels qui pourraient affecter votre intégration.

Nous encourageons la communauté à contribuer à l'amélioration de ce support pour l'Algérie, notamment en partageant des informations (non sensibles et publiquement autorisées) sur l'intégration une fois les API obtenues, en améliorant les traductions, ou en proposant des ajustements aux classes Gateway basés sur des expériences réelles.

[ico-version]: https://img.shields.io/packagist/v/shetabit/payment.svg?style=flat-square
[ico-download]: https://img.shields.io/packagist/dt/shetabit/payment.svg?color=%23F18&style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/shetabit/payment.svg?label=Code%20Quality&style=flat-square

[link-fa]: README-FA.md
[link-en]: README.md
[link-zh]: README-ZH.md
[link-packagist]: https://packagist.org/packages/shetabit/payment
[link-code-quality]: https://scrutinizer-ci.com/g/shetabit/payment
[link-author]: https://github.com/khanzadimahdi
[link-contributors]: ../../contributors

## Star History

[![Star History Chart](https://api.star-history.com/svg?repos=shetabit/payment&type=Date)](https://star-history.com/#shetabit/payment&Date)
