# Sprint 44 - Saisie des heures supplémentaires payées

## Objectif

Permettre aux managers de saisir facilement le nombre d'heures supplémentaires qu'ils ont payé à leurs salariés pour un mois donné. Une fois saisies, les rapports affichent les heures actualisées : `heures_réelles - heures_payées vs heures_théoriques`.

## Méthode d'implémentation la plus rapide

### Approche choisie : Extension du système existant

**Pourquoi cette approche :**
- ✅ Réutilise l'architecture SOLID existante (Services/Controllers)
- ✅ S'intègre naturellement dans les rapports existants
- ✅ Minimise les modifications de code
- ✅ Respecte les patterns Dolibarr existants

### Plan d'implémentation SOLID + MVP

#### **MVP 1 : Entité et Service de base** (2-3h)

**1. Nouvelle table SQL :**
```sql
-- sql/llx_timeclock_overtime_paid.sql
CREATE TABLE llx_timeclock_overtime_paid (
    rowid INTEGER AUTO_INCREMENT PRIMARY KEY,
    entity INTEGER DEFAULT 1 NOT NULL,
    datec DATETIME NOT NULL,
    tms TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fk_user INTEGER NOT NULL,
    fk_manager INTEGER NOT NULL,
    year INTEGER NOT NULL,
    month INTEGER NOT NULL,
    hours_paid DECIMAL(8,2) NOT NULL DEFAULT 0,
    note_private TEXT,
    UNIQUE KEY uk_overtime_paid (entity, fk_user, year, month),
    INDEX idx_user (fk_user),
    INDEX idx_manager (fk_manager),
    INDEX idx_period (year, month)
);
```

**2. Classe entité :**
```php
// class/timeclockovertimepaid.class.php
class TimeclockOvertimePaid extends CommonObject
{
    public $element = 'timeclockovertimepaid';
    public $table_element = 'timeclock_overtime_paid';
    // + propriétés et méthodes CRUD standard Dolibarr
}
```

**3. Service métier :**
```php
// Services/OvertimeService.php
class OvertimeService implements OvertimeServiceInterface 
{
    public function saveHoursPaid($userId, $year, $month, $hours, $managerId) {}
    public function getHoursPaid($userId, $year, $month) {}
    public function getMonthlyOvertimePaid($managerId, $year, $month) {}
}
```

**Interface de validation MVP 1 :**
- Page autonome : `overtime_paid.php` 
- Formulaire simple : Utilisateur + Mois/Année + Heures payées
- Test : Saisie et sauvegarde fonctionnelles

#### **MVP 2 : Intégration dans les rapports** (1-2h)

**1. Extension du contrôleur rapports :**
```php
// Modification dans reports.php
// Ajout action de saisie des heures payées
if ($action === 'save_paid_overtime') {
    $overtimeService->saveHoursPaid($userId, $year, $month, $hours, $user->id);
}
```

**2. Template avec modal de saisie :**
```html
<!-- Views/reports/overtime_modal.tpl -->
<ons-modal id="overtimeModal">
    <form method="POST">
        <select name="user_id"><!-- Liste utilisateurs --></select>
        <input type="number" name="hours_paid" step="0.5">
        <button type="submit" name="action" value="save_paid_overtime">
    </form>
</ons-modal>
```

**3. Bouton dans les rapports existants :**
```php
// Ajout dans Views/reports/monthly.tpl
echo '<button onclick="showOvertimeModal()">Saisir heures payées</button>';
```

**Interface de validation MVP 2 :**
- Bouton "Saisir heures payées" dans les rapports mensuels
- Modal de saisie fonctionnelle avec retour aux rapports
- Test : Saisie depuis l'interface rapports existante

#### **MVP 3 : Calculs automatisés** (1-2h)

**1. Extension des requêtes de rapports :**
```sql
-- Modification dans getMonthlyReports()
SELECT 
    tr.fk_user,
    SUM(tr.work_duration) as total_minutes,
    COALESCE(op.hours_paid * 60, 0) as paid_minutes,
    (SUM(tr.work_duration) - COALESCE(op.hours_paid * 60, 0)) as remaining_minutes
FROM llx_timeclock_records tr
LEFT JOIN llx_timeclock_overtime_paid op ON (
    op.fk_user = tr.fk_user 
    AND op.year = :year 
    AND op.month = :month
)
GROUP BY tr.fk_user
```

**2. Nouvelles colonnes dans les rapports :**
```php
// Views/reports/monthly.tpl
// Ajout colonnes : "Heures payées" | "Heures restant dues"
echo '<td>' . round($paid_minutes / 60, 2) . 'h</td>';
echo '<td>' . round($remaining_minutes / 60, 2) . 'h</td>';
```

**Interface de validation MVP 3 :**
- Colonnes "Payées" et "Restant dû" visibles dans les rapports
- Calculs corrects : `150h réelles - 10h payées = 140h vs 140h théoriques = 0h supplémentaires`
- Test : Vérification mathématique dans l'interface

### Architecture technique

**Respect des principes SOLID :**

- **SRP** : Classe dédiée `TimeclockOvertimePaid` + Service spécialisé `OvertimeService`
- **OCP** : Extension des rapports sans modification du code existant
- **LSP** : Héritage standard `CommonObject` de Dolibarr
- **ISP** : Interface `OvertimeServiceInterface` spécifique aux heures payées
- **DIP** : Injection du service dans les contrôleurs existants

**Integration Dolibarr :**
- ✅ Utilise `CommonObject` et les méthodes `createCommon()`
- ✅ Suit le pattern des tables avec `entity`, `datec`, `tms`
- ✅ Respecte les droits existants (`timeclock->validate` pour les managers)
- ✅ S'intègre dans le menu existant (rapports)

### Temps d'implémentation estimé

- **MVP 1** : 2-3 heures (SQL + Classe + Service)
- **MVP 2** : 1-2 heures (Modal + Intégration)  
- **MVP 3** : 1-2 heures (Calculs + Affichage)
- **Total** : 4-7 heures de développement

### Tests de validation

**MVP 1 :**
```bash
# Test direct
curl -X POST overtime_paid.php -d "user_id=5&year=2025&month=8&hours_paid=10"
# Vérification en base
SELECT * FROM llx_timeclock_overtime_paid WHERE fk_user=5;
```

**MVP 2 :**
- Clic sur "Saisir heures payées" → Modal s'ouvre
- Saisie 10h pour utilisateur X → Sauvegarde + fermeture modal
- Retour rapports → Pas de régression

**MVP 3 :**
- Rapport utilisateur avec 150h réelles, 140h théoriques
- Saisie 10h payées → Nouveau calcul : 140h restant vs 140h = 0h suppl.
- Vérification mathématique dans l'affichage

Cette approche garantit une implémentation rapide, testable à chaque étape, et parfaitement intégrée dans l'architecture existante.