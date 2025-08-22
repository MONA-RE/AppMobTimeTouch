<?php
/**
 * Template Rapports Annuels (Year-to-Date)
 * 
 * Responsabilité unique : Affichage des rapports d'heures annuels (SRP)
 * Affiche les heures cumulées par utilisateur depuis le début de l'année
 */
?>

<!-- Filtres de période -->
<div style="padding: 15px;">
  <ons-card>
    <div class="title" style="padding: 15px; background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
      <h4 style="margin: 0; color: #495057;">
        <ons-icon icon="md-filter-list" style="color: #007bff; margin-right: 8px;"></ons-icon>
        <?php echo $langs->trans('ReportFilters'); ?>
      </h4>
    </div>
    <div class="content" style="padding: 15px;">
      <form id="filters-form">
        <ons-row>
          <!-- Filtre Type de rapport -->
          <ons-col width="33%">
            <label for="report_type" style="font-weight: 500; margin-bottom: 5px; display: block;">
              <?php echo $langs->trans('ReportType'); ?>
            </label>
            <select id="report_type" name="report_type" onchange="toggleMonthFilter()" style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px;">
              <option value="monthly" <?php echo ($report_type === 'monthly') ? 'selected' : ''; ?>>
                <?php echo $langs->trans('MonthlyReport'); ?>
              </option>
              <option value="annual" <?php echo ($report_type === 'annual') ? 'selected' : ''; ?>>
                <?php echo $langs->trans('AnnualReport'); ?>
              </option>
            </select>
          </ons-col>
          
          <!-- Filtre Mois (masqué pour rapports annuels) -->
          <ons-col id="month_filter_col" width="33%" style="display: none;">
            <label for="filter_month" style="font-weight: 500; margin-bottom: 5px; display: block;">
              <?php echo $langs->trans('Month'); ?>
            </label>
            <select id="filter_month" name="filter_month" style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px;">
              <?php
              $months = [
                1 => $langs->trans('January'),
                2 => $langs->trans('February'), 
                3 => $langs->trans('March'),
                4 => $langs->trans('April'),
                5 => $langs->trans('May'),
                6 => $langs->trans('June'),
                7 => $langs->trans('July'),
                8 => $langs->trans('August'),
                9 => $langs->trans('September'),
                10 => $langs->trans('October'),
                11 => $langs->trans('November'),
                12 => $langs->trans('December')
              ];
              foreach ($months as $num => $name):
              ?>
              <option value="<?php echo $num; ?>" <?php echo ($filter_month == $num) ? 'selected' : ''; ?>>
                <?php echo $name; ?>
              </option>
              <?php endforeach; ?>
            </select>
          </ons-col>
          
          <!-- Filtre Année -->
          <ons-col width="33%">
            <label for="filter_year" style="font-weight: 500; margin-bottom: 5px; display: block;">
              <?php echo $langs->trans('Year'); ?>
            </label>
            <select id="filter_year" name="filter_year" style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px;">
              <?php
              $currentYear = date('Y');
              for ($year = $currentYear - 2; $year <= $currentYear + 1; $year++):
              ?>
              <option value="<?php echo $year; ?>" <?php echo ($filter_year == $year) ? 'selected' : ''; ?>>
                <?php echo $year; ?>
              </option>
              <?php endfor; ?>
            </select>
          </ons-col>
        </ons-row>
        
        <!-- Bouton d'application -->
        <div style="margin-top: 15px; text-align: center;">
          <ons-button onclick="applyFilters()" style="background-color: #007bff; color: white; border-radius: 6px; padding: 10px 20px;">
            <ons-icon icon="md-search" style="margin-right: 5px;"></ons-icon>
            <?php echo $langs->trans('ApplyFilters'); ?>
          </ons-button>
        </div>
      </form>
    </div>
  </ons-card>
</div>

