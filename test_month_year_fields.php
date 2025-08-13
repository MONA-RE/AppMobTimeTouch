<?php
/**
 * Test script pour vérifier les champs month/year séparés
 * MVP 44.2.2
 */

require_once '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockovertimepaid.class.php';

// Vérification module activé
if (!isModEnabled('appmobtimetouch')) {
    die('Module AppMobTimeTouch not enabled');
}

// Vérification droits
if (empty($user->rights->appmobtimetouch->overtimepaid->read)) {
    die('Insufficient permissions');
}

echo "<h2>Test MVP 44.2.2 - Champs month/year séparés</h2>";

// Test 1: Vérification de la structure
echo "<h3>Test 1: Structure de la classe</h3>";
$overtime = new TimeclockOvertimePaid($db);

$expected_fields = ['month', 'year'];
foreach ($expected_fields as $field) {
    if (isset($overtime->fields[$field])) {
        echo "✅ Champ '$field' défini dans \$fields<br>";
        echo "   - Type: " . $overtime->fields[$field]['type'] . "<br>";
        echo "   - Label: " . $overtime->fields[$field]['label'] . "<br>";
        if (isset($overtime->fields[$field]['arrayofkeyval'])) {
            echo "   - Options: " . count($overtime->fields[$field]['arrayofkeyval']) . " valeurs<br>";
        }
    } else {
        echo "❌ Champ '$field' manquant dans \$fields<br>";
    }
}

// Test 2: Propriétés de classe
echo "<h3>Test 2: Propriétés de classe</h3>";
foreach ($expected_fields as $field) {
    if (property_exists($overtime, $field)) {
        echo "✅ Propriété '$field' existe dans la classe<br>";
    } else {
        echo "❌ Propriété '$field' manquante dans la classe<br>";
    }
}

// Test 3: Vérification base de données
echo "<h3>Test 3: Structure base de données</h3>";
$sql_check = "SHOW COLUMNS FROM ".MAIN_DB_PREFIX."appmobtimetouch_timeclockovertimepaid";
$resql = $db->query($sql_check);

$db_fields = [];
if ($resql) {
    while ($obj = $db->fetch_object($resql)) {
        $db_fields[$obj->Field] = $obj->Type;
    }
    $db->free($resql);
}

foreach ($expected_fields as $field) {
    if (isset($db_fields[$field])) {
        echo "✅ Colonne '$field' existe en base (" . $db_fields[$field] . ")<br>";
    } else {
        echo "❌ Colonne '$field' manquante en base<br>";
    }
}

// Vérification que l'ancienne colonne n'existe plus
if (isset($db_fields['month_year'])) {
    echo "⚠️ Ancienne colonne 'month_year' existe encore (" . $db_fields['month_year'] . ")<br>";
} else {
    echo "✅ Ancienne colonne 'month_year' supprimée<br>";
}

// Test 4: Test de création (simulation)
echo "<h3>Test 4: Simulation création</h3>";
$test_overtime = new TimeclockOvertimePaid($db);
$test_overtime->fk_user = $user->id;
$test_overtime->month = 8; // Août
$test_overtime->year = 2025;
$test_overtime->hours_paid = 7.5;
$test_overtime->fk_user_manager = $user->id;
$test_overtime->note_private = 'Test MVP 44.2.2';

echo "Données de test préparées:<br>";
echo "- Employé: " . $test_overtime->fk_user . "<br>";
echo "- Mois: " . $test_overtime->month . " (Août)<br>";
echo "- Année: " . $test_overtime->year . "<br>";
echo "- Heures: " . $test_overtime->hours_paid . "<br>";
echo "- Manager: " . $test_overtime->fk_user_manager . "<br>";

// Test 5: Vérification des données existantes
echo "<h3>Test 5: Données existantes</h3>";
$sql_data = "SELECT rowid, ref, month, year, hours_paid FROM ".MAIN_DB_PREFIX."appmobtimetouch_timeclockovertimepaid ORDER BY rowid DESC LIMIT 5";
$resql_data = $db->query($sql_data);

if ($resql_data) {
    $num = $db->num_rows($resql_data);
    echo "Derniers enregistrements ($num):<br>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Ref</th><th>Mois</th><th>Année</th><th>Heures</th></tr>";
    
    while ($obj = $db->fetch_object($resql_data)) {
        echo "<tr>";
        echo "<td>" . $obj->rowid . "</td>";
        echo "<td>" . htmlspecialchars($obj->ref) . "</td>";
        echo "<td>" . $obj->month . "</td>";
        echo "<td>" . $obj->year . "</td>";
        echo "<td>" . $obj->hours_paid . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    $db->free($resql_data);
} else {
    echo "❌ Erreur requête données: " . $db->lasterror() . "<br>";
}

echo "<p><strong>✅ Tests MVP 44.2.2 terminés</strong></p>";
echo "<p><a href='timeclockovertimepaid_card.php?action=create'>→ Tester création avec interface</a></p>";
echo "<p><a href='timeclockovertimepaid_list.php'>→ Voir la liste avec nouvelles colonnes</a></p>";
?>