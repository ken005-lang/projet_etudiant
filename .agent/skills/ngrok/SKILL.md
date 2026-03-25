---
name: ngrok
description: >
  Guide complet de ngrok pour exposer un serveur local Laravel (sous Docker + WSL2) sur
  Internet via une URL publique temporaire ou permanente. Utiliser ce skill dès que
  l'utilisateur mentionne ngrok, tunnel, webhook, URL publique, tester en ligne, partager
  son site local, OAuth callback, Stripe/GitHub/PayPal webhook, domaine public temporaire,
  ou demande comment rendre son site local accessible depuis l'extérieur — même sans VPS.
---

# ngrok — Guide complet pour Laravel + Docker + WSL2

## Qu'est-ce que ngrok ?

ngrok est un **tunnel réseau sécurisé** qui expose ton serveur local sur Internet :

```
Visiteur externe
    ↓ HTTPS
ngrok (serveurs mondiaux)
    ↓ tunnel chiffré
Ton WSL2 (localhost:80)
    ↓
Docker → Nginx → Laravel
```

Sans VPS, sans DNS, sans ouvrir de ports sur ta box.

## Cas d'usage principaux

| Besoin | Exemple |
|---|---|
| **Tester des webhooks** | Stripe, GitHub, PayPal → ton app locale |
| **OAuth callbacks** | Google/GitHub refusent `localhost` comme redirect URI |
| **Démo rapide** | Montrer le site à un client sans déployer |
| **Tests mobile** | Accéder depuis ton téléphone sur le vrai réseau |
| **Laravel Reverb** | Tester les WebSockets depuis l'extérieur |

---

## INSTALLATION (WSL2 — choisir Linux, pas Windows)

```bash
# Méthode apt (recommandée sur WSL2)
curl -sSL https://ngrok-agent.s3.amazonaws.com/ngrok.asc \
  | sudo tee /etc/apt/trusted.gpg.d/ngrok.asc >/dev/null \
  && echo "deb https://ngrok-agent.s3.amazonaws.com buster main" \
  | sudo tee /etc/apt/sources.list.d/ngrok.list \
  && sudo apt update && sudo apt install ngrok -y

# Vérifier
ngrok version
```

### Connecter son compte (obligatoire)

```bash
# Récupérer le token sur https://dashboard.ngrok.com/get-started/your-authtoken
ngrok config add-authtoken TON_TOKEN_ICI
```

---

## UTILISATION DE BASE

### Exposer Laravel (Docker sur le port 80)

```bash
# S'assurer que Docker tourne
docker compose up -d

# Lancer le tunnel
ngrok http 80
```

Résultat dans le terminal :
```
Session Status    online
Account           jean@example.com
Forwarding        https://a1b2c3d4.ngrok-free.app -> http://localhost:80
Web Interface     http://127.0.0.1:4040
```

### Mettre à jour `.env` Laravel

```env
APP_URL=https://a1b2c3d4.ngrok-free.app
```

```bash
docker compose exec app php artisan config:clear
```

### Dashboard d'inspection (indispensable pour les webhooks)

Ouvrir **`http://localhost:4040`** dans le navigateur →
inspecte chaque requête reçue, headers, body, et permet de **rejouer** une requête.

---

## CONFIGURATION LARAVEL OBLIGATOIRE

### TrustProxies (sinon les redirects et HTTPS cassent)

**Laravel 11+ (`bootstrap/app.php`)** :
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->trustProxies(at: '*');
})
```

**Laravel 10 (`app/Http/Middleware/TrustProxies.php`)** :
```php
protected $proxies = '*';
protected $headers =
    Request::HEADER_X_FORWARDED_FOR   |
    Request::HEADER_X_FORWARDED_HOST  |
    Request::HEADER_X_FORWARDED_PORT  |
    Request::HEADER_X_FORWARDED_PROTO;
```

### Header ngrok (requis depuis 2023)

ngrok envoie un header `ngrok-skip-browser-warning` pour bypasser la page d'avertissement.
Dans le middleware ou directement dans Nginx, accepter ce header :

```php
// app/Http/Middleware/NgrokMiddleware.php
namespace App\Http\Middleware;

use Closure;

class NgrokMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->headers->set('ngrok-skip-browser-warning', 'true');
        return $response;
    }
}
```

Ou plus simple, passer le header dans chaque requête côté client :
```
https://abc.ngrok-free.app/ma-route
Header: ngrok-skip-browser-warning: true
```

---

## CAS D'USAGE DÉTAILLÉS

Pour chaque cas d'usage avancé, consulter le fichier de référence correspondant :

- **Webhooks** (Stripe, GitHub, PayPal) → [references/webhooks.md](references/webhooks.md)
- **OAuth callbacks** (Google, GitHub Login) → [references/oauth.md](references/oauth.md)
- **Laravel Reverb / WebSockets** → [references/reverb.md](references/reverb.md)
- **ngrok.yml — config avancée** → [references/config.md](references/config.md)

---

## LIMITES DU PLAN GRATUIT

| Limite | Plan gratuit | Plan payant |
|---|---|---|
| URL fixe | ❌ change à chaque restart | ✅ domaine permanent |
| Tunnels simultanés | 1 | Plusieurs |
| Connexions/minute | Limitées (~40/min) | Illimitées |
| Domaine personnalisé | ❌ | ✅ |
| Expiration session | ~8h | Non |

> Sur le plan gratuit, l'URL change à chaque `ngrok http 80`.
> Il faut donc mettre à jour `APP_URL` dans `.env` à chaque fois.

---

## COMMANDES ESSENTIELLES

```bash
# Tunnel HTTP basique
ngrok http 80

# Tunnel sur un sous-domaine fixe (plan payant)
ngrok http --domain=monsite.ngrok.app 80

# Tunnel avec auth basique (protéger l'accès)
ngrok http --basic-auth="user:motdepasse" 80

# Tunnel HTTPS uniquement (refuser HTTP)
ngrok http --bind-tls=true 80

# Inspecter la config active
ngrok config check

# Voir les tunnels actifs (API locale)
curl http://localhost:4040/api/tunnels

# Lancer avec un fichier de config
ngrok start --config ~/.config/ngrok/ngrok.yml --all
```

---

## DÉPANNAGE FRÉQUENT

| Symptôme | Cause | Solution |
|---|---|---|
| Page d'avertissement ngrok | Header manquant | Ajouter `ngrok-skip-browser-warning: true` |
| `APP_URL` incorrect | `.env` non mis à jour | `APP_URL=https://xxx.ngrok-free.app` + `config:clear` |
| Boucle de redirection | TrustProxies absent | Configurer `trustProxies(at: '*')` |
| Webhook non reçu | Tunnel coupé | Relancer ngrok, mettre à jour l'URL webhook |
| `ERR_NGROK_108` | 1 seul tunnel gratuit | Arrêter le tunnel existant avant d'en créer un autre |
| Session expirée | Plan gratuit ~8h | Relancer ngrok + mettre à jour APP_URL |
| WebSocket refusé | Port ou proto incorrect | Voir [references/reverb.md](references/reverb.md) |

---

## CHECKLIST DÉMARRAGE RAPIDE

```
□ ngrok installé dans WSL2 (pas Windows)
□ Token authtoken configuré (ngrok config add-authtoken ...)
□ Docker compose up -d (site qui tourne en local)
□ ngrok http 80 lancé dans un terminal dédié
□ APP_URL mis à jour dans .env avec l'URL ngrok
□ php artisan config:clear exécuté
□ TrustProxies configuré dans Laravel
□ Dashboard http://localhost:4040 ouvert pour inspecter les requêtes
```