<!-- Résumé annuel (Year-to-Date) -->
<div style="padding: 0 15px 15px 15px;">
  <ons-card>
    <div class="title" style="padding: 15px; background-color: #e8f5e8; border-bottom: 1px solid #a5d6a7;">
      <h4 style="margin: 0; color: #2e7d32;">
        <ons-icon icon="md-timeline" style="color: #4caf50; margin-right: 8px;"></ons-icon>
        <?php 
        $isYTD = ($filter_year == date('Y'));
        if ($isYTD) {
            echo $langs->trans('AnnualReportYTD') . ' - ' . $filter_year . ' (' . $langs->trans('YearToDate') . ')';
        } else {
            echo $langs->trans('AnnualReport') . ' - ' . $filter_year;
        }
        ?>
      </h4>
    </div>
    <div class="content" style="padding: 15px;">
      <?php
      $totalHours = 0;
      $totalUsers = count($annual_reports);
      $usersWithHours = 0;
      $totalTheoreticalHours = 0;
      
      foreach ($annual_reports as $report) {
        $totalHours += $report['total_hours'];
        $totalTheoreticalHours += $report['theoretical_hours'];
        if ($report['total_hours'] > 0) {
          $usersWithHours++;
        }
      }
      ?>
      <ons-row>
        <?php if (!$is_personal_view): ?>
        <ons-col width="20%">
          <div style="text-align: center;">
            <div style="font-size: 20px; font-weight: bold; color: #2196f3;">
              <?php echo $totalUsers; ?>
            </div>
            <div style="font-size: 12px; color: #6c757d;">
              <?php echo $langs->trans('TotalUsers'); ?>
            </div>
          </div>
        </ons-col>
        <ons-col width="20%">
          <div style="text-align: center;">
            <div style="font-size: 20px; font-weight: bold; color: #28a745;">
              <?php echo $usersWithHours; ?>
            </div>
            <div style="font-size: 12px; color: #6c757d;">
              <?php echo $langs->trans('ActiveUsers'); ?>
            </div>
          </div>
        </ons-col>
        <ons-col width="20%">
          <div style="text-align: center;">
            <div style="font-size: 20px; font-weight: bold; color: #ff9800;">
              <?php echo number_format($totalHours, 1); ?>h
            </div>
            <div style="font-size: 12px; color: #6c757d;">
              <?php echo $langs->trans('TotalWorkedHours'); ?>
            </div>
          </div>
        </ons-col>
        <ons-col width="20%">
          <div style="text-align: center;">
            <div style="font-size: 20px; font-weight: bold; color: #6c757d;">
              <?php echo number_format($totalTheoreticalHours / max(1, $totalUsers), 1); ?>h
            </div>
            <div style="font-size: 12px; color: #6c757d;">
              <?php echo $langs->trans('AvgTheoreticalHours'); ?>
            </div>
          </div>
        </ons-col>
        <ons-col width="20%">
          <div style="text-align: center;">
            <div style="font-size: 20px; font-weight: bold; color: #9c27b0;">
              <?php echo $usersWithHours > 0 ? number_format($totalHours / $usersWithHours, 1) : '0'; ?>h
            </div>
            <div style="font-size: 12px; color: #6c757d;">
              <?php echo $langs->trans('AverageWorkedHours'); ?>
            </div>
          </div>
        </ons-col>
        <?php else: ?>
        <!-- Vue personnelle : statistiques avec heures théoriques annuelles -->
        <?php
        $personal_theoretical = isset($annual_reports[0]) ? $annual_reports[0]['theoretical_hours'] : 0;
        $personal_delta = isset($annual_reports[0]) ? $annual_reports[0]['delta_hours'] : ($totalHours - $personal_theoretical);
        $delta_color = $personal_delta >= 0 ? '#28a745' : '#dc3545';
        $months_included = isset($annual_reports[0]) ? $annual_reports[0]['months_included'] : 12;
        ?>
        <ons-col width="20%">
          <div style="text-align: center;">
            <div style="font-size: 20px; font-weight: bold; color: #ff9800;">
              <?php echo number_format($totalHours, 1); ?>h
            </div>
            <div style="font-size: 12px; color: #6c757d;">
              <?php echo $langs->trans('WorkedHours'); ?>
            </div>
          </div>
        </ons-col>
        <ons-col width="20%">
          <div style="text-align: center;">
            <div style="font-size: 20px; font-weight: bold; color: #6c757d;">
              <?php echo number_format($personal_theoretical, 0); ?>h
            </div>
            <div style="font-size: 12px; color: #6c757d;">
              <?php echo $langs->trans('TheoreticalHours'); ?>
            </div>
          </div>
        </ons-col>
        <ons-col width="20%">
          <div style="text-align: center;">
            <div style="font-size: 20px; font-weight: bold; color: <?php echo $delta_color; ?>;">
              <?php echo ($personal_delta >= 0 ? '+' : '') . number_format($personal_delta, 1); ?>h
            </div>
            <div style="font-size: 12px; color: #6c757d;">
              <?php echo $langs->trans('Delta'); ?>
            </div>
          </div>
        </ons-col>
        <ons-col width="20%">
          <div style="text-align: center;">
            <div style="font-size: 20px; font-weight: bold; color: #2196f3;">
              <?php echo isset($annual_reports[0]) ? $annual_reports[0]['total_records'] : 0; ?>
            </div>
            <div style="font-size: 12px; color: #6c757d;">
              <?php echo $langs->trans('Records'); ?>
            </div>
          </div>
        </ons-col>
        <ons-col width="20%">
          <div style="text-align: center;">
            <div style="font-size: 20px; font-weight: bold; color: #4caf50;">
              <?php echo $months_included; ?>
            </div>
            <div style="font-size: 12px; color: #6c757d;">
              <?php echo $langs->trans('MonthsIncluded'); ?>
            </div>
          </div>
        </ons-col>
        <?php endif; ?>
      </ons-row>
      
      <!-- Informations YTD -->
      <?php if ($isYTD): ?>
      <div style="margin-top: 15px; padding: 10px; background-color: #fff3e0; border-left: 4px solid #ff9800; border-radius: 4px;">
        <small style="color: #ef6c00;">
          <ons-icon icon="md-info" style="margin-right: 5px;"></ons-icon>
          <?php 
          $current_month_name = $months[date('n')];
          echo html_entity_decode($langs->trans('YTDInfo', $current_month_name, date('d/m/Y')), ENT_QUOTES, 'UTF-8'); 
          ?>
        </small>
      </div>
      <?php endif; ?>
    </div>
  </ons-card>
