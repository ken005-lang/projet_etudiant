<x-mail::message>
# Sécurité : Identifiants modifiés

Bonjour **{{ $user->name }}**,

Le mot de passe ou le Code ID de votre compte **{{ $user->email ?? $user->username }}** a été modifié avec succès.

**Opération effectuée :**
- **Date :** {{ now()->format('d/m/Y à H:i') }}
- **Adresse IP :** {{ $ip }}
- **Statut :** Toutes les autres sessions ont été déconnectées par mesure de sécurité.

---

## ⚠️ Ce n'est pas vous ?
Si vous n'êtes pas à l'origine de ce changement, votre compte est peut-être compromis. Veuillez contacter immédiatement l'administration ou utiliser le lien de récupération sur la page de connexion.

Cordialement,
**L'équipe {{ config('app.name') }}**
</x-mail::message>
