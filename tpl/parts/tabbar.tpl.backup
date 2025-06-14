<!-- Contenu principal de l'application avec tabbar simplifié -->
<ons-page id="tabbarPage">
	<!-- Contenu principal - Page d'accueil par défaut -->
	<div id="main-content">
		<?php include 'tpl/home.tpl'; ?>
	</div>
	
	<!-- Tabbar en bas sans attribut page problématique -->
	<ons-tabbar position="bottom">
		<!-- Onglet Accueil / Today -->
		<ons-tab id="tabHome" onclick="showHomePage();" label="<?php echo $langs->trans('Today'); ?>" icon="fa-home" badge="" active>
		</ons-tab>
		
		<!-- Onglet Mes Enregistrements -->
		<ons-tab id="tabMyRecords" onclick="loadMyRecords();" label="<?php echo $langs->trans('MyRecords'); ?>" icon="fa-clock-o" badge="">
		</ons-tab>
		
		<?php if (!empty($user->rights->appmobtimetouch->timeclock->readall)): ?>
		<!-- Onglet Gestion (pour les managers) -->
		<ons-tab id="tabManagement" onclick="loadManagement();" label="<?php echo $langs->trans('Management'); ?>" icon="fa-users" badge="">
		</ons-tab>
		<?php else: ?>
		<!-- Onglet Rapports pour les utilisateurs normaux -->
		<ons-tab id="tabSummary" onclick="loadSummary();" label="<?php echo $langs->trans('Reports'); ?>" icon="fa-bar-chart" badge="">
		</ons-tab>
		<?php endif; ?>
		
		<!-- Onglet Paramètres -->
		<ons-tab id="tabSettings" onclick="loadSettings();" label="<?php echo $langs->trans('Settings'); ?>" icon="fa-cog" badge="">
		</ons-tab>
	</ons-tabbar>
</ons-page>