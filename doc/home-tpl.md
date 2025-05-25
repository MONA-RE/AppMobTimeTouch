# home.tpl

le template home.tpl qui affiche une interface complète de time tracking avec OnsenUI. Voici les principales fonctionnalités :
Structure de l'interface :
1. Carte de statut principal

Icône et statut : Visuel clair "Pointé" ou "Non pointé"
Informations contextuelles :

Heure de début si pointé
Durée écoulée (mise à jour en temps réel)
Type de pointage (bureau, télétravail, etc.)


Bouton d'action principal :

Clock In (vert) si non pointé
Clock Out (rouge) si pointé



2. Résumé du jour

Heures travaillées aujourd'hui
Temps de pause pris
Affichage en colonnes avec icônes

3. Résumé hebdomadaire (si disponible)

Heures totales de la semaine
Jours travaillés
Statut du résumé
Alerte heures supplémentaires si applicable

4. Historique récent

Liste des 5 derniers enregistrements
Barre colorée selon le type de pointage
Informations : date, type, heures, durée
Cliquable pour voir les détails

Fonctionnalités JavaScript :
1. Mise à jour en temps réel

Timer automatique : Met à jour la durée toutes les minutes
Gestion du cycle de vie : Démarre/arrête selon le statut

2. Pull to refresh

Actualisation par glissement vers le bas
Messages localisés selon l'état

3. Fonctions placeholder

clockIn() : Prête pour implémentation
clockOut() : Prête pour implémentation
viewRecord() : Navigation vers les détails

Design adaptatif :
1. Cartes OnsenUI

Style cohérent avec l'exemple fourni
Espacement optimal pour mobile
Couleurs distinctives selon les statuts

2. Icônes MaterialDesign

Visuels clairs : play/stop, time, warning
Codes couleur : vert (actif), rouge (arrêt), orange (pause)

3. Responsive

Colonnes flexibles pour les résumés
Texte adaptatif selon la taille d'écran

Le template est prêt à fonctionner et n'attend plus que l'implémentation des fonctions Clock In/Out pour être pleinement opérationnel.
Souhaitez-vous que je continue avec les templates des barres de navigation (topbar-home.tpl, topbar.tpl, etc.) 