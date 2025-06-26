# Guide de Dépannage (FAQ) pour `shetabit/payment`

Cette page vise à vous aider à résoudre les problèmes courants rencontrés lors de l'utilisation du package `shetabit/payment`.

## 1. Erreur : "Driver [NomDuDriver] not found"

*   **Cause Possible 1**: Le nom du driver utilisé dans `Payment::via('NomDuDriver')` ou `Payment::driver('NomDuDriver')` ne correspond pas exactement à celui défini dans votre fichier `config/payment.php` à la section `drivers`.
    *   **Solution**: Vérifiez les fautes de frappe. Les noms sont sensibles à la casse.

*   **Cause Possible 2**: La configuration pour ce driver est manquante ou incorrecte dans `config/payment.php`.
    *   **Solution**: Assurez-vous que la configuration du driver inclut au minimum la clé `class` pointant vers la classe de driver correcte. Par exemple :
        ```php
        // config/payment.php
        'drivers' => [
            'mydriver' => [
                'class' => \App\Payments\MyDriverGateway::class,
                // ... autres configurations ...
            ],
        ],
        ```

*   **Cause Possible 3**: Si vous utilisez un driver d'un package externe (comme une passerelle spécifique à un pays qui n'est pas dans `shetabit/multipay` par défaut), assurez-vous que ce package est correctement installé et que son service provider est enregistré (si nécessaire).

## 2. Le paiement échoue sans message d'erreur clair de la passerelle

*   **Solution 1**: Activez la journalisation (logging) dans Laravel (`config/logging.php`) au niveau `DEBUG`. Effectuez un nouveau test de paiement. Examinez les fichiers de log de Laravel (dans `storage/logs/`) pour des messages d'erreur plus détaillés qui pourraient provenir du package `shetabit/payment`, de `shetabit/multipay`, ou du client HTTP (comme Guzzle) lors de la communication avec l'API de la passerelle.

*   **Solution 2**: Connectez-vous au tableau de bord (dashboard) fourni par votre prestataire de paiement (par exemple, PayPal, Stripe, Edahabia, CIB). Souvent, leur interface liste les tentatives de paiement récentes avec des détails sur les erreurs ou les raisons de l'échec.

*   **Solution 3**: Vérifiez que vos clés API, identifiants marchand, et autres secrets sont corrects et correspondent à l'environnement que vous testez (sandbox/test ou production/live). Une clé de test ne fonctionnera pas en production, et vice-versa.

*   **Solution 4**: Assurez-vous que l'URL de l'API de la passerelle configurée dans `config/payment.php` est correcte pour l'environnement testé.

## 3. Problèmes avec l'URL de Callback / Webhook

*   **Problème**: L'utilisateur paie avec succès, mais la commande n'est pas mise à jour dans votre application, ou vous suspectez que le callback de la passerelle n'atteint pas votre application.

*   **Solution 1 (Pour les callbacks par redirection utilisateur)**:
    *   Vérifiez que l'URL de callback (`callbackUrl`, `return_url`, etc.) configurée dans `config/payment.php` pour la passerelle est correcte et pointe vers une route valide dans votre application Laravel.
    *   Assurez-vous que cette route ne soit pas bloquée par des protections CSRF si la passerelle effectue un `POST` simple sans envoyer de token CSRF (ce qui est courant). Vous pourriez avoir besoin d'exclure cette route de la vérification CSRF dans `app/Http/Middleware/VerifyCsrfToken.php`, mais faites-le avec prudence et comprenez les implications de sécurité. La vérification serveur-à-serveur dans la méthode `verify()` devient alors encore plus cruciale.
    *   Testez l'URL de callback directement dans votre navigateur (si c'est un `GET`) ou avec un outil comme Postman pour voir si votre application la gère correctement.

*   **Solution 2 (Pour les webhooks serveur-à-serveur)**:
    *   Vérifiez que l'URL du webhook configurée sur le tableau de bord de la passerelle de paiement est correcte et pointe vers votre application.
    *   Assurez-vous que votre serveur est accessible publiquement sur cette URL. Des outils comme `ngrok` peuvent être utiles pour tester les webhooks sur un environnement de développement local.
    *   Vérifiez les logs de votre serveur web (Nginx, Apache) pour voir si des requêtes entrantes atteignent cette URL.
    *   Si le handler de webhook (par exemple, une classe implémentant `WebhookHandlerInterface`) effectue une vérification de signature, assurez-vous que le secret de signature est identique dans votre configuration et sur le tableau de bord de la passerelle.

## 4. Erreurs liées à la devise

*   **Problème**: "Currency not supported" ou erreurs de montant incorrect.
    *   **Solution**:
        *   Vérifiez la configuration `currency` globale dans `config/payment.php` et la configuration de devise spécifique au driver.
        *   Si vous utilisez `CurrencyAwareDriverInterface`, assurez-vous que la devise de la facture est listée dans `getSupportedCurrencies()` du driver.
        *   Vérifiez si la passerelle attend les montants dans l'unité principale (ex: Dinars) ou l'unité mineure (ex: Santeem/centimes). La méthode `expectsAmountInMinorUnits()` (si implémentée et utilisée par le système de paiement) et la documentation de l'API de la passerelle sont vos guides.

## 5. "Health Check" d'une passerelle échoue

*   **Problème**: `php artisan payment:check-gateway {driver}` signale des erreurs.
    *   **Solution**: Lisez attentivement les messages de la commande. Ils devraient indiquer quelles clés de configuration sont manquantes ou quel format est invalide. Référez-vous à la documentation du `README.md` pour la configuration attendue de cette passerelle.

## 6. Contribuer ou Obtenir de l'Aide Supplémentaire

*   Si vous rencontrez un problème non listé ici, ou si vous avez une solution à un problème courant, n'hésitez pas à :
    *   Ouvrir une "Issue" sur le dépôt GitHub du package `shetabit/payment`.
    *   Proposer une Pull Request pour améliorer ce guide de dépannage.
