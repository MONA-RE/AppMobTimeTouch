# home.php 
le fichier home.php qui récupère toutes les données nécessaires pour l'affichage du time tracking. Voici ce qu'il fait :
Fonctionnalités principales :
1. Chargement de l'environnement Dolibarr

Inclusion des fichiers de base Dolibarr
Gestion des chemins multiples pour main.inc.php

2. Inclusion des classes nécessaires
php- TimeclockRecord : Gestion des enregistrements de temps
- TimeclockType : Types de pointage (bureau, télétravail, etc.)
- WeeklySummary : Résumés hebdomadaires
3. Récupération du statut actuel

Vérification si l'utilisateur est pointé : getActiveRecord()
Calcul de la durée actuelle : Si pointé, temps écoulé depuis le Clock In
Informations du pointage actif : Heure de début, type, etc.

4. Données du jour

Enregistrements terminés aujourd'hui
Total des heures travaillées
Total des pauses prises

5. Données de la semaine

Résumé hebdomadaire : Si existe
Heures totales de la semaine
Statut du résumé

6. Historique récent
Selon le paramètre view :

1 : Aujourd'hui seulement
2 : Cette semaine
3 : Les 30 derniers jours

7. Configuration

Types de pointage disponibles : Bureau, télétravail, mission, etc.
Type par défaut : Pour le Clock In

Variables préparées pour le template :

$is_clocked_in : Booléen, statut de pointage
$active_record : Enregistrement actif si pointé
$clock_in_time : Heure de pointage
$current_duration : Durée écoulée
$today_total_hours : Heures du jour
$weekly_summary : Résumé de la semaine
$recent_records : Historique
$timeclock_types : Types disponibles
$default_type_id : Type par défaut

Ces données seront utilisées dans le template home.tpl pour afficher le statut et les boutons Clock In/Out.

mis à jour le fichier home.php avec les améliorations suivantes :
Améliorations apportées :
1. Gestion des actions mobiles

✅ Actions clockin/clockout directement depuis l'interface mobile
✅ Validation des données (géolocalisation, permissions)
✅ Redirection après soumission pour éviter la re-soumission
✅ Messages de succès/erreur avec gestion des traductions

2. Optimisation des données

✅ Calcul en temps réel des durées actives
✅ Résumé hebdomadaire dynamique (existant ou calculé à la volée)
✅ Intégration du temps actif dans les totaux du jour et de la semaine
✅ Gestion des jours travaillés uniques

3. Configuration et sécurité

✅ Utilisation de TimeclockConfig pour les paramètres
✅ Vérification des permissions par action
✅ Validation de la géolocalisation si requise
✅ Contrôles de sécurité renforcés

4. Préparation pour JavaScript

✅ Variables JS configurées ($js_data) pour l'interface
✅ Tokens d'authentification pour les appels API
✅ Paramètres de configuration exposés au front-end

5. Fonctions helpers

✅ Formatage des durées amélioré
✅ Calculs des heures supplémentaires
✅ Alertes automatiques (dépassement seuils)

6. Gestion d'erreurs robuste

✅ Messages d'erreur traduits
✅ Fallbacks pour les données manquantes
✅ Logging des erreurs approprié