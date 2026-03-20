<x-mail::message>
# Réinitialisation de mot de passe

Bonjour **{{ $user->name }}**,

Nous avons reçu une demande pour réinitialiser le mot de passe de votre compte ({{ $user->username }}).

Voici votre code de vérification à saisir sur la page :

<div style="text-align: center; margin: 20px 0;">
    <span style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #ff6600; padding: 15px 25px; border: 2px dashed #ff6600; border-radius: 8px; background-color: #fff5eb;">
        {{ $code }}
    </span>
</div>

Ce code est valable pendant **{{ $expiresIn }} minutes**. Ne le partagez avec personne.

---

**Ce n'est pas vous ?**
Si vous n'avez pas demandé cette action, ignorez cet email. Votre compte reste sécurisé.

Cordialement,
**L'équipe {{ config('app.name') }}**
</x-mail::message>
