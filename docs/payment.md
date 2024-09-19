# Paiement

## Paiement sur la plateforme

### Étape 1 : Création de la Transaction

**Endpoint**: `POST /transactions/payment`

**Description**: Cette route permet de créer une nouvelle transaction de paiement sur la plateforme.

### Champs de la Requête

La requête doit contenir les champs suivants dans le corps de la requête (format JSON) :

| Champ           | Type    | Description                                                           | Obligatoire |
| --------------- | ------- | --------------------------------------------------------------------- | ----------- |
| `amount`        | numeric | Montant de la transaction (doit être supérieur à 0)                   | Oui         |
| `aggregator_id` | integer | ID de l'agrégateur (doit exister dans la table `aggregators`)         | Oui         |
| `currency_id`   | integer | ID de la devise (doit exister dans la table `currencies`)             | Oui         |
| `service_name`  | string  | Nom du service (doit avoir entre 2 et 100 caractères)                 | Oui         |
| `service_id`    | integer | ID du service (maximum 100 caractères)                                | Oui         |
| `payer_uuid`    | uuid    | UUID du payeur                                                        | Oui         |
| `note`          | string  | Note ou description de la transaction                                 | Oui         |
| `receiver_uuid` | uuid    | UUID du receveur (peut être nul, doit être différent de `payer_uuid`) | Non         |

L'absence montre que l'argent ira dans le compte de la plateforme, sinon sur le compte du receveur

### Exemple de Requête
```http
POST /transactions/payment HTTP/1.1
Host: example.com
Content-Type: application/json

{
    "amount": 100.00,
    "aggregator_id": 1,
    "currency_id": 2,
    "service_name": "Service Example",
    "service_id": 123,
    "payer_uuid": "550e8400-e29b-41d4-a716-446655440000",
    "note": "Paiement pour service",
    "receiver_uuid": "550e8400-e29b-41d4-a716-446655440001"
}

```
```json
{
    "amount": 100.00,
    "aggregator_id": 1,
    "currency_id": 2,
    "service_name": "Service Example",
    "service_id": 123,
    "payer_uuid": "550e8400-e29b-41d4-a716-446655440000",
    "note": "Paiement pour service",
    "receiver_uuid": "550e8400-e29b-41d4-a716-446655440001"
}
```

### Réponse

En cas de succès, la réponse sera au format JSON et contiendra les champs suivants :

| Champ              | Type   | Description                                       |
| ------------------ | ------ | ------------------------------------------------- |
| `uuid`             | string | UUID de la transaction                            |
| `status`           | string | Statut de la transaction                          |
| `type`             | string | Type de la transaction (ex: paiement)             |
| `verification_url` | string | URL pour vérifier la transaction                  |
| `payment_url`      | string | URL pour effectuer le paiement                    |
| `transaction_id`   | string | ID de la transaction dans le système d'agrégation |

### Exemple de Réponse

```json
{
    "uuid": "550e8400-e29b-41d4-a716-446655440002",
    "status": "processed",
    "type": "payment",
    "verification_url": "http://example.com/transactions/verify/payment/550e8400-e29b-41d4-a716-446655440002",
    "payment_url": "http://example.com/pay",
    "transaction_id": "123456"
}
```

### Erreurs Possibles

- **400 Bad Request**: Si les données de la requête ne sont pas valides ou manquantes.
- **404 Not Found**: Si l'agrégateur ou la devise spécifiée n'existe pas.
- **500 Internal Server Error**: En cas d'erreur inattendue sur le serveur.

---

### Étape 2 : Vérification de la Transaction

Après avoir créé une transaction de paiement, il est crucial de vérifier son statut. Le paiement ne signifie pas nécessairement qu'il a été effectué avec succès. Vous devez toujours vérifier la transaction.

**Endpoint**: `GET /transactions/verify/{type}/{transaction:uuid}`

Cette route permet de vérifier l'état de la transaction créée.

### Exemple de Vérification

Pour vérifier une transaction, utilisez l'UUID retourné lors de la création de la transaction.

```http
GET /transactions/verify/payment/550e8400-e29b-41d4-a716-446655440002
```

### Routes Associées

- **Procéder à la transaction**: `GET /transactions/proceed/{type}/{transaction:uuid}`
- **Obtenir les détails d'une transaction**: `GET /transactions/{transaction:uuid}` 
