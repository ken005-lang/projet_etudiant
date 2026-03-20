<x-mail::message>
# {{ $mode === 'groupe' ? 'Récupération de Code ID' : 'Réinitialisation de mot de passe' }}

Bonjour **{{ $user->name }}**,

Nous avons reçu une demande de récupération pour votre compte associé à **{{ $user->email }}**.

<x-mail::button :url="$resetUrl" color="primary">
{{ $mode === 'groupe' ? 'Gérer mon Code ID' : 'Réinitialiser mon mot de passe' }}
</x-mail::button>

Ce lien est valable pendant **{{ $expiresIn }} minutes**.

@if($mode === 'groupe')
**Note pour le groupe :** Ce lien vous permettra soit de modifier votre Code ID (si vous connaissez l'ancien), soit de soumettre une demande de récupération administrative si vous l'avez totalement perdu.
@endif

---

**Ce n'est pas vous ?**
Si vous n'avez pas demandé cette action, ignorez cet email. Votre compte reste sécurisé.

Cordialement,
**L'équipe {{ config('app.name') }}**
</x-mail::message>
