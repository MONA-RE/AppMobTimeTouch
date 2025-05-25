<ons-page id="tabbarPage">
	<ons-tabbar position="bottom">
		<!-- Onglet Accueil / Today -->
		<ons-tab id="tabHome" page="home.php" label="<?php echo $langs->trans('Today'); ?>" icon="fa-home" badge="">
		</ons-tab>
		
		<!-- Onglet Mes Enregistrements -->
		<ons-tab id="tabMyRecords" onclick="loadMyRecords();" label="<?php echo $langs->trans('MyRecords'); ?>" icon="fa-clock-o" badge="">
		</ons-tab>
		
		<?php if ($user->rights->appmobtimetouch->timeclock->readall): ?>
		<!-- Onglet Gestion (pour les managers) -->
		<ons-tab id="tabManagement" onclick="loadManagement();" label="<?php echo $langs->trans('Management'); ?>" icon="fa-users" badge="">
		</ons-tab>
		<?php else: ?>
		<!-- Onglet Résumés pour les utilisateurs normaux -->
		<ons-tab id="tabSummary" onclick="loadSummary();" label="<?php echo $langs->trans('Summary'); ?>" icon="fa-bar-chart" badge="">
		</ons-tab>
		<?php endif; ?>
		
		<!-- Onglet Paramètres -->
		<ons-tab id="tabSettings" onclick="loadSettings();" label="<?php echo $langs->trans('Settings'); ?>" icon="fa-cog" badge="">
		</ons-tab>
	</ons-tabbar>
</ons-page>