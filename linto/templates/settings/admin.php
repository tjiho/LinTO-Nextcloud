
    <!-- Conteneur principal (obligatoire) -->
    <div class="section" id="linto-settings">

        <!-- Titre de section -->
        <h2>LinTO</h2>

        <!-- Description -->
        <p class="settings-hint">Configurez les paramètres de transcription.</p>

        <!-- Formulaire -->
        <form id="linto-settings-form">
            <div class="form-group">
                <label for="linto-api-url">URL de l'API LinTO</label>
                <input type="text"
                    id="linto-api-url"
                    name="apiUrl"
                    class="input"
                    placeholder="https://studio.linto.ai"
                    value="<?php p($_['apiUrl']); ?>"
                />
            </div>
            <div class="form-group">
                <label for="linto-organisation-id">ID Organisation</label>
                <input type="text"
                    id="linto-organisation-id"
                    name="organisationId"
                    class="input"
                    placeholder="ID de votre organisation LinTO"
                    value="<?php p($_['organisationId']); ?>"
                />
            </div>
            <div class="form-group">
                <label for="linto-api-key">Clé API</label>
                <input type="text"
                    id="linto-api-key"
                    name="apiKey"
                    class="input"
                    placeholder="Votre clé API LinTO"
                    value="<?php p($_['apiKey']); ?>"
                />
            </div>
            <button type="submit" class="button button-primary">
                Sauvegarder
            </button>
        </form>
    </div>
