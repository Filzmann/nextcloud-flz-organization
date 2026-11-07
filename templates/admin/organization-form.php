<?php
/** @var array $_ */
/** @var OCP\IL10N $l */

use OCP\Util;

script('flz_organization', 'admin');
style('flz_organization', 'admin');
?>

<div class="app-flz_organization admin-settings">
    <h2><?php p($l->t('Organization Definition')); ?></h2>
    
    <?php if (!empty($_['errors'])): ?>
        <div class="error-box">
            <h3><?php p($l->t('Validation Errors')); ?></h3>
            <ul>
                <?php foreach ($_['errors'] as $error): ?>
                    <li><?php p($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($_['saved'])): ?>
        <div class="success-box">
            <p><?php p($l->t('Organization definition saved successfully.')); ?></p>
        </div>
    <?php endif; ?>
    
    <form method="post" action="<?php print_unescaped(link_to('flz_organization', 'admin/organization/save')); ?>">
        <input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']); ?>" />
        
        <div class="form-section">
            <h3><?php p($l->t('Basic Settings')); ?></h3>
            
            <div class="form-group">
                <label for="staffBlockLabel"><?php p($l->t('Staff Block Label')); ?></label>
                <input type="text" id="staffBlockLabel" name="staffBlockLabel" 
                       value="<?php p($_['definition']['staffBlockLabel'] ?? ''); ?>" 
                       maxlength="120" required />
                <p class="hint"><?php p($l->t('Label for the leadership and staff block')); ?></p>
            </div>
            
            <div class="form-group">
                <label for="teamGroupPrefix"><?php p($l->t('Team Group Prefix')); ?></label>
                <input type="text" id="teamGroupPrefix" name="teamGroupPrefix" 
                       value="<?php p($_['definition']['teamGroupPrefix'] ?? ''); ?>" 
                       maxlength="64" required />
                <p class="hint"><?php p($l->t('Prefix for team groups')); ?></p>
            </div>
            
            <div class="form-group">
                <label for="teamLabelPrefix"><?php p($l->t('Team Label Prefix')); ?></label>
                <input type="text" id="teamLabelPrefix" name="teamLabelPrefix" 
                       value="<?php p($_['definition']['teamLabelPrefix'] ?? ''); ?>" 
                       maxlength="64" required />
                <p class="hint"><?php p($l->t('Label prefix for team groups')); ?></p>
            </div>
            
            <div class="form-group">
                <label for="teamCodeMaxLength"><?php p($l->t('Team Code Maximum Length')); ?></label>
                <input type="number" id="teamCodeMaxLength" name="teamCodeMaxLength" 
                       value="<?php p($_['definition']['teamCodeMaxLength'] ?? 16); ?>" 
                       min="1" max="64" required />
                <p class="hint"><?php p($l->t('Maximum length for team codes')); ?></p>
            </div>
        </div>
        
        <div class="form-section">
            <h3><?php p($l->t('Roles')); ?></h3>
            <p class="hint"><?php p($l->t('Configure organization roles and their group mappings')); ?></p>
            
            <div id="roles-container" class="roles-grid">
                <?php $index = 0; ?>
                <?php foreach ($_['definition']['roles'] ?? [] as $key => $role): ?>
                    <div class="role-card" data-index="<?php p($index); ?>">
                        <input type="hidden" name="roles[<?php p($index); ?>][key]" value="<?php p($key); ?>" />
                        
                        <div class="form-group">
                            <label><?php p($l->t('Role Key')); ?></label>
                            <input type="text" name="roles[<?php p($index); ?>][roleKey]" 
                                   value="<?php p($key); ?>" readonly />
                        </div>
                        
                        <div class="form-group">
                            <label><?php p($l->t('Group ID')); ?></label>
                            <input type="text" name="roles[<?php p($index); ?>][groupId]" 
                                   value="<?php p($role['groupId'] ?? ''); ?>" required />
                        </div>
                        
                        <div class="form-group">
                            <label><?php p($l->t('Label')); ?></label>
                            <input type="text" name="roles[<?php p($index); ?>][label]" 
                                   value="<?php p($role['label'] ?? ''); ?>" required />
                        </div>
                        
                        <div class="form-group">
                            <label><?php p($l->t('Short Label')); ?></label>
                            <input type="text" name="roles[<?php p($index); ?>][shortLabel]" 
                                   value="<?php p($role['shortLabel'] ?? $role['label'] ?? ''); ?>" />
                        </div>
                        
                        <div class="form-group">
                            <label><?php p($l->t('Sort Order')); ?></label>
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
                <?php p($l->t('Save Organization Definition')); ?>
            </button>
            <a href="<?php print_unescaped(link_to('flz_organization', 'admin')); ?>" 
               class="button button--secondary">
                <?php p($l->t('Back to Overview')); ?>
            </a>
        </div>
    </form>
</div>
