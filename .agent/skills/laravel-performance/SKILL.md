---
name: laravel-performance
description: >
  Guide complet d'optimisation des performances pour applications Laravel (10–12) sur stack
  PHP 8.4 / MySQL / Redis / Nginx / WSL2. À utiliser dès que l'utilisateur mentionne un site
  lent, des pages qui chargent longtemps, des requêtes SQL lentes, un temps de réponse élevé,
  ou demande comment optimiser / accélérer son application Laravel. Couvre le diagnostic,
  les quick wins, le cache, les requêtes N+1, les assets Vite, la configuration serveur et Redis.
---

# Laravel Performance — Guide d'optimisation

## Stack cible
- **PHP** : 8.4
- **Framework** : Laravel 10 / 11 / 12
- **Base de données** : MySQL (+ SQLite en dev)
- **Cache / Sessions** : Redis (ou file en dev)
- **Serveur** : Nginx
- **Environnement dev** : Windows 11 + WSL2
- **WebSockets** : Laravel Reverb

---

## ÉTAPE 0 — Diagnostic avant tout

Ne jamais optimiser à l'aveugle. Commencer par identifier la source réelle du problème.

### Installer Laravel Debugbar (dev uniquement)

```bash
composer require barryvdh/laravel-debugbar --dev
```

S'active automatiquement quand `APP_DEBUG=true`. Affiche en bas de page :
- ⏱ Temps total de la requête
- 🗄 Nombre et durée des requêtes SQL
- 📦 Modèles Eloquent chargés
- 🔁 Vues Blade rendues
- 🧠 Utilisation mémoire

### Activer la détection automatique des N+1

Dans `app/Providers/AppServiceProvider.php` → méthode `boot()` :

```php
use Illuminate\Database\Eloquent\Model;

public function boot(): void
{
    // Lance une exception dès qu'une relation lazy est chargée
    Model::preventLazyLoading(! app()->isProduction());
}
```

---

## ÉTAPE 1 — WSL2 : piège n°1 en dev (gain majeur)

> **C'est la cause la plus fréquente de lenteur en dev Laravel sur Windows.**

Si le projet est dans `/mnt/c/Users/...`, les I/O disque sont **5 à 10× plus lents** qu'en filesystem Linux natif. Tout est affecté : `php artisan serve`, `composer`, `npm`, accès aux fichiers Blade, etc.

### Vérifier l'emplacement du projet

```bash
pwd
# Si résultat commence par /mnt/c/ → déplacer le projet
```

### Migrer dans le filesystem WSL2

```bash
# Bon emplacement
/home/jean/projets/mon-site

# Mauvais emplacement
/mnt/c/Users/Jean/projets/mon-site
```

```bash
# Migration
cp -r /mnt/c/Users/Jean/projets/mon-site ~/projets/mon-site
cd ~/projets/mon-site
composer install
php artisan key:generate
```

---

## ÉTAPE 2 — Requêtes N+1 (cause la plus fréquente en prod)

### Le problème

```php
// ❌ Mauvais — 1 requête pour les posts + 1 requête par post pour l'auteur
$posts = Post::all();
// Dans la vue : $post->user->name ← requête SQL à chaque itération
```

### La solution : Eager Loading

```php
// ✅ Correct — 2 requêtes au total, peu importe le nombre de posts
$posts = Post::with('user')->get();

// ✅ Relations imbriquées
$posts = Post::with(['user', 'user.role', 'comments.author'])->get();

// ✅ Sélectionner uniquement les colonnes nécessaires
$posts = Post::with('user:id,name,email')->get();
```

### Lazy Eager Loading (quand la relation est conditionnelle)

```php
$posts = Post::all();

if ($needsAuthors) {
    $posts->load('user');
}
```

---

## ÉTAPE 3 — Optimisations des requêtes Eloquent

### Sélectionner uniquement les colonnes nécessaires

```php
// ❌ Charge toutes les colonnes inutilement
$users = User::all();

// ✅ Seulement ce dont on a besoin
$users = User::select('id', 'name', 'email')->get();
```

### Pagination obligatoire pour les grandes listes

```php
// ❌ Charge tout en mémoire
$posts = Post::all();

// ✅ Paginer
$posts = Post::with('user')->paginate(20);

// ✅ Cursor pagination (plus performant pour grands datasets)
$posts = Post::with('user')->cursorPaginate(20);
```

### Chunk pour les traitements en masse

```php
// ❌ Risque d'exploser la mémoire
$users = User::all();
foreach ($users as $user) { /* traitement */ }

// ✅ Traitement par lots
User::chunk(200, function ($users) {
    foreach ($users as $user) { /* traitement */ }
});
```

### Index MySQL — colonnes fréquemment filtrées

Dans les migrations :

```php
// ✅ Index simple
$table->index('status');
$table->index('created_at');

// ✅ Index composite (respecter l'ordre des clauses WHERE)
$table->index(['user_id', 'status']);

// ✅ Index sur colonnes de foreign key (toujours)
$table->foreignId('user_id')->constrained()->index();
```

---

## ÉTAPE 4 — Cache Laravel

### Configuration `.env`

```env
# En dev (sans Redis)
CACHE_DRIVER=file
SESSION_DRIVER=file

# En prod (avec Redis — recommandé)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### Caches Artisan (à lancer en prod ou staging)

```bash
# Cache la configuration (évite de parser .env à chaque requête)
php artisan config:cache

# Cache les routes
php artisan route:cache

# Cache les vues Blade compilées
php artisan view:cache

# Optimise l'autoloader Composer
composer install --optimize-autoloader --no-dev

