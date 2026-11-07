<?php
/** @var array $_ */
/** @var OCP\IL10N $l */

script('flz_organization', 'admin');
style('flz_organization', 'admin');
?>

<div class="app-flz_organization admin-settings">
    <h2><?php p($l->t('Organization Services Administration')); ?></h2>
    
    <div class="section">
        <h3><?php p($l->t('Configuration Status')); ?></h3>
        <div class="status-grid">
            <div class="status-card">
                <h4><?php p($l->t('Organization Definition')); ?></h4>
                <p>
                    <strong><?php p($l->t('Status')); ?>:</strong>
                    <span class="status-badge <?= $_['status']['organization']['valid'] ? 'valid' : 'invalid' ?>">
                        <?php p($_['status']['organization']['valid'] ? $l->t('Valid') : $l->t('Invalid')); ?>
                    </span>
                </p>
                <p>
                    <strong><?php p($l->t('Persisted')); ?>:</strong>
                    <span><?php p($_['status']['organization']['persisted'] ? $l->t('Yes') : $l->t('No')); ?></span>
                </p>
                <p>
                    <a href="<?php print_unescaped(link_to('flz_organization', 'admin/organization')); ?>" 
                       class="button">
                        <?php p($l->t('Configure Organization')); ?>
                    </a>
                </p>
            </div>
            
            <div class="status-card">
                <h4><?php p($l->t('Calendar Context')); ?></h4>
                <p>
                    <strong><?php p($l->t('Country')); ?>:</strong>
                    <span><?php p($_['status']['calendar']['countryCode']); ?></span>
                </p>
                <p>
                    <strong><?php p($l->t('Subdivision')); ?>:</strong>
                    <span><?php p($_['status']['calendar']['subdivisionCode']); ?></span>
                </p>
                <p>
                    <strong><?php p($l->t('Timezone')); ?>:</strong>
                    <span><?php p($_['status']['calendar']['timezone']); ?></span>
                </p>
                <p>
                    <a href="<?php print_unescaped(link_to('flz_organization', 'admin/calendar')); ?>" 
                       class="button">
                        <?php p($l->t('Configure Calendar')); ?>
                    </a>
                </p>
            </div>
            
            <div class="status-card">
                <h4><?php p($l->t('Authorization Settings')); ?></h4>
                <p>
                    <strong><?php p($l->t('Status')); ?>:</strong>
                    <span class="status-badge <?= $_['status']['authorization']['valid'] ? 'valid' : 'invalid' ?>">
                        <?php p($_['status']['authorization']['valid'] ? $l->t('Valid') : $l->t('Invalid')); ?>
                    </span>
                </p>
                <p>
                    <strong><?php p($l->t('Calendar Peer Editing')); ?>:</strong>
                    <span><?php p(count($_['status']['authorization']['calendarPeerEditing'])); ?> <?php p($l->t('roles')); ?></span>
                </p>
                <p>
                    <strong><?php p($l->t('Vacation Peer Editing')); ?>:</strong>
                    <span><?php p(count($_['status']['authorization']['vacationPeerEditing'])); ?> <?php p($l->t('roles')); ?></span>
                </p>
                <p>
                    <strong><?php p($l->t('Vacation Peer Approval')); ?>:</strong>
                    <span><?php p(count($_['status']['authorization']['vacationPeerApproval'])); ?> <?php p($l->t('roles')); ?></span>
                </p>
            </div>
        </div>
        
        <div class="overall-status">
            <strong><?php p($l->t('Overall Configuration')); ?>:</strong>
            <span class="status-badge <?= $_['status']['valid'] ? 'valid' : 'invalid' ?>">
                <?php p($_['status']['valid'] ? $l->t('Valid') : $l->t('Invalid')); ?>
            </span>
        </div>
    </div>
</div>
