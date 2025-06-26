# Guide pour le Développement de Nouvelles Passerelles de Paiement

## 1. Introduction

Ce guide a pour objectif d'aider les développeurs à créer et intégrer de nouvelles passerelles de paiement dans l'écosystème `shetabit/payment` et `shetabit/multipay`. Suivre ces directives assurera la cohérence, la qualité et la maintenabilité des contributions.

La philosophie est de fournir des drivers robustes, sécurisés et faciles à configurer pour interagir avec diverses API de paiement.

## 2. Prérequis

*   Excellente compréhension de PHP et des principes de la Programmation Orientée Objet.
*   Bonne connaissance du framework Laravel (si la passerelle est spécifique à `shetabit/payment` ou pour comprendre son intégration).
*   Accès complet et compréhension de la documentation technique officielle de la passerelle de paiement que vous souhaitez intégrer.
*   Un environnement de test (sandbox) fourni par le prestataire de paiement est fortement recommandé.

## 3. Structure d'un Driver

Les drivers de passerelle résident généralement dans le répertoire `src/Drivers/` du package concerné (`shetabit/payment` ou `shetabit/multipay`).

### 3.1. Interface et Classe Abstraite
Votre classe de driver doit hériter de `Shetabit\Multipay\Abstracts\Driver`. Cette classe abstraite fournit des méthodes utilitaires et s'assure que vous implémentez l'interface `Shetabit\Multipay\Contracts\DriverInterface`.

L'interface `DriverInterface` définit les méthodes publiques suivantes que votre driver doit implémenter :
*   `purchase()`: Pour initier la transaction ou effectuer les appels API nécessaires avant de rediriger l'utilisateur. Doit retourner l'ID de transaction de la passerelle ou mettre à jour l'objet `Invoice`.
*   `pay()`: Pour générer le formulaire de redirection (`RedirectionForm`) ou les données nécessaires pour envoyer l'utilisateur vers la plateforme de paiement.
*   `verify()`: Pour vérifier le statut de la transaction après le retour de l'utilisateur ou via un callback serveur-à-serveur. Doit retourner un objet implémentant `ReceiptInterface`.

Votre classe héritera également des méthodes `amount()` et `detail()` de `AbstractDriver` qui permettent de stocker le montant et les détails de la facture (`Invoice`).

### 3.2. Nommage et Namespace
*   **Namespace**: `Shetabit\Payment\Drivers` (pour `shetabit/payment`) ou `Shetabit\Multipay\Drivers` (pour `shetabit/multipay`).
*   **Nom de classe**: Utilisez un nom descriptif suffixé par `Gateway` (par exemple, `MyNewPaymentGateway.php`).

## 4. Gestion de la Configuration

Votre driver recevra un objet `Invoice` et un tableau `settings` dans son constructeur.
```php
protected Invoice \$invoice;
protected array \$settings;

public function __construct(Invoice \$invoice, array \$settings)
{
    \$this->invoice = \$invoice; // Contient le montant, l'UUID, les détails
    \$this->settings = \$settings; // Vos clés API, URL, etc.
}
```
Ces `settings` sont chargés depuis le fichier `config/payment.php` de l'application Laravel, sous la clé de votre driver.

*   Accédez à vos configurations via `$this->settings['your_config_key']`.
*   Définissez des clés de configuration claires (par exemple, `merchantId`, `apiKey`, `apiUrl`, `sandboxMode`).
*   Pour les données sensibles (clés API, secrets), assurez-vous que la documentation recommande l'utilisation de `env()` dans le fichier `config/payment.php` publié.
*   **Timeouts**: Si votre driver effectue des appels HTTP externes, prévoyez une option de configuration `timeout` (en secondes) que les utilisateurs peuvent définir. Utilisez cette valeur lors de vos appels HTTP. Par exemple :
    ```php
    // Dans la configuration du driver (config/payment.php)
    // 'your_driver_name' => [
    //     // ...
    //     'timeout' => 45, // secondes
    // ]

    // Dans votre driver
    $timeout = $this->settings['timeout'] ?? 30; // Une valeur par défaut raisonnable
    // Http::timeout($timeout)->post(...);
    ```

## 5. Flux de Paiement Typique

