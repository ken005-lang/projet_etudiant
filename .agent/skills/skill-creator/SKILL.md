---
description: Guide pour concevoir et générer de nouvelles compétences Antigravity claires, cohérentes et directement utilisables.
---

# Créateur de Compétences Antigravity

Ce guide vous aide à créer de nouvelles compétences de haute qualité pour étendre les capacités d'Antigravity. Utilisez ce processus à chaque fois que vous devez concevoir une nouvelle compétence.

## 1. Définition de la Mission

Avant de créer les fichiers, définissez clairement l'objectif de la compétence.
- **Quel est le problème spécifique à résoudre ?** (ex: "Générer des tests unitaires", "Optimiser le CSS", etc.)
- **Quand cette compétence doit-elle être activée ?**

## 2. Structure du Dossier

Créez un dossier dédié dans le répertoire approprié :
- **Global** (recommandé pour cet usage) : `~/.gemini/antigravity/skills/<nom-de-la-competence>/`
- **Local** (spécifique au projet) : `.agent/skills/<nom-de-la-competence>/`

Fichier requis :
- `SKILL.md` : Contient toutes les instructions.

Dossiers optionnels :
- `scripts/` : Pour les outils d'automatisation.
- `examples/` : Pour des modèles de référence.
- `resources/` : Pour des templates.

## 3. Rédaction du `SKILL.md`

Le fichier `SKILL.md` doit respecter ce format :

### Frontmatter (Obligatoire)
```yaml
---
description: [Description concise à la troisième personne commençant par un verbe d'action. Doit inclure des mots-clés pour l'activation.]
---
```

### Corps du document
Organisez les instructions de manière logique :
1. **Objectif** : Résumé de ce que fait la compétence.
2. **Prérequis** : Dépendances ou outils nécessaires.
3. **Flux de travail (Workflow)** : Étapes numérotées claires.
4. **Arbre de décision** : "Si X, alors faire Y".
5. **Utilisation des scripts** : Comment appeler les scripts inclus (utiliser `--help`).
6. **Validation** : Comment vérifier que la tâche est terminée avec succès.

## 4. Meilleures Pratiques

- **Spécificité** : Évitez les compétences trop vagues. Préférez plusieurs petites compétences ciblées.
- **Langue** : Toutes les instructions du skill-creator et des compétences qu'il génère doivent être en français, comme demandé par l'utilisateur.
- **Modularité** : Utilisez des scripts pour les tâches complexes au lieu de longues instructions textuelles.

## 5. Processus de Création

1. Analysez le besoin.
2. Déterminez si un script est nécessaire.
3. Rédigez le `SKILL.md` en suivant le modèle ci-dessus.
4. Testez la compétence avec une tâche factice.
