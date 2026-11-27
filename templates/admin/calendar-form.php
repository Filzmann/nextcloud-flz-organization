<?php
/** @var array $_ */
/** @var OCP\IL10N $l */

script('flz_organization', 'admin');
style('flz_organization', 'admin');
?>

<div class="app-flz_organization admin-settings">
    <h2><?php p($l->t('Kalenderkontext')); ?></h2>
    
    <?php if (!empty($_['errors'])): ?>
        <div class="error-box">
            <h3><?php p($l->t('Validierungsfehler')); ?></h3>
            <ul>
                <?php foreach ($_['errors'] as $error): ?>
                    <li><?php p($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($_['saved'])): ?>
        <div class="success-box">
            <p><?php p($l->t('Kalenderkontext wurde erfolgreich gespeichert.')); ?></p>
        </div>
    <?php endif; ?>
    
    <form method="post" action="<?php print_unescaped(link_to('flz_organization', 'admin/calendar/save')); ?>">
        <input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']); ?>" />
        
        <div class="form-section">
            <h3><?php p($l->t('Kalendereinstellungen')); ?></h3>
            
            <div class="form-group">
                <label for="countryCode"><?php p($l->t('Ländercode')); ?></label>
                <select id="countryCode" name="countryCode" required>
                    <?php
                    $countries = [
                        'DE' => $l->t('Deutschland'),
                        'AT' => $l->t('Österreich'),
                        'CH' => $l->t('Schweiz'),
                    ];
                    foreach ($countries as $code => $name):
                        $selected = ($_['context']['countryCode'] ?? 'DE') === $code ? 'selected' : '';
                    ?>
                        <option value="<?php p($code); ?>" <?php p($selected); ?>><?php p($name); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="hint"><?php p($l->t('Ländercode nach ISO 3166-1 Alpha-2')); ?></p>
            </div>
            
            <div class="form-group">
                <label for="subdivisionCode"><?php p($l->t('Regionscode')); ?></label>
                <input type="text" id="subdivisionCode" name="subdivisionCode" 
                       value="<?php p($_['context']['subdivisionCode'] ?? ''); ?>" 
                       pattern="^[A-Z]{2}-[A-Z0-9]{1,3}$" 
                       maxlength="10" required />
                <p class="hint"><?php p($l->t('Regionscode nach ISO 3166-2 (z. B. DE-BE für Berlin)')); ?></p>
            </div>
            
            <div class="form-group">
                <label><?php p($l->t('Zeitzone')); ?></label>
                <p><strong><?php p($_['context']['timezone'] ?? 'UTC'); ?></strong></p>
                <p class="hint"><?php p($l->t('Die fachliche Zeitzone folgt immer der Instanz-Zeitzone aus den Nextcloud-Systemeinstellungen (config.php „timezone“) und gilt app-übergreifend für alle FLZ-Apps.')); ?></p>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="button button--primary">
                <?php p($l->t('Kalenderkontext speichern')); ?>
            </button>
            <a href="<?php print_unescaped(link_to('flz_organization', 'admin')); ?>" 
               class="button button--secondary">
                <?php p($l->t('Zurück zur Übersicht')); ?>
            </a>
        </div>
    </form>
</div>
