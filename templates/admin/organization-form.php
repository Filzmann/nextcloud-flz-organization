<?php
/** @var array $_ */
/** @var OCP\IL10N $l */

use OCP\Util;

script('flz_organization', 'admin');
style('flz_organization', 'admin');
?>

<div class="app-flz_organization admin-settings">
    <h2><?php p($l->t('Organisationsdefinition')); ?></h2>
    
    <?php if (!empty($_['errors'])): ?>
        <div class="error-box">
            <h3><?php p($l->t('Validierungsfehler')); ?></h3>
            <ul>
                <?php foreach ($_['errors'] as $error): ?>
                    <li><?php p($l->t($error)); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($_['saved'])): ?>
        <div class="success-box">
            <p><?php p($l->t('Organisationsdefinition wurde erfolgreich gespeichert.')); ?></p>
        </div>
    <?php endif; ?>
    
    <form method="post" action="<?php print_unescaped(link_to('flz_organization', 'admin/organization/save')); ?>">
        <input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']); ?>" />
        
        <div class="form-section">
            <h3><?php p($l->t('Grundeinstellungen')); ?></h3>
            
            <div class="form-group">
                <label for="staffBlockLabel"><?php p($l->t('Bezeichnung Mitarbeiterblock')); ?></label>
                <input type="text" id="staffBlockLabel" name="staffBlockLabel" 
                       value="<?php p($_['definition']['staffBlockLabel'] ?? ''); ?>" 
                       maxlength="120" required />
                <p class="hint"><?php p($l->t('Bezeichnung für den Leitungs- und Mitarbeiterblock')); ?></p>
            </div>
            
            <div class="form-group">
                <label for="teamGroupPrefix"><?php p($l->t('Teamgruppen-Präfix')); ?></label>
                <input type="text" id="teamGroupPrefix" name="teamGroupPrefix" 
                       value="<?php p($_['definition']['teamGroupPrefix'] ?? ''); ?>" 
                       maxlength="64" required />
                <p class="hint"><?php p($l->t('Präfix für Teamgruppen')); ?></p>
            </div>
            
            <div class="form-group">
                <label for="teamLabelPrefix"><?php p($l->t('Teambezeichnungs-Präfix')); ?></label>
                <input type="text" id="teamLabelPrefix" name="teamLabelPrefix" 
                       value="<?php p($_['definition']['teamLabelPrefix'] ?? ''); ?>" 
                       maxlength="64" required />
                <p class="hint"><?php p($l->t('Bezeichnungsschema für Teamgruppen')); ?></p>
            </div>
            
            <div class="form-group">
                <label for="teamCodeMaxLength"><?php p($l->t('Maximale Teamcode-Länge')); ?></label>
                <input type="number" id="teamCodeMaxLength" name="teamCodeMaxLength" 
                       value="<?php p($_['definition']['teamCodeMaxLength'] ?? 16); ?>" 
                       min="1" max="64" required />
                <p class="hint"><?php p($l->t('Maximale Länge für Teamcodes')); ?></p>
            </div>
        </div>
        
        <div class="form-section">
            <h3><?php p($l->t('Rollen')); ?></h3>
            <p class="hint"><?php p($l->t('Organisationsrollen und ihre Gruppenzuordnungen konfigurieren')); ?></p>
            
            <div id="roles-container" class="roles-grid">
                <?php $index = 0; ?>
                <?php foreach ($_['definition']['roles'] ?? [] as $key => $role): ?>
                    <div class="role-card" data-index="<?php p($index); ?>">
                        <input type="hidden" name="roles[<?php p($index); ?>][key]" value="<?php p($key); ?>" />
                        
                        <div class="form-group">
                            <label><?php p($l->t('Rollenschlüssel')); ?></label>
                            <input type="text" name="roles[<?php p($index); ?>][roleKey]" 
                                   value="<?php p($key); ?>" readonly />
                        </div>
                        
                        <div class="form-group">
                            <label><?php p($l->t('Gruppen-ID')); ?></label>
                            <input type="text" name="roles[<?php p($index); ?>][groupId]" 
                                   value="<?php p($role['groupId'] ?? ''); ?>" required />
                        </div>
                        
                        <div class="form-group">
                            <label><?php p($l->t('Bezeichnung')); ?></label>
                            <input type="text" name="roles[<?php p($index); ?>][label]" 
                                   value="<?php p($role['label'] ?? ''); ?>" required />
                        </div>
                        
                        <div class="form-group">
                            <label><?php p($l->t('Kurzbezeichnung')); ?></label>
                            <input type="text" name="roles[<?php p($index); ?>][shortLabel]" 
                                   value="<?php p($role['shortLabel'] ?? $role['label'] ?? ''); ?>" />
                        </div>
                        
                        <div class="form-group">
                            <label><?php p($l->t('Sortierreihenfolge')); ?></label>
                            <input type="number" name="roles[<?php p($index); ?>][sortOrder]" 
                                   value="<?php p($role['sortOrder'] ?? 0); ?>" />
                        </div>
                        
                        <?php $index++; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="button button--primary">
                <?php p($l->t('Organisationsdefinition speichern')); ?>
            </button>
            <a href="<?php print_unescaped(link_to('flz_organization', 'admin')); ?>" 
               class="button button--secondary">
                <?php p($l->t('Zurück zur Übersicht')); ?>
            </a>
        </div>
    </form>
</div>