1.  L'application appelle `Payment::via('yourDriverName')->amount(100)->detail('key', 'value')->purchase();`.
2.  Votre méthode `purchase()` est exécutée. Vous y effectuez les appels API nécessaires pour enregistrer la transaction auprès de la passerelle. Stockez l'ID de transaction retourné par la passerelle dans l'objet `Invoice` via `$this->invoice->setTransactionId('gateway_txn_id');`.
3.  L'application appelle ensuite `$invoice->pay();` (ou vous le chaînez).
4.  Votre méthode `pay()` est exécutée. Vous y préparez un objet `Shetabit\Multipay\RedirectionForm` avec l'URL de la passerelle et les données requises.
5.  L'utilisateur est redirigé et effectue le paiement.
6.  L'utilisateur est redirigé vers votre URL de callback.
7.  Dans votre contrôleur de callback, vous appelez `Payment::via('yourDriverName')->verify();`.
8.  Votre méthode `verify()` est exécutée. Vous y récupérez les données de la requête, effectuez un appel API à la passerelle pour confirmer le statut réel de la transaction, et retournez un `Shetabit\Multipay\Receipt`.

#### 5.1. Création d'un Reçu (`Receipt`) Riche

Lors de l'implémentation de la méthode `verify()`, votre driver doit retourner un objet implémentant `Shetabit\Multipay\Contracts\ReceiptInterface`. Idéalement, cet objet devrait être aussi informatif que possible.

