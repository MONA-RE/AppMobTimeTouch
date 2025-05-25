<ons-toolbar modifier="material">
    <div class="left">
        <ons-back-button animation="fade"></ons-back-button>
        <span id="titreAppMobTimeTouch">
            <?php echo $langs->trans($title ?? "AppMobTimeTouch"); ?>
        </span>
    </div>
    <div class="right">
        <!-- Optional actions based on page context -->
        <?php if (isset($page_actions) && !empty($page_actions)): ?>
            <?php foreach ($page_actions as $action): ?>
                <ons-toolbar-button onclick="<?php echo $action['onclick']; ?>">
                    <ons-icon icon="<?php echo $action['icon']; ?>"></ons-icon>
                </ons-toolbar-button>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <!-- Default menu access -->
        <ons-toolbar-button onclick="document.querySelector('#mySplitter').right.toggle();">
            <ons-icon icon="md-menu"></ons-icon>
        </ons-toolbar-button>
    </div>
</ons-toolbar>