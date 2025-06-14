<?php
/**
 * Template Rapports Mensuels
 * 
 * Responsabilité unique : Affichage des rapports d'heures mensuels (SRP)
 * Affiche les heures cumulées par utilisateur pour un mois donné
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
          <!-- Filtre Mois -->
          <ons-col width="50%">
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
          <ons-col width="50%">
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

<!-- Résumé du mois -->
<div style="padding: 0 15px 15px 15px;">
  <ons-card>
    <div class="title" style="padding: 15px; background-color: #e3f2fd; border-bottom: 1px solid #90caf9;">
      <h4 style="margin: 0; color: #1565c0;">
        <ons-icon icon="md-assessment" style="color: #2196f3; margin-right: 8px;"></ons-icon>
        <?php 
        echo $langs->trans('MonthlyReport') . ' - ' . 
        $months[$filter_month] . ' ' . $filter_year; 
        ?>
      </h4>
    </div>
    <div class="content" style="padding: 15px;">
      <?php
      $totalHours = 0;
      $totalUsers = count($monthly_reports);
      $usersWithHours = 0;
      
      foreach ($monthly_reports as $report) {
        $totalHours += $report['total_hours'];
        if ($report['total_hours'] > 0) {
          $usersWithHours++;
        }
      }
      ?>
      <ons-row>
        <?php if (!$is_personal_view): ?>
        <ons-col width="25%">
          <div style="text-align: center;">
            <div style="font-size: 20px; font-weight: bold; color: #2196f3;">
              <?php echo $totalUsers; ?>
            </div>
            <div style="font-size: 12px; color: #6c757d;">
              <?php echo $langs->trans('TotalUsers'); ?>
            </div>
          </div>
        </ons-col>
        <ons-col width="25%">
          <div style="text-align: center;">
            <div style="font-size: 20px; font-weight: bold; color: #28a745;">
              <?php echo $usersWithHours; ?>
            </div>
            <div style="font-size: 12px; color: #6c757d;">
              <?php echo $langs->trans('ActiveUsers'); ?>
            </div>
          </div>
        </ons-col>
        <ons-col width="25%">
          <div style="text-align: center;">
            <div style="font-size: 20px; font-weight: bold; color: #ff9800;">
              <?php echo number_format($totalHours, 1); ?>h
            </div>
            <div style="font-size: 12px; color: #6c757d;">
              <?php echo $langs->trans('TotalHours'); ?>
            </div>
          </div>
        </ons-col>
        <ons-col width="25%">
          <div style="text-align: center;">
            <div style="font-size: 20px; font-weight: bold; color: #9c27b0;">
              <?php echo $usersWithHours > 0 ? number_format($totalHours / $usersWithHours, 1) : '0'; ?>h
            </div>
            <div style="font-size: 12px; color: #6c757d;">
              <?php echo $langs->trans('AverageHours'); ?>
            </div>
          </div>
        </ons-col>
        <?php else: ?>
        <!-- Vue personnelle : statistiques simplifiées -->
        <ons-col width="33%">
          <div style="text-align: center;">
            <div style="font-size: 24px; font-weight: bold; color: #ff9800;">
              <?php echo number_format($totalHours, 1); ?>h
            </div>
            <div style="font-size: 14px; color: #6c757d;">
              <?php echo $langs->trans('TotalHours'); ?>
            </div>
          </div>
        </ons-col>
        <ons-col width="33%">
          <div style="text-align: center;">
            <div style="font-size: 24px; font-weight: bold; color: #2196f3;">
              <?php echo isset($monthly_reports[0]) ? $monthly_reports[0]['total_records'] : 0; ?>
            </div>
            <div style="font-size: 14px; color: #6c757d;">
              <?php echo $langs->trans('Records'); ?>
            </div>
          </div>
        </ons-col>
        <ons-col width="33%">
          <div style="text-align: center;">
            <div style="font-size: 24px; font-weight: bold; color: <?php echo (isset($monthly_reports[0]) && $monthly_reports[0]['incomplete_records'] > 0) ? '#dc3545' : '#28a745'; ?>;">
              <?php echo isset($monthly_reports[0]) ? $monthly_reports[0]['incomplete_records'] : 0; ?>
            </div>
            <div style="font-size: 14px; color: #6c757d;">
              <?php echo $langs->trans('Incomplete'); ?>
            </div>
          </div>
        </ons-col>
        <?php endif; ?>
      </ons-row>
    </div>
  </ons-card>
</div>

<!-- Liste des utilisateurs et leurs heures -->
<?php if (!empty($monthly_reports)): ?>
<div style="padding: 0 15px 15px 15px;">
  <ons-card>
    <div class="title" style="padding: 15px; background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
      <h4 style="margin: 0; color: #495057;">
        <?php if ($is_personal_view): ?>
        <ons-icon icon="md-person" style="color: #6c757d; margin-right: 8px;"></ons-icon>
        <?php echo $langs->trans('MyHours'); ?>
        <?php else: ?>
        <ons-icon icon="md-people" style="color: #6c757d; margin-right: 8px;"></ons-icon>
        <?php echo $langs->trans('UsersHours'); ?> (<?php echo count($monthly_reports); ?>)
        <?php endif; ?>
      </h4>
    </div>
    
    <!-- En-tête du tableau -->
    <div style="display: flex; padding: 10px 15px; background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; font-weight: 500; font-size: 14px;">
      <?php if (!$is_personal_view): ?>
      <div style="flex: 2; color: #495057;">
        <?php echo $langs->trans('User'); ?>
      </div>
      <?php endif; ?>
      <div style="flex: 1; text-align: center; color: #495057;">
        <?php echo $langs->trans('Records'); ?>
      </div>
      <div style="flex: 1; text-align: center; color: #495057;">
        <?php echo $langs->trans('Hours'); ?>
      </div>
      <div style="flex: 1; text-align: center; color: #495057;">
        <?php echo $langs->trans('Status'); ?>
      </div>
    </div>
    
    <!-- Lignes des utilisateurs -->
    <?php foreach ($monthly_reports as $index => $report): ?>
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
      
      <!-- Heures -->
      <div style="flex: 1; text-align: center;">
        <div class="hours-display" style="font-size: 16px;">
          <?php echo number_format($report['total_hours'], 1); ?>h
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
      <ons-icon icon="md-bar-chart" style="font-size: 48px; color: #6c757d; margin-bottom: 15px;"></ons-icon>
      <h4 style="color: #6c757d; margin-bottom: 10px;">
        <?php echo $langs->trans('NoDataFound'); ?>
      </h4>
      <p style="color: #6c757d; margin: 0;">
        <?php echo $langs->trans('NoActivityForPeriod'); ?>
      </p>
    </div>
  </ons-card>
</div>
<?php endif; ?>

