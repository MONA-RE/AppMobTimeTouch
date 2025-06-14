<ons-toolbar modifier="material">
    <div id="titreAppMobTimeTouch" class="left">
        <span id="titreAppMobTimeTouch">
            <?php echo $langs->trans("AppMobTimeTouch"); ?>
        </span>
    </div>
    <div id="home" class="left" onclick="goToHome()" style="cursor: pointer;">
        <img src="img/clock-icon.png" id="homeLogo" style="height: 30px; width: 30px; margin-left: 10px;" />
    </div>
    <div id="menuHautDroite" class="right">
        <!-- Status indicator -->
        <?php if ($is_clocked_in): ?>
        <div style="display: inline-block; margin-right: 10px;">
            <ons-icon icon="md-radio-button-checked" style="color: #4CAF50; font-size: 20px;" title="<?php echo $langs->trans('ClockedIn'); ?>"></ons-icon>
        </div>
        <?php else: ?>
        <div style="display: inline-block; margin-right: 10px;">
            <ons-icon icon="md-radio-button-unchecked" style="color: #999; font-size: 20px;" title="<?php echo $langs->trans('NotClockedIn'); ?>"></ons-icon>
        </div>
        <?php endif; ?>
        
        <!-- Menu hamburger -->
        <ons-toolbar-button onclick="document.querySelector('#mySplitter').right.toggle();">
            <ons-icon icon="md-menu"></ons-icon>
        </ons-toolbar-button>
    </div>
</ons-toolbar>