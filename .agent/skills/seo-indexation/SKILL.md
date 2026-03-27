---
description: Guide pour optimiser le SEO technique, l'indexation Google (meta tags, sitemap, robots.txt, structure HTML) pour sites Web, particulièrement sur Render, statiques ou Node. À utiliser dès que l'utilisateur demande d'optimiser le SEO, l'indexation, de rajouter des meta tags, sitemaps ou robots.txt.
---

# 🧠 SKILL: SEO & INDEXATION WEB (Render / Sites statiques ou Node)

## 🎯 Objectif

Aider à rendre un site web :

* Indexable par Google
* Visible dans les résultats de recherche
* Optimisé pour le SEO technique de base

---

## 🔍 1. Vérifications de base

Toujours vérifier :

* Le site est accessible (status 200)
* Pas de `noindex` dans les meta tags
* Pas de blocage dans robots.txt

---

## 🏷️ 2. Ajouter les META tags essentiels

Dans `<head>` :

```html
<title>Nom du site - Description claire</title>
<meta name="description" content="Description du site en 150-160 caractères">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- SEO -->
<meta name="robots" content="index, follow">

<!-- Open Graph (réseaux sociaux) -->
<meta property="og:title" content="Nom du site">
<meta property="og:description" content="Description">
<meta property="og:type" content="website">
<meta property="og:url" content="https://projet-etudiant-1.onrender.com">
```

---

## 🧭 3. Créer un fichier robots.txt

À la racine du projet :

```
User-agent: *
Allow: /
Sitemap: https://projet-etudiant-1.onrender.com/sitemap.xml
```

---

## 🗺️ 4. Créer un sitemap.xml

À la racine :

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>https://projet-etudiant-1.onrender.com/</loc>
    <priority>1.0</priority>
  </url>
</urlset>
```

---

## ⚡ 5. Performance minimale

* Optimiser les images (taille < 200KB)
* Charger les scripts en `defer`
* Éviter les blocs JS inutiles

---

## 🔗 6. Structure HTML SEO

Toujours inclure :

```html
<h1>Titre principal</h1>
<h2>Sous-titre</h2>
<p>Contenu texte avec mots-clés</p>
```

---

## 🔎 7. Indexation Google

Étapes à suggérer à l’utilisateur :

1. Aller sur Google Search Console
2. Ajouter la propriété du site
3. Vérifier la propriété
4. Soumettre :
   https://projet-etudiant-1.onrender.com/sitemap.xml
5. Demander l’indexation de la page principale

---

## 🚨 8. Erreurs à éviter

* Page vide ou trop légère
* Aucun texte (Google ne comprend pas)
* JS-only sans SSR
* Temps de chargement lent
* URL non sécurisée (HTTP au lieu de HTTPS)

---

## 🤖 9. Bonnes pratiques pour IDE IA

Toujours :

* Ajouter SEO automatiquement dans les templates
* Vérifier la présence des balises
* Générer sitemap et robots.txt si absents
* Proposer du contenu texte optimisé
* Ajouter des titres pertinents

---

## 📈 10. Bonus (amélioration)

* Ajouter Google Analytics
* Ajouter des backlinks (GitHub, réseaux sociaux)
* Ajouter du contenu régulier

---

## 🧪 Commande type pour IDE IA

"Optimise ce projet pour le SEO et l’indexation Google (meta tags, sitemap, robots.txt, structure HTML)"
