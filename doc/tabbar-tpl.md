le template tabbar.tpl adapté pour l'application de time tracking. Voici la structure de navigation :
Onglets de navigation :
1. Today (Accueil)

Icône : fa-home (maison)
Fonction : Page d'accueil avec statut actuel et boutons Clock In/Out
Page : home.php

2. My Records (Mes Enregistrements)

Icône : fa-clock-o (horloge)
Fonction : Liste des enregistrements de l'utilisateur
Action : loadMyRecords()

3. Management OU Summary (selon les droits)

Pour les managers (readall permission) :

Icône : fa-users (utilisateurs)
Label : "Management"
Fonction : Gestion des équipes, validation


Pour les utilisateurs normaux :

Icône : fa-bar-chart (graphique)
Label : "Summary"
Fonction : Résumés personnels, statistiques



4. Settings (Paramètres)

Icône : fa-cog (engrenage)
Fonction : Configuration utilisateur, préférences
Action : loadSettings()

Fonctionnalités clés :
1. Gestion des droits
php<?php if ($user->rights->appmobtimetouch->timeclock->readall): ?>
    <!-- Onglet Management pour managers -->
<?php else: ?>
    <!-- Onglet Summary pour utilisateurs -->
<?php endif; ?>
2. Navigation JavaScript

Fonctions dédiées pour chaque onglet
Chargement dynamique des contenus
Système de badges (prêt pour notifications)

3. Adaptation mobile

Position bottom : Barre en bas d'écran (standard mobile)
Icônes FontAwesome : Cohérent avec l'exemple fourni
Labels courts : Optimisés pour petit écran



Cette structure offre une navigation intuitive adaptée aux besoins du time tracking tout en conservant la simplicité de l'interface mobile.