Envisagez de retourner un objet qui implémente également une interface plus riche (comme `Shetabit\Payment\Contracts\RichReceiptInterface` à titre d'exemple) pour standardiser l'accès à des données telles que :
*   Date du paiement (`getPaymentDate()`)
*   Montant payé et devise (`getAmountPaid()`, `getCurrencyPaid()`)
*   Type de méthode de paiement (`getPaymentMethodType()`)
*   Quatre derniers chiffres de la carte (`getCardLastFour()`)
*   Réponse brute de la passerelle (`getRawGatewayResponse()`)

Peupler ces informations de manière standardisée améliore grandement l'utilisabilité du reçu pour le développeur final. Si la classe `Shetabit\Multipay\Receipt` de base est utilisée, assurez-vous de stocker la référence de transaction et toute autre information pertinente que l'API de votre passerelle fournit.

## 6. Gestion des Erreurs et Exceptions

*   Utilisez les exceptions standards fournies par `shetabit/multipay` lorsque c'est approprié :
    *   `Shetabit\Multipay\Exceptions\PurchaseFailedException`: Si l'initiation du paiement échoue.
    *   `Shetabit\Multipay\Exceptions\InvalidPaymentException`: Si la vérification échoue (paiement non confirmé, données invalides, etc.).
    *   `Shetabit\Multipay\Exceptions\PaymentFailedException`: Utilisée plus rarement, souvent pour des échecs non couverts par les autres.
*   Fournissez des messages d'erreur clairs et significatifs. Ces messages doivent être traduisibles (voir section Localisation).
*   Gérez les erreurs de communication avec l'API (timeouts, erreurs HTTP) de manière robuste.

## 7. Localisation (L10N)

Si votre driver lève des exceptions avec des messages spécifiques, ces messages doivent être traduisibles.
*   Utilisez la fonction `trans()` de Laravel : `throw new PurchaseFailedException(trans('shetabitPayment::payment.your_driver_error_key'));`
*   Ajoutez vos clés de traduction aux fichiers de langue du package (par exemple, `resources/lang/en/payment.php`, `resources/lang/fr/payment.php`).
*   Assurez-vous que le `PaymentServiceProvider` charge et publie ces fichiers de langue. Le namespace utilisé ici est `shetabitPayment` (pour les drivers dans `shetabit/payment`). Ajustez si vous contribuez à `shetabit/multipay`.

## 8. Sécurité

*   **HTTPS**: Toutes les communications avec l'API de la passerelle doivent se faire via HTTPS. Les URL configurées doivent être HTTPS.
*   **Données Sensibles**: Ne stockez **jamais** de numéros de carte de crédit complets, CVV, ou dates d'expiration. La tokenisation ou la gestion de ces données doit être entièrement déléguée à la passerelle de paiement.
*   **Vérification Serveur-Serveur**: La méthode `verify()` **doit** inclure un appel API direct depuis votre serveur vers le serveur de la passerelle pour confirmer le statut de la transaction. Ne vous fiez jamais uniquement aux paramètres reçus dans l'URL de redirection côté client.
*   **Clés API**: Ne codez jamais de clés API en dur. Elles doivent provenir de la configuration.
*   **Validation des Données**: Validez toutes les données reçues de la passerelle avant de les utiliser.

## 9. Tests

Il est **obligatoire** de fournir des tests unitaires robustes pour votre driver de passerelle. Des tests bien écrits garantissent la fiabilité de votre driver et facilitent sa maintenance.

Utilisez le framework de test PHPUnit intégré à Laravel. Pour les appels API externes, utilisez impérativement le système de Mocking HTTP de Laravel (`Illuminate\Support\Facades\Http::fake()`) ou des bibliothèques de mocking similaires (comme Guzzle Mock Handler si vous n'utilisez pas le Facade `Http` de Laravel).

### 9.1. Ce qu'il Faut Tester

Voici une liste non exhaustive des aspects à couvrir dans vos tests :

1.  **Construction et Configuration** :
    *   Vérifiez que le driver peut être instancié correctement.
    *   Testez que les configurations essentielles (clés API, URLs, mode) sont correctement lues et stockées à partir du tableau `\$settings`.
    *   Testez la gestion des configurations manquantes ou invalides (par exemple, lever une exception si une clé API requise n'est pas fournie).

2.  **Méthode `purchase()`** :
    *   Simulez une réponse API réussie de la passerelle et vérifiez que :
        *   L'ID de transaction est correctement extrait de la réponse et défini sur l'objet `Invoice` (`\$this->invoice->setTransactionId(...)`).
        *   La méthode retourne la valeur attendue (généralement l'ID de transaction ou l'objet `Invoice` lui-même).
    *   Simulez différents types de réponses d'échec de l'API (erreur d'authentification, solde insuffisant, requête invalide, etc.) et vérifiez que les exceptions appropriées (par exemple, `PurchaseFailedException`) sont levées avec des messages clairs et traduisibles.
    *   Vérifiez que les données correctes sont envoyées à l'API de la passerelle (montant, devise, identifiants, URL de callback, etc.).

3.  **Méthode `pay()`** :
    *   Assurez-vous que la méthode retourne un objet `Shetabit\Multipay\RedirectionForm`.
    *   Vérifiez que l'URL d'action du formulaire de redirection est correcte.
    *   Vérifiez que tous les paramètres requis par la passerelle pour la redirection sont présents dans les données du formulaire et correctement formatés.
    *   Testez le cas où `purchase()` n'a pas été appelé ou a échoué (par exemple, l'ID de transaction est manquant).

4.  **Méthode `verify()`** :
    *   C'est souvent la méthode la plus complexe à tester en raison des multiples scénarios de retour.
    *   **Cas de Succès** :
        *   Simulez une requête de callback valide de la passerelle (avec les bons paramètres).
        *   Simulez une réponse API de vérification réussie de la part de la passerelle.
        *   Vérifiez que la méthode retourne un objet implémentant `Shetabit\Multipay\Contracts\ReceiptInterface`.
        *   Vérifiez que l'ID de transaction et la référence de la passerelle sont correctement définis sur le reçu.
        *   Si vous implémentez `RichReceiptInterface`, vérifiez que les données supplémentaires (montant payé, date, etc.) sont correctement extraites et définies.
    *   **Cas d'Échec (Callback)** :
        *   Simulez un callback où des paramètres essentiels sont manquants ou invalides : levez `InvalidPaymentException`.
        *   Simulez un callback indiquant un paiement échoué ou annulé par l'utilisateur (avant même l'appel API de vérification si possible) : levez `InvalidPaymentException`.
    *   **Cas d'Échec (API de Vérification)** :
        *   Simulez une réponse de l'API de vérification indiquant que la transaction n'est pas trouvée, pas encore payée, ou a échoué : levez `InvalidPaymentException`.
        *   Simulez une erreur de communication avec l'API de vérification (timeout, erreur serveur) : levez une exception appropriée.
    *   **Cas de Sécurité** :
        *   Si la vérification implique une signature ou un token, testez le cas où la signature est invalide.

5.  **Gestion des Devises (si `CurrencyAwareDriverInterface` est implémentée)** :
    *   Testez que `getSupportedCurrencies()` retourne les bonnes devises.
    *   Testez que `expectsAmountInMinorUnits()` retourne la bonne valeur booléenne.
    *   (Si la logique de validation est dans le driver) Testez qu'une exception est levée si on tente un paiement avec une devise non supportée.

6.  **Health Check (si `HealthCheckableInterface` est implémentée)** :
    *   Testez la méthode `checkHealth()` :
        *   Vérifiez qu'elle retourne le statut 'ok' avec les bonnes configurations.
        *   Vérifiez qu'elle retourne le statut 'error' si des configurations essentielles sont manquantes ou invalides.
        *   Vérifiez que les détails retournés sont corrects.

### 9.2. Exemple de Structure de Test (Conceptuel)

```php
<?php

namespace Shetabit\Payment\Tests\Unit\Drivers; // Ajustez le namespace

use Shetabit\Payment\Drivers\MyNewGateway; // Votre classe de driver
use Shetabit\Multipay\Invoice;
use Shetabit\Multipay\Contracts\ReceiptInterface;
use Shetabit\Multipay\RedirectionForm;
use Shetabit\Multipay\Exceptions\PurchaseFailedException;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase; // Votre classe TestCase de base

class MyNewGatewayTest extends TestCase
{
    private array \$settings;
    private Invoice \$invoice;

    protected function setUp(): void
    {
        parent::setUp();

        \$this->settings = [
            'merchantId' => 'test_merchant',
            'apiKey' => 'test_api_key',
            'paymentUrl' => 'https://gateway.example.com/pay',
            'apiUrl' => 'https://api.gateway.example.com/v1',
            'callbackUrl' => 'https://myapp.com/callback/mynewgateway',
            'currency' => 'XYZ', // Devise supportée par votre passerelle
        ];

        \$this->invoice = new Invoice();
        \$this->invoice->amount(1000); // Montant dans l'unité attendue
        \$this->invoice->uuid('test-uuid');
        // \$this->invoice->currency('XYZ'); // Si votre Invoice stocke la devise
    }

    /** @test */
    public function it_can_be_instantiated()
    {
        \$driver = new MyNewGateway(\$this->invoice, \$this->settings);
        \$this->assertInstanceOf(MyNewGateway::class, \$driver);
    }

    /** @test */
    public function purchase_succeeds_and_sets_transaction_id_on_successful_api_response()
    {
        Http::fake([
            \$this->settings['apiUrl'].'/transactions' => Http::response(['transaction_id' => 'gw_txn_123', 'status' => 'pending'], 200)
        ]);

        \$driver = new MyNewGateway(\$this->invoice, \$this->settings);
        \$driver->purchase();

        \$this->assertEquals('gw_txn_123', \$this->invoice->getTransactionId());
    }

    /** @test */
    public function purchase_throws_exception_on_api_failure()
    {
        Http::fake([
            \$this->settings['apiUrl'].'/transactions' => Http::response(['error' => 'Invalid API Key'], 401)
        ]);

        \$this->expectException(PurchaseFailedException::class);
        // this->expectExceptionMessage('Message traduit de l'erreur'); // Si vous testez le message

        \$driver = new MyNewGateway(\$this->invoice, \$this->settings);
        \$driver->purchase();
    }

    /** @test */
    public function pay_returns_a_redirection_form_with_correct_data()
    {
        // Simuler que purchase a réussi et a défini un ID de transaction
        \$this->invoice->setTransactionId('gw_txn_123');
        \$driver = new MyNewGateway(\$this->invoice, \$this->settings);
        \$redirectionForm = \$driver->pay();

        \$this->assertInstanceOf(RedirectionForm::class, \$redirectionForm);
        \$this->assertEquals(\$this->settings['paymentUrl'], \$redirectionForm->getAction());
        \$this->assertEquals('POST', strtoupper(\$redirectionForm->getMethod())); // Ou GET
        \$this->assertArrayHasKey('transaction_id', \$redirectionForm->getInputs());
        \$this->assertEquals('gw_txn_123', \$redirectionForm->getInputs()['transaction_id']);
        // Ajoutez d'autres assertions pour les champs du formulaire
    }

    /** @test */
    public function verify_returns_receipt_on_successful_payment_and_api_confirmation()
    {
        // Simuler la requête de callback de la passerelle
        // request()->merge(['gateway_txn_ref' => 'gw_txn_123', 'status_code' => '00']); // Exemple

        Http::fake([
            \$this->settings['apiUrl'].'/transactions/gw_txn_123/verify' => Http::response(['status' => 'completed', 'reference' => 'ref_abc'], 200)
        ]);

        // Simuler que la transaction ID de la facture est connue (par exemple, récupérée de la session ou DB)
        \$this->invoice->setTransactionId('gw_txn_123'); // Important si verify() en a besoin

        \$driver = new MyNewGateway(\$this->invoice, \$this->settings);

        // Simuler les inputs de la requête de callback
        // La manière de le faire dépendra de comment votre driver accède aux inputs (Request facade, etc.)
        // Pour cet exemple, supposons que le driver utilise request() helper ou une Request injectée.
        // Dans un vrai test, vous pourriez avoir besoin de créer une vraie Request et la passer, ou mocker la Facade Request.
        // Ici, on va mocker la facade pour simplifier l'exemple conceptuel.
        app('request')->merge(['gateway_txn_ref' => 'gw_txn_123', 'status_code' => '00']);


        \$receipt = \$driver->verify();

        \$this->assertInstanceOf(ReceiptInterface::class, \$receipt);
        \$this->assertEquals('ref_abc', \$receipt->getReferenceId());
        // \$this->assertTrue(\$receipt->isSucceeded()); // Si votre Receipt a une telle méthode
    }

    /** @test */
    public function verify_throws_exception_if_callback_data_is_missing_or_tampered()
    {
        app('request')->merge(['gateway_txn_ref' => null]); // Données manquantes

        \$this->expectException(InvalidPaymentException::class);

        \$driver = new MyNewGateway(\$this->invoice, \$this->settings);
        \$driver->verify();
    }

    // Ajoutez plus de tests pour les cas d'échec de verify(), les erreurs API, etc.
}
```

### 9.3. Tests Manuels
Même avec une excellente couverture de tests unitaires, les tests manuels avec un véritable environnement de sandbox (si fourni par la passerelle) sont cruciaux avant de considérer un driver comme prêt pour la production. Cela permet de valider l'expérience utilisateur de bout en bout et d'attraper des problèmes non couverts par les mocks.

---

## 10. Gestion des Webhooks (Notifications Asynchrones)

Les webhooks permettent aux passerelles de paiement d'envoyer des notifications asynchrones à votre application pour des événements tels que les paiements réussis, les remboursements, les litiges, etc. C'est un mécanisme plus fiable que de se baser uniquement sur la redirection de l'utilisateur.

Bien qu'un framework complet de gestion des webhooks soit idéalement une fonctionnalité du package `shetabit/multipay` de base, les drivers développés pour `shetabit/payment` peuvent se préparer à une telle fonctionnalité.

### 10.1. `WebhookHandlerInterface` (Concept)

Une interface comme `Shetabit\Payment\Contracts\WebhookHandlerInterface` (ou son équivalent dans `shetabit/multipay`) pourrait être définie pour standardiser la gestion des webhooks :

```php
<?php

namespace Shetabit\Payment\Contracts; // Ou Shetabit\Multipay\Contracts

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

interface WebhookHandlerInterface
{
    public function handleWebhook(Request \$request): Response;
    // public function getSubscribedEvents(): array; // Optionnel
}
```

### 10.2. Responsabilités d'un Handler de Webhook

Si votre passerelle supporte les webhooks et que vous implémentez cette interface, votre méthode `handleWebhook` devrait :

1.  **Vérifier l'Authenticité**: Validez la signature du webhook (par exemple, en utilisant un secret partagé et un algorithme HMAC) pour vous assurer qu'il provient bien de la passerelle. Rejetez les requêtes non valides.
2.  **Parser le Payload**: Analysez le corps de la requête (souvent JSON) pour extraire le type d'événement et les données associées.
3.  **Traiter l'Événement**:
    *   Mettez à jour l'état de la commande/facture dans votre application.
    *   Loggez l'événement.
    *   Émettez des événements Laravel (par exemple, `WebhookPaymentSucceeded::dispatch(\$invoice, \$payload)`) pour permettre à d'autres parties de l'application de réagir.
4.  **Retourner une Réponse HTTP**: Répondez rapidement avec un statut HTTP `200 OK` pour accuser réception si le traitement initial (vérification, parsing de base) est réussi. Les traitements plus longs devraient être délégués à des jobs en file d'attente. Si la signature est invalide ou la requête malformée, retournez un code d'erreur approprié (4xx).

### 10.3. Configuration

L'URL de votre endpoint de webhook et le secret de signature devront être configurés par l'utilisateur dans `config/payment.php` et sur le tableau de bord de la passerelle de paiement.

## 11. Fonctionnalités Avancées (Concepts)

Cette section aborde des fonctionnalités de paiement avancées qui pourraient être intégrées à l'avenir, principalement au niveau de `shetabit/multipay`.

### 11.1. Paiements Récurrents / Abonnements

Le support des paiements récurrents est une fonctionnalité complexe qui varie considérablement entre les passerelles. Une intégration nécessiterait probablement une nouvelle interface, par exemple `RecurringPaymentDriverInterface`, avec des méthodes pour :

*   `createSubscription(array \$planDetails, array \$customerDetails, array \$options = []): array`: Créer un abonnement.
*   `getSubscriptionDetails(string \$subscriptionId): array`: Obtenir les détails d'un abonnement.
*   `updateSubscription(string \$subscriptionId, array \$updateDetails): array`: Mettre à jour un abonnement.
*   `cancelSubscription(string \$subscriptionId): array`: Annuler un abonnement.

**Défis Clés pour les Abonnements :**
*   **Tokenisation**: Gestion sécurisée des informations de paiement pour les débits futurs.
*   **Logique de Plan**: Définition des fréquences, montants, périodes d'essai, etc.
*   **Webhooks**: Indispensables pour suivre l'état des paiements récurrents.
*   **Standardisation**: Trouver un ensemble commun de fonctionnalités malgré les différences entre les API des passerelles.

L'implémentation de cette fonctionnalité serait une entreprise majeure. Les développeurs de drivers pour des passerelles supportant les abonnements devraient consulter la documentation de l'API de leur passerelle et se préparer à une éventuelle interface standardisée.

### 11.2. Remboursements (Refunds)

La capacité de traiter des remboursements est essentielle. Une intégration future pourrait inclure une interface comme `RefundableDriverInterface` avec une méthode principale :

*   `refund(string \$transactionReference, float \$amount, array \$options = []): array`: Pour initier un remboursement complet ou partiel d'une transaction existante.
    *   `\$transactionReference`: L'ID de la transaction originale à rembourser.
    *   `\$amount`: Le montant à rembourser.
    *   `\$options`: Peut inclure une raison, une référence de remboursement interne, etc.
    *   La méthode devrait retourner un tableau avec le statut et l'ID du remboursement de la passerelle.

**Considérations pour les Remboursements :**
*   **Support API**: Toutes les passerelles ne permettent pas les remboursements via API, ou peuvent avoir des restrictions (délais, permissions).
*   **Remboursements Partiels**: La logique doit gérer correctement les remboursements partiels si supportés.
*   **Statut Asynchrone**: Le statut final d'un remboursement peut être notifié via webhook. La réponse initiale à l'appel API de remboursement peut être simplement un accusé de réception.
*   **Exceptions**: Une exception spécifique comme `PaymentRefundFailedException` devrait être utilisée pour les échecs.

### 11.3. Gestion des Litiges (Disputes)

La gestion des litiges (chargebacks) est une fonctionnalité très avancée et dont le support API varie énormément. Si elle devait être standardisée, une interface comme `DisputeManagementDriverInterface` pourrait inclure :

*   `getDisputeDetails(string \$disputeId): array`: Obtenir les détails d'un litige.
*   `listDisputes(array \$filters = []): array`: Lister les litiges.
*   `provideEvidenceForDispute(string \$disputeId, array \$evidenceDetails): bool`: Soumettre des preuves pour un litige.
*   `acceptDispute(string \$disputeId): bool`: Accepter un litige.

**Considérations pour la Gestion des Litiges :**
*   **Support API Limité**: Moins de passerelles offrent des API complètes pour cela par rapport aux paiements ou remboursements.
*   **Processus Complexes**: Les flux de litiges sont souvent complexes et spécifiques à chaque réseau de cartes ou prestataire.
*   **Webhooks Cruciaux**: Les notifications de nouveaux litiges ou de mises à jour de statut se font quasi exclusivement par webhooks.

L'intégration de cette fonctionnalité serait très spécifique aux quelques passerelles qui la proposent de manière programmable.

## 12. Considérations pour les Passerelles à API Privée/Restreinte

Si vous développez un driver pour une passerelle de paiement dont l'API n'est pas publiquement documentée (par exemple, une banque locale spécifique), il y a des considérations importantes :

1.  **Documentation du Driver**:
    *   Indiquez très clairement dans la documentation de votre driver (par exemple, dans le `README.md` du package principal si votre driver y est inclus) que l'API est privée.
    *   Expliquez que l'utilisateur final du package devra contacter directement l'institution financière pour obtenir la documentation technique de l'API, les identifiants, les URL, etc.
    *   Précisez que votre driver est un "squelette" ou un "adaptateur" qui fournit la structure de base, mais que des ajustements dans la logique d'appel API, le mappage des paramètres, et l'interprétation des réponses pourraient être nécessaires en fonction des détails techniques fournis par la banque.

2.  **Flexibilité de la Configuration**:
    *   Rendez la configuration de votre driver aussi flexible que possible pour permettre à l'utilisateur de spécifier les URL d'API, les noms des paramètres clés si ceux-ci peuvent varier légèrement, etc.
    *   Documentez exhaustivement toutes les options de configuration.

3.  **Gestion des Erreurs**:
    *   Étant donné que vous ne pourrez peut-être pas tester tous les scénarios d'API sans accès direct et complet, assurez-vous que votre driver gère les erreurs de communication et les réponses inattendues de manière aussi gracieuse que possible, en fournissant des logs et des exceptions utiles.

4.  **Contribution et Maintenance**:
    *   La maintenance d'un tel driver peut être complexe si vous n'avez pas un accès continu à un environnement de test ou aux mises à jour de l'API. Soyez transparent à ce sujet.
    *   Encouragez les utilisateurs ayant un accès direct à l'API à contribuer aux améliorations et aux corrections.

L'objectif est de fournir un point de départ utile tout en gérant les attentes sur le travail d'intégration final qui incombera à l'utilisateur en collaboration avec son prestataire de paiement.

## 13. Documentation du Driver

Ajoutez une section au `README.md` principal du package pour documenter :
*   Comment configurer votre driver (clés spécifiques dans `config/payment.php`).
*   Les particularités d'utilisation de votre driver.
*   Les dépendances spécifiques si votre driver en a (par exemple, un SDK externe).

## 14. Processus de Contribution

1.  Forkez le dépôt du package (`shetabit/payment` ou `shetabit/multipay`).
2.  Créez une branche pour votre nouvelle passerelle (`feature/my-new-gateway`).
3.  Développez votre driver en suivant ce guide et les standards de codage (PSR-12).
4.  Écrivez les tests unitaires.
5.  Mettez à jour la documentation.
6.  Soumettez une Pull Request (PR) vers la branche principale du dépôt original.
7.  Décrivez clairement les changements dans votre PR et expliquez comment tester votre passerelle (idéalement avec des identifiants de sandbox si vous pouvez les partager ou une méthode pour mocker).

## 15. Exemple de Squelette

Reportez-vous aux drivers existants comme `EdahabiaGateway.php` ou `CibGateway.php` dans `shetabit/payment` pour un exemple de structure de base.

---

En suivant ce guide, vous contribuerez à maintenir un haut niveau de qualité et de cohérence au sein de l'écosystème `shetabit/payment`. Merci pour votre contribution !
