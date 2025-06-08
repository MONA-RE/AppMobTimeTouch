<ons-toolbar modifier="material">
    <div class="left">
        <ons-back-button animation="fade" onclick="history.back()"></ons-back-button>
        <span id="titreValidationManager">
            <?php echo $langs->trans("ValidationManager"); ?>
        </span>
    </div>
    <div class="right">
        <!-- Actions spécifiques validation manager -->
        <?php if (isset($page_actions) && !empty($page_actions)): ?>
            <?php foreach ($page_actions as $action): ?>
                <ons-toolbar-button onclick="<?php echo $action['onclick']; ?>">
                    <ons-icon icon="<?php echo $action['icon']; ?>"></ons-icon>
                </ons-toolbar-button>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Actions par défaut validation manager -->
            <ons-toolbar-button onclick="refreshDashboard()" title="<?php echo $langs->trans('Refresh'); ?>">
                <ons-icon icon="md-refresh"></ons-icon>
            </ons-toolbar-button>
        <?php endif; ?>
        
        <!-- Badge notification validations en attente -->
        <?php if (isset($stats['total_pending']) && $stats['total_pending'] > 0): ?>
        <div style="display: inline-block; margin-right: 10px; position: relative;">
            <ons-icon icon="md-notifications" style="color: #FF9800; font-size: 20px;" title="<?php echo $stats['total_pending']; ?> validations en attente"></ons-icon>
            <ons-badge style="position: absolute; top: -8px; right: -8px; background-color: #f44336; font-size: 10px;">
                <?php echo $stats['total_pending']; ?>
            </ons-badge>
        </div>
        <?php endif; ?>
        
        <!-- Menu hamburger -->
        <ons-toolbar-button onclick="document.querySelector('#mySplitter').right.toggle();">
            <ons-icon icon="md-menu"></ons-icon>
        </ons-toolbar-button>
    </div>
</ons-toolbar>