
    <!-- Conteneur principal (obligatoire) -->
    <div class="section" id="linto-settings">

        <!-- Titre de section -->
        <h2>LinTO</h2>

        <!-- Description -->
        <p class="settings-hint"><?php p($l->t('Configure the transcription settings.')); ?></p>

        <!-- Formulaire -->
        <form id="linto-settings-form">
            <div class="form-group">
                <label for="linto-api-url"><?php p($l->t('LinTO API URL')); ?></label>
                <input type="text"
                    id="linto-api-url"
                    name="apiUrl"
                    class="input"
                    placeholder="https://studio.linto.ai"
                    value="<?php p($_['apiUrl']); ?>"
                />
            </div>
            <div class="form-group">
                <label for="linto-organisation-id"><?php p($l->t('Organisation ID')); ?></label>
                <input type="text"
                    id="linto-organisation-id"
                    name="organisationId"
                    class="input"
                    placeholder="<?php p($l->t('Your LinTO organisation ID')); ?>"
                    value="<?php p($_['organisationId']); ?>"
                />
            </div>
            <div class="form-group">
                <label for="linto-api-key"><?php p($l->t('API Key')); ?></label>
                <input type="text"
                    id="linto-api-key"
                    name="apiKey"
                    class="input"
                    placeholder="<?php p($l->t('Your LinTO API key')); ?>"
                    value="<?php p($_['apiKey']); ?>"
                />
            </div>
            <button type="submit" class="button button-primary">
                <?php p($l->t('Save')); ?>
            </button>
        </form>
    </div>
