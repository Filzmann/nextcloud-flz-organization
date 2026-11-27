<?php
/** @var array $_ */
/** @var OCP\IL10N $l */

script('flz_organization', 'admin');
style('flz_organization', 'admin');
?>

<div class="app-flz_organization admin-settings">
    <h2><?php p($l->t('Organisationsdienste-Verwaltung')); ?></h2>
    
    <div class="section">
        <h3><?php p($l->t('Konfigurationsstatus')); ?></h3>
        <div class="status-grid">
            <div class="status-card">
                <h4><?php p($l->t('Organisationsdefinition')); ?></h4>
                <p>
                    <strong><?php p($l->t('Status')); ?>:</strong>
                    <span class="status-badge <?= $_['status']['organization']['valid'] ? 'valid' : 'invalid' ?>">
                        <?php p($_['status']['organization']['valid'] ? $l->t('Gültig') : $l->t('Ungültig')); ?>
                    </span>
                </p>
                <p>
                    <strong><?php p($l->t('Persistiert')); ?>:</strong>
                    <span><?php p($_['status']['organization']['persisted'] ? $l->t('Ja') : $l->t('Nein')); ?></span>
                </p>
                <p>
                    <a href="<?php print_unescaped(link_to('flz_organization', 'admin/organization')); ?>" 
                       class="button">
                        <?php p($l->t('Organisation konfigurieren')); ?>
                    </a>
                </p>
            </div>
            
            <div class="status-card">
                <h4><?php p($l->t('Kalenderkontext')); ?></h4>
                <p>
                    <strong><?php p($l->t('Land')); ?>:</strong>
                    <span><?php p($_['status']['calendar']['countryCode']); ?></span>
                </p>
                <p>
                    <strong><?php p($l->t('Region')); ?>:</strong>
                    <span><?php p($_['status']['calendar']['subdivisionCode']); ?></span>
                </p>
                <p>
                    <strong><?php p($l->t('Zeitzone')); ?>:</strong>
                    <span><?php p($_['status']['calendar']['timezone']); ?></span>
                </p>
                <p>
                    <a href="<?php print_unescaped(link_to('flz_organization', 'admin/calendar')); ?>" 
                       class="button">
                        <?php p($l->t('Kalender konfigurieren')); ?>
                    </a>
                </p>
            </div>
            
            <div class="status-card">
                <h4><?php p($l->t('Autorisierungseinstellungen')); ?></h4>
                <p>
                    <strong><?php p($l->t('Status')); ?>:</strong>
                    <span class="status-badge <?= $_['status']['authorization']['valid'] ? 'valid' : 'invalid' ?>">
                        <?php p($_['status']['authorization']['valid'] ? $l->t('Gültig') : $l->t('Ungültig')); ?>
                    </span>
                </p>
                <p>
                    <strong><?php p($l->t('Kalender-Peer-Bearbeitung')); ?>:</strong>
                    <span><?php p(count($_['status']['authorization']['calendarPeerEditing'])); ?> <?php p($l->t('Rollen')); ?></span>
                </p>
                <p>
                    <strong><?php p($l->t('Urlaubs-Peer-Bearbeitung')); ?>:</strong>
                    <span><?php p(count($_['status']['authorization']['vacationPeerEditing'])); ?> <?php p($l->t('Rollen')); ?></span>
                </p>
                <p>
                    <strong><?php p($l->t('Urlaubs-Peer-Genehmigung')); ?>:</strong>
                    <span><?php p(count($_['status']['authorization']['vacationPeerApproval'])); ?> <?php p($l->t('Rollen')); ?></span>
                </p>
            </div>
        </div>
        
        <div class="overall-status">
            <strong><?php p($l->t('Gesamtkonfiguration')); ?>:</strong>
            <span class="status-badge <?= $_['status']['valid'] ? 'valid' : 'invalid' ?>">
                <?php p($_['status']['valid'] ? $l->t('Gültig') : $l->t('Ungültig')); ?>
            </span>
        </div>
    </div>
</div>