</div>

<!-- Liste des utilisateurs et leurs heures annuelles -->
<!-- TK2508-0367 MVP 3: Les employés peuvent maintenant voir cette section avec leurs propres données -->
<?php if (!empty($annual_reports) && $show_user_list): ?>
<div style="padding: 0 15px 15px 15px;">
  <ons-card>
    <div class="title" style="padding: 15px; background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
      <h4 style="margin: 0; color: #495057;">
        <?php if ($is_personal_view): ?>
        <ons-icon icon="md-person" style="color: #6c757d; margin-right: 8px;"></ons-icon>
        <?php echo $user_list_title; ?>
        <?php else: ?>
        <ons-icon icon="md-people" style="color: #6c757d; margin-right: 8px;"></ons-icon>
        <?php echo $user_list_title; ?> (<?php echo count($annual_reports); ?>)
        <?php endif; ?>
      </h4>
    </div>
    
    <!-- En-tête du tableau -->
    <div style="display: flex; padding: 10px 15px; background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; font-weight: 500; font-size: 12px;">
      <?php if (!$is_personal_view): ?>
      <div style="flex: 2; color: #495057;">
        <?php echo $langs->trans('User'); ?>
      </div>
      <?php endif; ?>
      <div style="flex: 1; text-align: center; color: #495057;">
        <?php echo $langs->trans('Records'); ?>
      </div>
      <div style="flex: 1; text-align: center; color: #495057;">
        <?php echo $langs->trans('WorkedHours'); ?>
      </div>
      <div style="flex: 1; text-align: center; color: #495057;">
        <?php echo $langs->trans('TheoreticalHours'); ?>
      </div>
      <div style="flex: 1; text-align: center; color: #495057;">
        <?php echo $langs->trans('Delta'); ?>
      </div>
      <!-- MVP 44.3: Nouvelles colonnes heures supplémentaires -->
      <div style="flex: 1; text-align: center; color: #495057;">
        <?php echo $langs->trans('PaidHours'); ?>
      </div>
      <div style="flex: 1; text-align: center; color: #495057;">
        <?php echo $langs->trans('RemainingOvertime'); ?>
      </div>
      <div style="flex: 1; text-align: center; color: #495057;">
        <?php echo $langs->trans('MonthlyAverage'); ?>
      </div>
      <div style="flex: 1; text-align: center; color: #495057;">
        <?php echo $langs->trans('Status'); ?>
      </div>
    </div>
    
    <!-- Lignes des utilisateurs -->
    <?php foreach ($annual_reports as $index => $report): ?>
    <div class="user-row" style="display: flex; padding: 12px 15px; border-bottom: 1px solid #f0f0f0; align-items: center;">
      <?php if (!$is_personal_view): ?>
      <!-- Nom utilisateur -->
      <div style="flex: 2;">
        <div style="font-weight: 500; margin-bottom: 3px;">
          <?php echo dol_escape_htmltag($report['fullname']); ?>
        </div>
        <div style="font-size: 12px; color: #6c757d;">
          <?php echo dol_escape_htmltag($report['login']); ?>
        </div>
      </div>
      <?php endif; ?>
      
      <!-- Nombre d'enregistrements -->
      <div style="flex: 1; text-align: center;">
        <div style="font-weight: 500;">
          <?php echo $report['total_records']; ?>
        </div>
        <?php if ($report['incomplete_records'] > 0): ?>
        <div style="font-size: 11px; color: #dc3545;">
          <?php echo $report['incomplete_records']; ?> incomplet(s)
        </div>
        <?php endif; ?>
      </div>
      
      <!-- Heures travaillées -->
      <div style="flex: 1; text-align: center;">
        <div class="hours-display" style="font-size: 16px;">
          <?php echo number_format($report['total_hours'], 1); ?>h
        </div>
      </div>
      
      <!-- Heures théoriques -->
      <div style="flex: 1; text-align: center;">
        <div style="font-size: 14px; color: #6c757d;">
          <?php echo number_format($report['theoretical_hours'], 0); ?>h
        </div>
        <div style="font-size: 11px; color: #adb5bd;">
          (<?php echo $report['months_included']; ?> mois)
        </div>
      </div>
      
      <!-- Delta avec codage couleur -->
      <div style="flex: 1; text-align: center;">
        <?php 
        $delta = $report['delta_hours'];
        $delta_color = '#6c757d';
        $delta_bg = '#f8f9fa';
        $delta_icon = 'md-remove';
        
        if ($delta > 0) {
            $delta_color = '#155724';
            $delta_bg = '#d4edda';
            $delta_icon = 'md-add';
        } elseif ($delta < 0) {
            $delta_color = '#721c24';
            $delta_bg = '#f8d7da';
            $delta_icon = 'md-remove';
        }
        ?>
        <div style="display: inline-block; background-color: <?php echo $delta_bg; ?>; color: <?php echo $delta_color; ?>; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
          <ons-icon icon="<?php echo $delta_icon; ?>" style="font-size: 10px; margin-right: 2px;"></ons-icon>
          <?php echo ($delta >= 0 ? '+' : '') . number_format($delta, 1); ?>h
        </div>
      </div>
      
      <!-- MVP 44.3: Heures payées (total année) -->
      <div style="flex: 1; text-align: center;">
        <div style="font-size: 14px; color: #007bff;">
          <?php echo number_format($report['paid_overtime_hours'] ?? 0, 1); ?>h
        </div>
      </div>
      
      <!-- MVP 44.3: Heures supplémentaires restantes -->
      <div style="flex: 1; text-align: center;">
        <?php 
        $remaining = $report['remaining_overtime_hours'] ?? $report['delta_hours'];
        $remaining_color = '#6c757d';
        $remaining_bg = '#f8f9fa';
        $remaining_icon = 'md-remove';
        
        if ($remaining > 0) {
            $remaining_color = '#dc3545';
            $remaining_bg = '#f8d7da';
            $remaining_icon = 'md-warning';
        } elseif ($remaining < 0) {
            $remaining_color = '#28a745';
            $remaining_bg = '#d4edda';
            $remaining_icon = 'md-check';
        }
        ?>
        <div style="display: inline-block; background-color: <?php echo $remaining_bg; ?>; color: <?php echo $remaining_color; ?>; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
          <ons-icon icon="<?php echo $remaining_icon; ?>" style="font-size: 10px; margin-right: 2px;"></ons-icon>
          <?php echo ($remaining >= 0 ? '+' : '') . number_format($remaining, 1); ?>h
        </div>
      </div>
      
      <!-- Moyenne mensuelle -->
      <div style="flex: 1; text-align: center;">
        <div style="font-size: 14px; color: #495057;">
          <?php echo number_format($report['total_hours'] / max(1, $report['months_included']), 1); ?>h
        </div>
        <div style="font-size: 11px; color: #adb5bd;">
          /mois
        </div>
      </div>
      
      <!-- Statut -->
      <div style="flex: 1; text-align: center;">
        <?php if ($report['total_hours'] == 0): ?>
          <span style="background-color: #f8f9fa; color: #6c757d; padding: 3px 8px; border-radius: 12px; font-size: 11px;">
            <?php echo $langs->trans('NoActivity'); ?>
          </span>
        <?php elseif ($report['incomplete_records'] > 0): ?>
          <span style="background-color: #fff3cd; color: #856404; padding: 3px 8px; border-radius: 12px; font-size: 11px;">
            <ons-icon icon="md-warning" style="font-size: 10px;"></ons-icon>
            <?php echo $langs->trans('Incomplete'); ?>
          </span>
        <?php else: ?>
          <span style="background-color: #d4edda; color: #155724; padding: 3px 8px; border-radius: 12px; font-size: 11px;">
            <ons-icon icon="md-check" style="font-size: 10px;"></ons-icon>
            <?php echo $langs->trans('Complete'); ?>
          </span>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </ons-card>
</div>
<?php else: ?>
<!-- État vide -->
<div style="padding: 0 15px 15px 15px;">
  <ons-card>
    <div class="content" style="text-align: center; padding: 40px;">
      <ons-icon icon="md-timeline" style="font-size: 48px; color: #6c757d; margin-bottom: 15px;"></ons-icon>
      <h4 style="color: #6c757d; margin-bottom: 10px;">
        <?php echo $langs->trans('NoDataFound'); ?>
      </h4>
      <p style="color: #6c757d; margin: 0;">
        <?php echo $langs->trans('NoActivityForYear'); ?>
      </p>
    </div>
  </ons-card>
</div>
<?php endif; ?>

<script>
// Initialize month filter visibility
document.addEventListener('DOMContentLoaded', function() {
    toggleMonthFilter();
});
</script>