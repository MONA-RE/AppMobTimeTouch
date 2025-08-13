<?php
/**
 * Migration script MVP 44.2.2 - Split month_year into month and year fields
 */

require_once '../../main.inc.php';

// Vérification module activé
if (!isModEnabled('appmobtimetouch')) {
    die('Module AppMobTimeTouch not enabled');
}

// Vérification droits admin
if (!$user->admin) {
    die('Admin rights required for migration');
}

echo "<h2>MVP 44.2.2 - Migration month_year → month + year</h2>";

// Check if migration is needed
$sql_check = "SHOW COLUMNS FROM ".MAIN_DB_PREFIX."appmobtimetouch_timeclockovertimepaid LIKE 'month_year'";
$resql_check = $db->query($sql_check);
$month_year_exists = ($resql_check && $db->num_rows($resql_check) > 0);

$sql_check_new = "SHOW COLUMNS FROM ".MAIN_DB_PREFIX."appmobtimetouch_timeclockovertimepaid LIKE 'month'";
$resql_check_new = $db->query($sql_check_new);
$month_exists = ($resql_check_new && $db->num_rows($resql_check_new) > 0);

echo "<h3>État de la migration :</h3>";
echo "- Colonne month_year existe : " . ($month_year_exists ? "✅ Oui" : "❌ Non") . "<br>";
echo "- Colonne month existe : " . ($month_exists ? "✅ Oui" : "❌ Non") . "<br><br>";

if (!$month_year_exists && !$month_exists) {
    echo "❌ Erreur: Aucune colonne trouvée. La table doit être recréée.<br>";
    exit;
}

if ($month_exists && !$month_year_exists) {
    echo "✅ Migration déjà effectuée !<br>";
    exit;
}

if ($month_year_exists && !$month_exists) {
    echo "<h3>🔄 Migration nécessaire</h3>";
    
    if (isset($_POST['migrate'])) {
        echo "<h4>Étape 1: Ajout des nouvelles colonnes</h4>";
        
        $sql1 = "ALTER TABLE ".MAIN_DB_PREFIX."appmobtimetouch_timeclockovertimepaid 
                ADD COLUMN month integer COMMENT 'Month (1-12)' AFTER fk_user,
                ADD COLUMN year integer COMMENT 'Year (ex: 2025)' AFTER month";
        
        $result1 = $db->query($sql1);
        if ($result1) {
            echo "✅ Colonnes month et year ajoutées<br>";
        } else {
            echo "❌ Erreur ajout colonnes: " . $db->lasterror() . "<br>";
            exit;
        }
        
        echo "<h4>Étape 2: Migration des données</h4>";
        
        $sql2 = "UPDATE ".MAIN_DB_PREFIX."appmobtimetouch_timeclockovertimepaid 
                SET 
                    year = CAST(SUBSTRING(month_year, 1, 4) AS UNSIGNED),
                    month = CAST(SUBSTRING(month_year, 6, 2) AS UNSIGNED)
                WHERE month_year IS NOT NULL AND month_year != ''";
        
        $result2 = $db->query($sql2);
        if ($result2) {
            echo "✅ Données migrées: " . $db->affected_rows() . " enregistrements<br>";
        } else {
            echo "❌ Erreur migration données: " . $db->lasterror() . "<br>";
            exit;
        }
        
        echo "<h4>Étape 3: Ajout contraintes NOT NULL</h4>";
        
        $sql3 = "ALTER TABLE ".MAIN_DB_PREFIX."appmobtimetouch_timeclockovertimepaid 
                MODIFY COLUMN month integer NOT NULL COMMENT 'Month (1-12)',
                MODIFY COLUMN year integer NOT NULL COMMENT 'Year (ex: 2025)'";
        
        $result3 = $db->query($sql3);
        if ($result3) {
            echo "✅ Contraintes NOT NULL ajoutées<br>";
        } else {
            echo "❌ Erreur contraintes: " . $db->lasterror() . "<br>";
            exit;
        }
        
        echo "<h4>Étape 4: Suppression ancienne colonne</h4>";
        
        $sql4 = "ALTER TABLE ".MAIN_DB_PREFIX."appmobtimetouch_timeclockovertimepaid 
                DROP COLUMN month_year";
        
        $result4 = $db->query($sql4);
        if ($result4) {
            echo "✅ Colonne month_year supprimée<br>";
        } else {
            echo "❌ Erreur suppression: " . $db->lasterror() . "<br>";
            exit;
        }
        
        echo "<p><strong>🎉 Migration MVP 44.2.2 terminée avec succès !</strong></p>";
        echo "<p><a href='timeclockovertimepaid_card.php?action=create'>→ Tester la création avec nouveaux champs</a></p>";
        
    } else {
        echo "<p>⚠️ Cette migration va modifier la structure de la base de données.</p>";
        echo "<form method='post'>";
        echo "<input type='hidden' name='migrate' value='1'>";
        echo "<input type='submit' value='🚀 Lancer la migration' class='button' onclick='return confirm(\"Êtes-vous sûr de vouloir migrer les données ?\");'>";
        echo "</form>";
    }
    
} else {
    echo "⚠️ État inattendu: les deux colonnes existent. Vérification manuelle requise.<br>";
}

echo "<p><a href='timeclockovertimepaid_list.php'>← Retour à la liste</a></p>";
?>