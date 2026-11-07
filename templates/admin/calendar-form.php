<?php
/** @var array $_ */
/** @var OCP\IL10N $l */

script('flz_organization', 'admin');
style('flz_organization', 'admin');
?>

<div class="app-flz_organization admin-settings">
    <h2><?php p($l->t('Calendar Context')); ?></h2>
    
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
            <p><?php p($l->t('Calendar context saved successfully.')); ?></p>
        </div>
    <?php endif; ?>
    
    <form method="post" action="<?php print_unescaped(link_to('flz_organization', 'admin/calendar/save')); ?>">
        <input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']); ?>" />
        
        <div class="form-section">
            <h3><?php p($l->t('Calendar Settings')); ?></h3>
            
            <div class="form-group">
                <label for="countryCode"><?php p($l->t('Country Code')); ?></label>
                <select id="countryCode" name="countryCode" required>
                    <?php
                    $countries = [
                        'DE' => $l->t('Germany'),
                        'AT' => $l->t('Austria'),
                        'CH' => $l->t('Switzerland'),
                    ];
                    foreach ($countries as $code => $name):
                        $selected = ($_['context']['countryCode'] ?? 'DE') === $code ? 'selected' : '';
                    ?>
                        <option value="<?php p($code); ?>" <?php p($selected); ?>><?php p($name); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="hint"><?php p($l->t('ISO 3166-1 alpha-2 country code')); ?></p>
            </div>
            
            <div class="form-group">
                <label for="subdivisionCode"><?php p($l->t('Subdivision Code')); ?></label>
                <input type="text" id="subdivisionCode" name="subdivisionCode" 
                       value="<?php p($_['context']['subdivisionCode'] ?? ''); ?>" 
                       pattern="^[A-Z]{2}-[A-Z0-9]{1,3}$" 
                       maxlength="10" required />
                <p class="hint"><?php p($l->t('ISO 3166-2 subdivision code (e.g., DE-BE for Berlin)')); ?></p>
            </div>
            
            <div class="form-group">
                <label for="timezone"><?php p($l->t('Timezone')); ?></label>
                <select id="timezone" name="timezone" required>
                    <?php
                    $timezones = [
                        'Europe/Berlin' => '(GMT+1) Berlin',
                        'Europe/Vienna' => '(GMT+1) Vienna',
                        'Europe/Zurich' => '(GMT+1) Zurich',
                        'UTC' => 'UTC',
                    ];
                    foreach ($timezones as $tz => $label):
                        $selected = ($_['context']['timezone'] ?? 'Europe/Berlin') === $tz ? 'selected' : '';
                    ?>
                        <option value="<?php p($tz); ?>" <?php p($selected); ?>><?php p($label); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="hint"><?php p($l->t('IANA timezone identifier')); ?></p>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="button button--primary">
                <?php p($l->t('Save Calendar Context')); ?>
            </button>
            <a href="<?php print_unescaped(link_to('flz_organization', 'admin')); ?>" 
               class="button button--secondary">
                <?php p($l->t('Back to Overview')); ?>
            </a>
        </div>
    </form>
</div>
