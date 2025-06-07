Quand tu t'adresse à moi , tu utiliseras le vous de politesse.
Ce projet consiste à créer un module dolibarr permettant aux salariers de pointer leur temps de présence. Ce module devra être utilisable depuis une smartphone et utiliser le framework onsenui pour s'intégrer dans les autres applications développé avec ce même framework.


le module que nous allons construire doit dans la mesure du possible
utiliser les fonctionnalités standards de Dolibarr version  16.0.4
Il ne faut pas apporter de  modification du code source du core de dolibarr .
Respecter la logique de configuration et d'implémentation de Dolibarr.
Gérer les langues avec les fonctions $langs->trans("AppmobsalesordersSmartphone").

Pour l'affichage des prix tu utiliseras l'affichage avec les devis comme dans l'exemple suivant : price($line->price, 0, $langs, 1, -1, -1, $currency)

Pour l'affichage mobile utilise la technologie onsenui en respectant strictement la structure existante de l'application notement avec les fichiers index.php, home.php, home.tpl, topbar-home.tpl et topbar.tpl



