# topbar-home.tpl 
template topbar-home.tpl spécifique à la page d'accueil du time tracking. Voici les caractéristiques :
Éléments de la barre de navigation :
1. Partie gauche

Titre : "AppMobTimeTouch" (localisé)
Logo : Icône d'horloge (clock-icon.png)
Style cohérent avec l'exemple fourni

2. Partie droite

Indicateur de statut :

Point vert (●) si utilisateur pointé
Point gris (○) si utilisateur non pointé
Tooltip au survol pour clarifier le statut


Menu hamburger : Accès au menu latéral

Spécificités par rapport à l'exemple :
1. Adaptation du contenu

Remplace "appMobileCommandes" par "AppMobTimeTouch"
Logo d'horloge au lieu de boîte ouverte
Indicateur de statut spécifique au time tracking

2. Fonctionnalité ajoutée

Indicateur visuel du statut de pointage dans la barre
Feedback immédiat pour l'utilisateur
Couleurs distinctives (vert/gris) selon le statut

3. Structure identique

Modifier "material" pour le style
Classes left/right pour le positionnement
Splitter toggle pour le menu latéral

Données utilisées :

$is_clocked_in : Variable booléenne du statut
$langs->trans() : Traductions localisées

Cette barre de navigation donne un feedback visuel immédiat sur le statut de pointage, ce qui est essentiel pour une application de time tracking.