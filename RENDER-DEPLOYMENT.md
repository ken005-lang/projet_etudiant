# 🚀 Déploiement sur Render.com

## Structure des fichiers

Tous les fichiers nécessaires pour déployer sur Render sont en place :

```
project/
├── Dockerfile                        ← Image Docker PHP 8.4 + Nginx
├── .dockerignore                     ← Fichiers exclus du build
├── render.yaml                       ← Blueprint Infrastructure-as-Code
├── conf/
│   ├── nginx/
│   │   └── nginx-site.conf          ← Config Nginx personnalisée
│   └── supervisor/
│       └── supervisord.conf          ← Config Supervisor (services)
├── scripts/
│   └── 00-laravel-deploy.sh         ← Script de démarrage (migrations, cache...)
├── .env.testing                      ← Variables pout les tests
└── app/Providers/AppServiceProvider ← Force HTTPS en production
```

---

## Étape 1 — Créer un compte Render

1. Aller sur https://render.com
2. Cliquer **"Get Started"** → S'inscrire avec GitHub (recommandé)
3. Autoriser Render à accéder à vos repositories

---

## Étape 2 — Pousser le code vers GitHub

```bash
# S'assurer que tous les fichiers sont en place
git add .
git commit -m "Add Render configuration"
git push origin main
```

---

## Étape 3 — Déployer via Blueprint (RECOMMANDÉ)

### Via le Dashboard Render

1. **Créer une Blueprint** :
   - Dashboard Render → **New** → **Blueprint**
   - **Connect Repository** : Sélectionner votre repo GitHub
   - Render détecte automatiquement `render.yaml` ✓

2. **Appliquer la Blueprint** :
   - Render crée automatiquement :
     - **Database PostgreSQL** (base de données)
     - **Redis Key Value** (cache & sessions)
     - **Web Service** (application)
     - **Worker** (queues)
     - **Cron Job** (scheduler)

3. **Définir les secrets** :
   - Chaque service → **Environment** → **Add Secret Variable**
   
   Secrets à ajouter :
   ```
   APP_KEY              = base64:... (généré localement avec php artisan key:generate --show)
   MAIL_HOST            = smtp.mailgun.org (ou votre provider)
   MAIL_PASSWORD        = votre_clé_api
   ```

---

## Étape 4 — Générer APP_KEY

```bash
# Sur votre machine, dans le projet
php artisan key:generate --show
# → Copier la valeur (commence par "base64:")
```

Puis dans le Dashboard Render :
- Service **mon-app** → **Environment** → **Add Secret Variable**
- Nom : `APP_KEY`
- Valeur : la clé copiée ci-dessus

---

## Étape 5 — Vérifier le déploiement

1. Attendre le build initial (5-10 minutes)

2. Onglet **Logs** :
   ```
   ✅ "📦 Installation des dépendances Composer..."
   ✅ "⚡ Optimisation de la configuration..."
   ✅ "🗄️  Exécution des migrations..."
   ✅ "✅ Application prête !"
   ```

3. Onglet **Events** : Voir l'historique des déploiements

4. Tester l'application :
   - https://mon-app.onrender.com (URL générée automatiquement)
   - https://mon-app.onrender.com/up → doit retourner **HTTP 200**

