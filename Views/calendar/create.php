<section class="creation-section container">
    <h1>Mon calendrier</h1>

    <form method="POST" action="/calendars" id="calendarForm">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token) ?>">

        <!-- 1. TITRE -->
        <div class="section-box">
            <label for="title">Titre de votre calendrier :</label>
            <input
                type="text"
                id="title"
                name="title"
                placeholder="Ex : Joyeux noël Alex !"
                required
            >
        </div>

        <!-- 2. CHOIX DU THÈME -->
        <div class="section-box">
            <h2>Choisir un thème :</h2>
            <div class="themes-grid">
                <?php foreach ($themes as $theme): ?>
                    <label class="theme-option">
                        <input
                            type="radio"
                            name="theme_id"
                            value="<?php echo $theme['theme_id'] ?>"
                            data-image="<?php echo htmlspecialchars($theme['image_path']) ?>"
                            required
                        >
                        <img src="/assets/images/themes/<?php echo htmlspecialchars($theme['image_path']) ?>" alt="<?php echo htmlspecialchars($theme['theme_name']) ?>">
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 3. ASSOCIATION CASES AU THÈME (Aperçu visuel) -->
        <div class="section-box">
            <h2>Associer les cases au thème choisi :</h2>
            <div class="calendar-preview" id="calendarPreview">
                <?php for ($day = 1; $day <= 24; $day++): ?>
                    <div class="preview-case"><?php echo $day ?></div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- 4. REMPLIR LES 24 CASES -->
        <div class="section-box">
            <div class="remplir-section">
                <div class="apercu-box">
                    <h2>Joyeux noël Alex !</h2>
                    <div class="calendar-edit" id="calendarEdit">
                        <?php for ($day = 1; $day <= 24; $day++): ?>
                            <div class="edit-case" data-day="<?php echo $day ?>">
                                <span class="day-number"><?php echo $day ?></span>
                                <span class="edit-icon" style="display: none;">✏️</span>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="grille-box">
                    <h2>Remplir les 24 cases :</h2>
                    <div class="cases-grid">
                        <?php for ($day = 1; $day <= 24; $day++): ?>
                            <button
                                type="button"
                                class="case-btn"
                                data-day="<?php echo $day ?>"
                                onclick="openSurpriseModal(<?php echo $day ?>)"
                            >
                                <span class="day-num"><?php echo $day ?></span>
                                <span class="edit-badge" id="badge-<?php echo $day ?>" style="display: none;">✏️</span>
                            </button>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Champs cachés pour les surprises (remplis par JavaScript) -->
        <div id="surprisesData"></div>

        <!-- BOUTONS -->
        <div class="form-actions">
            <a href="/calendars" class="btn btn-annuler">Annuler</a>
            <button type="submit" class="btn btn-fond-fonce">Enregistrer le calendrier</button>
        </div>
    </form>
</section>

<!-- MODAL POUR AJOUTER UNE SURPRISE -->
<div id="surpriseModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeSurpriseModal()">&times;</span>
        <h2 id="modalTitle">Surprise du jour <span id="modalDay"></span></h2>

        <div class="modal-form">
            <label for="surpriseType">Type de surprise :</label>
            <select id="surpriseType" required>
                <option value="">Choisir un type</option>
                <option value="text">Texte</option>
                <option value="image">Image</option>
                <option value="video">Vidéo</option>
                <option value="link">Lien</option>
            </select>

            <label for="surpriseContent">Contenu :</label>
            <textarea
                id="surpriseContent"
                placeholder="Écrivez votre surprise ici..."
                rows="5"
                required
            ></textarea>

            <div class="modal-actions">
                <button type="button" class="btn btn-annuler" onclick="closeSurpriseModal()">Annuler</button>
                <button type="button" class="btn btn-fond-fonce" onclick="saveSurprise()">Enregistrer la surprise</button>
            </div>
        </div>
    </div>
</div>