# Tout en une commande
php artisan optimize
```

> ⚠️ En dev, `config:cache` peut masquer les changements `.env`.
> Utiliser `php artisan config:clear` ou `php artisan optimize:clear` pour nettoyer.

### Mettre des données en cache dans le code

```php
use Illuminate\Support\Facades\Cache;

// Cache pendant 60 minutes
$posts = Cache::remember('posts.all', 3600, function () {
    return Post::with('user')->get();
});

// Cache permanent (jusqu'à suppression manuelle)
$config = Cache::rememberForever('app.config', fn() => Config::all());

// Invalider le cache après modification
Cache::forget('posts.all');

// Tags (nécessite Redis ou Memcached)
Cache::tags(['posts', 'users'])->remember('posts.featured', 3600, fn() => /* ... */);
Cache::tags(['posts'])->flush(); // Invalide tout ce qui est tagué 'posts'
```

---

## ÉTAPE 5 — Redis (recommandé en prod)

### Installation sur WSL2

```bash
sudo apt update && sudo apt install redis-server -y
sudo service redis-server start

# Vérifier
redis-cli ping   # → PONG
```

### Laravel — `config/database.php` (déjà configuré par défaut)

```php
'redis' => [
    'default' => [
        'host'     => env('REDIS_HOST', '127.0.0.1'),
        'port'     => env('REDIS_PORT', 6379),
        'database' => env('REDIS_DB', 0),
    ],
    'cache' => [
        'host'     => env('REDIS_HOST', '127.0.0.1'),
        'port'     => env('REDIS_PORT', 6379),
        'database' => env('REDIS_CACHE_DB', 1), // DB séparée pour le cache
    ],
],
```

### `.env` avec Redis

```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
BROADCAST_CONNECTION=reverb
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
```

---

## ÉTAPE 6 — Queues (tâches asynchrones)

Décharger du cycle requête/réponse tout ce qui n'est pas immédiat :

- Envoi d'emails
- Notifications
- Génération de PDF / exports
- Appels API externes
- Traitements d'images

```php
// ❌ Synchrone — bloque la réponse
Mail::to($user)->send(new WelcomeMail($user));

// ✅ Asynchrone — envoi dans une queue
Mail::to($user)->queue(new WelcomeMail($user));

// ✅ Job dédié
ProcessPayment::dispatch($order)->onQueue('payments');
```

```bash
# Lancer le worker (en dev)
php artisan queue:work

# Avec Redis, supervisor en prod
php artisan queue:work redis --tries=3 --timeout=60
```

---

## ÉTAPE 7 — Assets Vite

```bash
# ✅ En dev — HMR actif, rechargement instantané
npm run dev

# ✅ En prod — build optimisé (minification, tree-shaking)
npm run build
```

```php
// Dans les layouts Blade — utiliser @vite, jamais de liens manuels
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

### Optimisations Vite supplémentaires (`vite.config.js`)

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                // Séparation des vendors pour meilleur cache navigateur
                manualChunks: {
                    vendor: ['axios', 'alpinejs'],
                },
            },
        },
    },
});
```

---

## ÉTAPE 8 — Nginx : configuration optimisée pour Laravel

```nginx
server {
    listen 80;
    server_name monsite.com;
    root /var/www/monsite/public;
    index index.php;

    # Gzip
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml;
    gzip_min_length 1000;

    # Cache navigateur pour les assets statiques
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff2?)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Laravel — toutes les requêtes vers index.php
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    # Bloquer l'accès aux fichiers sensibles
    location ~ /\.(env|git) {
        deny all;
    }
}
```

---

## ÉTAPE 9 — OPcache PHP (prod)

OPcache précompile les fichiers PHP en bytecode → supprime la recompilation à chaque requête.

Dans `/etc/php/8.4/fpm/conf.d/10-opcache.ini` :

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.revalidate_freq=0       ; 0 en prod (jamais revalider)
opcache.save_comments=1
opcache.enable_cli=0
```

---

## Checklist rapide — Ordre de priorité

```
DIAGNOSTIC
□ Laravel Debugbar installé
□ Model::preventLazyLoading() activé en dev

WSL2 (dev)
□ Projet dans ~/projets/ et non dans /mnt/c/

BASE DE DONNÉES
□ Eager loading (with()) sur toutes les relations
□ Pagination sur toutes les listes
□ Index sur colonnes filtrées/triées fréquemment
□ select() pour limiter les colonnes chargées

CACHE
□ CACHE_DRIVER=redis (ou file en dev)
□ php artisan optimize lancé
□ Données coûteuses mises en cache avec Cache::remember()

QUEUES
□ Emails, notifications, jobs lourds en queue
□ Worker actif (queue:work)

ASSETS
□ npm run dev actif en dev (HMR)
□ npm run build pour la prod

SERVEUR (prod)
□ OPcache activé
□ Gzip Nginx activé
□ Cache navigateur sur assets statiques
□ composer install --optimize-autoloader --no-dev
```

---

## Référence commandes utiles

```bash
# Diagnostic
php artisan about                    # Infos app + config active
php artisan telescope:install        # Monitoring avancé (optionnel)

# Cache
php artisan optimize                 # Config + route + view cache
php artisan optimize:clear           # Vide tous les caches

# Queues
php artisan queue:work               # Lance le worker
php artisan queue:failed             # Liste les jobs échoués
php artisan queue:retry all          # Rejoue les jobs échoués

# Base de données
php artisan db:show                  # Infos connexion DB
php artisan model:show NomDuModele   # Relations, attributs, scopes
```
