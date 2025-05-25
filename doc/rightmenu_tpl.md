le template rightmenu.tpl spécialement adapté pour l'application de time tracking. Voici la structure du menu :
Organisation du menu :
1. Section Utilisateur

Mon Compte : Profil utilisateur
Mes Heures : Enregistrements personnels
Résumés : Résumés hebdomadaires/mensuels

2. Section Management (selon les droits)

Gestion Équipe : Pour les managers (readall)
Validation : Validation des heures (validate) avec badge de notification
Rapports : Exports et statistiques (export)

3. Section Configuration

Préférences : Paramètres utilisateur
À propos : Informations sur l'application

4. Section PWA (iOS uniquement)

Installation : Installer comme app
Activation PWA : Mode hors-ligne

5. Déconnexion

Fermer Session : Logout sécurisé

Fonctionnalités clés :
1. Gestion des droits utilisateur
php<?php if ($user->rights->appmobtimetouch->timeclock->readall): ?>
    <!-- Fonctions management -->
<?php endif; ?>
2. Badges de notification
php<?php if (isset($pending_validation_count) && $pending_validation_count > 0): ?>
    <ons-badge style="background-color: #f44336;"><?php echo $pending_validation_count; ?></ons-badge>
<?php endif; ?>
3. Icônes colorées

Codes couleur spécifiques pour chaque section
Cohérence visuelle avec les onglets
Compréhension immédiate des fonctions

4. Structure responsive

Layout OnsenUI avec left/center/right
Séparateurs visuels entre les sections
Espacement optimisé pour mobile