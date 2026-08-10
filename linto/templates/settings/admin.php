
    <!-- Conteneur principal (obligatoire) -->
    <div class="section" id="linto-settings">

        <!-- Titre de section -->
        <h2>LinTO</h2>

        <!-- Description -->
        <p class="settings-hint">Configurez les paramètres de transcription.</p>

        <!-- Formulaire -->
        <form id="linto-settings-form">
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
