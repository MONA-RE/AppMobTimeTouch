-- Requête pour afficher la somme des durées de travail par utilisateur et par mois
-- Génère un rapport mensuel des heures travaillées pour tous les utilisateurs actifs
-- Utilise le champ work_duration (en minutes) de la table llx_timeclock_records

SELECT 
    u.firstname,
    u.lastname,
    YEAR(tr.clock_in_time) as annee,
    MONTH(tr.clock_in_time) as mois,
    MONTHNAME(tr.clock_in_time) as nom_mois,
    SUM(tr.work_duration) as total_minutes,
    ROUND(SUM(tr.work_duration) / 60, 2) as total_heures
FROM llx_timeclock_records tr
JOIN llx_user u ON tr.fk_user = u.rowid
WHERE tr.work_duration IS NOT NULL
GROUP BY u.rowid, u.firstname, u.lastname, YEAR(tr.clock_in_time), MONTH(tr.clock_in_time)
ORDER BY u.lastname, u.firstname, annee DESC, mois DESC;

-- Alternative avec plus de détails (nombre de jours travaillés, moyenne journalière)
/*
SELECT 
    u.firstname,
    u.lastname,
    YEAR(tr.clock_in_time) as annee,
    MONTH(tr.clock_in_time) as mois,
    MONTHNAME(tr.clock_in_time) as nom_mois,
    COUNT(DISTINCT DATE(tr.clock_in_time)) as jours_travailles,
    SUM(tr.work_duration) as total_minutes,
    ROUND(SUM(tr.work_duration) / 60, 2) as total_heures,
    ROUND(SUM(tr.work_duration) / COUNT(DISTINCT DATE(tr.clock_in_time)) / 60, 2) as moyenne_heures_par_jour
FROM llx_timeclock_records tr
JOIN llx_user u ON tr.fk_user = u.rowid
WHERE tr.work_duration IS NOT NULL
  AND tr.status IN (1, 3) -- Seulement les enregistrements validés ou terminés
GROUP BY u.rowid, u.firstname, u.lastname, YEAR(tr.clock_in_time), MONTH(tr.clock_in_time)
ORDER BY u.lastname, u.firstname, annee DESC, mois DESC;
*/