<ons-page id="ONSrightmenu">

  <ons-list-title>MENU</ons-list-title>
  <ons-list id="links" class="rightMenuList">


    <!-- Mes Heures -->
    <ons-list-item onclick="gotoPage('myTimeclockRecords');" tappable modifier="nodivider">
      <div class="left">
        <ons-icon icon="fa-clock-o" style="color: #4CAF50;"></ons-icon>
      </div>
      <div class="center">
        <span><?php echo $langs->trans("MyTimeclockRecords"); ?></span>
      </div>
    </ons-list-item>


    <?php if (!empty($user->rights->appmobtimetouch->timeclock->readall)): ?>
    <!-- Gestion Équipe (Managers seulement) -->
    <ons-list-item onclick="gotoPage('teamManagement');" tappable modifier="nodivider">
      <div class="left">
        <ons-icon icon="fa-users" style="color: #9C27B0;"></ons-icon>
      </div>
      <div class="center">
        <span><?php echo $langs->trans("TeamManagement"); ?></span>
      </div>
    </ons-list-item>
    <?php endif; ?>

    <?php if (!empty($user->rights->appmobtimetouch->timeclock->validate)): ?>
    <!-- Validation Manager Dashboard (MVP 3.1) -->
    <ons-list-item onclick="loadManagement();" tappable modifier="nodivider">
      <div class="left">
        <ons-icon icon="fa-check-circle" style="color: #607D8B;"></ons-icon>
      </div>
      <div class="center">
        <span><?php echo $langs->trans("ValidationManager"); ?></span>
        <?php 
        // Afficher le nombre d'éléments en attente de validation
        if (isset($pending_validation_count) && $pending_validation_count > 0): 
        ?>
        <div class="right">
          <ons-badge style="background-color: #f44336;"><?php echo $pending_validation_count; ?></ons-badge>
        </div>
        <?php endif; ?>
      </div>
    </ons-list-item>
    <?php endif; ?>

    <?php if (!empty($user->rights->appmobtimetouch->timeclock->export)): ?>
    <!-- Rapports -->
    <ons-list-item onclick="loadReports();" tappable modifier="nodivider">
      <div class="left">
        <ons-icon icon="fa-bar-chart" style="color: #795548;"></ons-icon>
      </div>
      <div class="center">
        <span><?php echo $langs->trans("Reports"); ?></span>
      </div>
    </ons-list-item>
    <?php endif; ?>

    <!-- Divider Applications -->
    <ons-list-item modifier="nodivider" style="height: 1px; background-color: #e0e0e0; margin: 10px 0;"></ons-list-item>

    <!-- Titre Applications -->
    <ons-list-item modifier="nodivider" style="background-color: #f5f5f5; padding: 5px 15px;">
      <div class="center">
        <span style="font-size: 12px; color: #666; font-weight: bold; text-transform: uppercase;">
          <?php echo $langs->trans("Applications"); ?>
        </span>
      </div>
    </ons-list-item>

    <?php if (!empty($conf->appmobsalesorders->enabled) && $user->rights->appmobsalesorders->order->read_validated): ?>
    <!-- AppMobSalesOrders -->
    <ons-list-item onclick="goToCustomApp('appmobsalesorders');" tappable modifier="nodivider">
      <div class="left">
        <img src="<?php echo DOL_URL_ROOT; ?>/custom/appmobsalesorders/img/open-box_3286875.png" style="width: 20px; height: 20px;" alt="AppMobSalesOrders">
      </div>
      <div class="center">
        <span><?php echo $langs->trans("appmobsalesorders"); ?></span>
      </div>
    </ons-list-item>
    <?php endif; ?>

    <!-- Divider Fin Applications -->
    <ons-list-item modifier="nodivider" style="height: 1px; background-color: #e0e0e0; margin: 10px 0;"></ons-list-item>



    <!-- À propos -->
    <ons-list-item onclick="gotoPage('aproposApplication');" tappable modifier="nodivider">
      <div class="left">
        <ons-icon icon="fa-info-circle" style="color: #757575;"></ons-icon>
      </div>
      <div class="center">
        <span><?php echo $langs->trans("about"); ?></span>
      </div>
    </ons-list-item>



    <!-- Divider -->
    <ons-list-item modifier="nodivider" style="height: 1px; background-color: #e0e0e0; margin: 10px 0;"></ons-list-item>

    <!-- Déconnexion -->
    <ons-list-item onclick="closeSession();" tappable modifier="nodivider">
      <div class="left">
        <ons-icon icon="fa-sign-out" style="color: #f44336;"></ons-icon>
      </div>
      <div class="center">
        <span><?php echo $langs->trans("closeSession"); ?></span>
      </div>
    </ons-list-item>

  </ons-list>
</ons-page>