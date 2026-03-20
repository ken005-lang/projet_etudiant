<x-mail::message>
# 🚨 Demande de Récupération d'Identité

Une demande de récupération manuelle du Code ID a été soumise par un groupe.

**Détails du Groupe :**
- **Nom du groupe :** {{ $data['group_name'] }}
- **Chef de groupe :** {{ $data['chef_nom'] }} {{ $data['chef_prenom'] }}
- **Filière :** {{ $data['filiere'] }}
- **Niveau :** {{ $data['niveau'] }}
- **Email de contact :** {{ $data['email'] }}

**Informations techniques :**
- **Adresse IP :** {{ $ip }}
- **Date :** {{ now()->format('d/m/Y H:i') }}

Veuillez vérifier ces informations dans la base de données avant de renvoyer manuellement le Code ID à l'adresse fournie.

Cordialement,
**Système de Sécurité ITES**
</x-mail::message>
