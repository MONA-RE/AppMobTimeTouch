-- Script pour mettre à jour les indexes après migration MVP 44.2.2
-- Exécuter après la migration des champs month_year → month + year
 USE `dev-smta`;

-- 1. Supprimer l'ancien index sur month_year (s'il existe)
DROP INDEX IF EXISTS idx_appmobtimetouch_timeclockovertimepaid_month_year ON llx_appmobtimetouch_timeclockovertimepaid;

-- 2. Supprimer l'ancienne contrainte unique (s'il existe)
DROP INDEX IF EXISTS uk_appmobtimetouch_timeclockovertimepaid_user_month ON llx_appmobtimetouch_timeclockovertimepaid;

-- 3. Ajouter les nouveaux index
ALTER TABLE llx_appmobtimetouch_timeclockovertimepaid ADD INDEX idx_appmobtimetouch_timeclockovertimepaid_month (month);
ALTER TABLE llx_appmobtimetouch_timeclockovertimepaid ADD INDEX idx_appmobtimetouch_timeclockovertimepaid_year (year);

-- 4. Ajouter la nouvelle contrainte unique (un enregistrement par utilisateur par mois)
ALTER TABLE llx_appmobtimetouch_timeclockovertimepaid ADD UNIQUE INDEX uk_appmobtimetouch_timeclockovertimepaid_user_month(fk_user, month, year, entity);

-- Vérifier les indexes créés
SHOW INDEX FROM llx_appmobtimetouch_timeclockovertimepaid;