le template topbar.tpl générique pour toutes les autres pages de l'application. Voici les caractéristiques :
Structure de la barre générique :
1. Partie gauche

Bouton retour : Navigation automatique vers la page précédente
Animation fade : Transition fluide
Titre dynamique :

Utilise $title si défini dans le contrôleur
Sinon utilise "AppMobTimeTouch" par défaut



2. Partie droite

Actions contextuelles (optionnelles) :

Système flexible pour ajouter des boutons spécifiques
Définis via $page_actions dans le contrôleur PHP


Menu hamburger : Toujours disponible pour accès au menu latéral

Flexibilité du système :
1. Titre personnalisé
php// Dans le contrôleur PHP
$title = "TimeclockRecords"; // Sera traduit
2. Actions personnalisées
php// Dans le contrôleur PHP
$page_actions = array(
    array(
        'icon' => 'md-add',
        'onclick' => 'addNewRecord()'
    ),
    array(
        'icon' => 'md-refresh',
        'onclick' => 'refreshData()'
    )
);
Différences avec topbar-home.tpl :
1. Navigation

Bouton retour au lieu du logo
Pas d'indicateur de statut (spécifique à l'accueil)

2. Flexibilité

Titre configurable selon la page
Actions contextuelles selon les besoins

3. Usage

Pages secondaires : Liste des enregistrements, détails, paramètres
Formulaires : Création/modification d'enregistrements
Rapports : Vues statistiques

Exemples d'utilisation :
Page de liste des enregistrements :
php$title = "MyTimeclockRecords";
$page_actions = array(
    array('icon' => 'md-filter-list', 'onclick' => 'showFilters()')
);
Page de détail d'un enregistrement :
php$title = "TimeclockRecordDetails";
$page_actions = array(
    array('icon' => 'md-edit', 'onclick' => 'editRecord()'),
    array('icon' => 'md-delete', 'onclick' => 'deleteRecord()')
);
Cette structure offre une navigation cohérente tout en permettant une personnalisation selon le contexte de chaque page.