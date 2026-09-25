# Rook Server - Product Enrichment Service

Backend Laravel pour la gestion et l'enrichissement automatique de produits grâce à l'IA avec **Rook Pipeline**.

---

## 🚀 Fonctionnalités Produit

- **Gestion des Produits (`/api/products`)** : API REST pour la création, consultation, mise à jour et suppression de produits.
- **Enrichissement IA Automatique** : À la création d'un produit, un job en arrière-plan (`SyncProductEnrichmentJob`) interroge le microservice **Rook Pipeline** pour compléter automatiquement le produit avec :
  - Titre SEO & Méta-description
  - Description détaillée (`long_description`)
  - Avantages du produit (`benefits`)
  - Spécifications techniques (`specifications`)
  - Conseils d'utilisation (`usage_tips`)
  - Mots-clés SEO (`seo_tags`)
  - Statut de traitement (`rook_status` : `processing`, `completed`, `failed`)

---

## 📁 Structure du projet

```
Rook-server/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── API/
│   │           └── Product/
│   │               └── ProductController.php    # Contrôleur API Produit
│   ├── Models/
│   │   └── Product.php                          # Modèle Eloquent Produit
│   ├── Services/
│   │   └── RookPipeline/
│   │       └── RookPipelineClient.php           # Client HTTP pour Rook Pipeline
│   └── Jobs/
│       └── SyncProductEnrichmentJob.php         # Job d'enrichissement asynchrone
├── database/
│   ├── factories/
│   │   └── ProductFactory.php                   # Factory Produit
│   └── migrations/
│       └── 2026_09_17_182158_create_products_table.php
├── routes/
│   └── api.php                                  # Routes API Produit
└── tests/
    ├── Feature/
    │   └── Product/
    │       └── ProductApiTest.php               # Tests Fonctionnels API Produit
    └── Unit/
        └── Jobs/
            └── SyncProductEnrichmentJobTest.php # Tests Unitaires Job Enrichissement
```

---

## 🚀 Installation & Démarrage

### 1. Configuration `.env`

Assurez-vous de configurer les variables d'environnement du service Rook Pipeline :

```env
ROOK_PIPELINE_BASE_URL=http://localhost:8000
ROOK_PIPELINE_TOKEN=votre_token_secret
```

### 2. Migration de la base de données

```bash
php artisan migrate
```

### 3. Lancement du serveur et du worker de queue

```bash
# Serveur web API
php artisan serve

# Worker pour le traitement de l'enrichissement
php artisan queue:work
```

---

## 📚 Endpoints API Produit

| Méthode | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/products` | Lister tous les produits |
| `POST` | `/api/products` | Créer un produit & lancer l'enrichissement IA |
| `GET` | `/api/products/{id}` | Consulter un produit enrichi |
| `PUT` | `/api/products/{id}` | Mettre à jour un produit |
| `DELETE` | `/api/products/{id}` | Supprimer un produit |
| `GET` | `/api/test-fastapi` | Tester la connexion au service Rook Pipeline (`/health`) |

---

## 🧪 Tests

```bash
# Lancer les tests produits
vendor/bin/phpunit tests/Feature/Product/ProductApiTest.php
vendor/bin/phpunit tests/Unit/Jobs/SyncProductEnrichmentJobTest.php
```