5. Vérifier les pages principales :
   - CSS/JS chargés (pas d'erreurs mixed content)
   - Pas d'erreurs 500 en production

---

## Étape 6 — Configurer un domaine custom (Optionnel)

1. Dashboard Render → Votre service **mon-app**
2. **Settings** → **Custom Domains** → **Add Custom Domain**
3. Entrer votre domaine : `votredomaine.com` ou `app.votredomaine.com`

4. **Configurer le DNS** chez votre registrar :

   **Pour un sous-domaine** (`app.votredomaine.com`) :
   ```
   CNAME  app  →  mon-app.onrender.com
   ```

   **Pour domaine apex** (`votredomaine.com`) :
   ```
   A    @  →  216.24.57.1  (fournie par Render)
   AAAA @  →  [IPv6 fournie par Render]
   ```

5. Attendre la propagation DNS (quelques minutes à 48h)

6. Mettre à jour **APP_URL** en production :
   - Service → Environment → Modifier `APP_URL`
   - Valeur : `https://votredomaine.com`
   - Redéployer (Manual Deploy)

---

## Étape 7 — CI/CD GitHub Actions (Optionnel)

Les tests s'exécutent automatiquement sur chaque push vers `main`.

### Pour déclencher le redéploiement Render après les tests :

1. **Récupérer le Deploy Hook** :
   - Dashboard Render → Votre service → **Settings**
   - ** Deploy Hook** → Copier l'URL

2. **Ajouter dans GitHub** :
   - Repo GitHub → **Settings** → **Secrets and variables** → **Actions**
   - **New secret** :
     - Nom : `RENDER_DEPLOY_HOOK_URL`
     - Valeur : l'URL du hook copiée ci-dessus

---

## Commandes utiles

### Exécuter des commandes artisan via le Shell Render

**Dashboard Render → Service → Shell** (services payants uniquement)

Exemples :
```bash
# Voir le statut des migrations
php artisan migrate:status

# Clear caches
php artisan cache:clear
php artisan config:clear

# Vérifier les jobs échoués
php artisan queue:failed

# Tinker (REPL)
php artisan tinker
```

### Voir les logs

```
Dashboard → Logs → Filtrer par niveau (info, warn, error)
```

---

## Tarifs Render (Mode 2026)

| Service | Free | Starter ($7/mois) | Standard ($25/mois) |
|---------|------|-------------------|---------------------|
| **Web Service** | ✅ (dors après 15 min) | ✅ Always-on | ✅ Always-on + autoscale |
| **PostgreSQL** | ✅ (30 jours, 1 GB) | ✅ Payant | ✅ Payant |
| **Redis** | ✅ (ephémère) | ✅ Payant | ✅ Payant |
| **Worker** | ❌ | ✅ | ✅ |
| **Cron Jobs** | ❌ | ✅ | ✅ |
| **Domaine custom** | ✅ | ✅ | ✅ |
| **SSL automatique** | ✅ | ✅ | ✅ |

⚠️ **Limitations Free Tier** :
- Web Service s'éteint après 15 min d'inactivité
- PostgreSQL supprimé après 30 jours
- Redis ephémère (données perdues au redémarrage)
- Pas de workers/cron jobs

---

## Troubleshooting

### ❌ "SQLSTATE[...]" — Erreur de connexion DB

**Cause** : Variables DB_HOST, DB_PASSWORD, etc. manquantes ou incorrectes

**Solution** :
1. Vérifier dans Dashboard → Service → Environment
2. Les variables doivent être liées à la DB PostgreSQL (dans render.yaml)
3. Redéployer (Manual Deploy) après changement

### ❌ "No application encryption key" — APP_KEY manquant

**Solution** :
1. Générer localement : `php artisan key:generate --show`
2. Ajouter en secret dans le Dashboard
3. Redéployer

### ❌ Logs vides / Service n'a pas démarre

**Cause** : Erreur lors du build Docker

**Solution** :
1. Vérifier l'onglet **Build** pour les erreurs
2. Vérifier que Dockerfile et .dockerignore sont corrects
3. Tester localement : `docker build -t test .`

### ❌ Assets (CSS/JS) manquent / Mixed content errors

**Cause** : HTTPS non forcé

**Solution** :
- AppServiceProvider.php a `URL::forceScheme('https')` en production ✓
- Vérifier que APP_ENV=production en variables d'env

---

## Documentation

- **Render Docs** : https://render.com/docs/deploy-php-laravel-docker
- **Laravel docs** : https://laravel.com/docs
- **PostgreSQL** : https://www.postgresql.org/docs/16/

---

**Questions ?** Consultez les logs du service pour plus de détails.
