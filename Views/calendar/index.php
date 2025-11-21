<section class="calendars-list container">
    <div class="header-section">
        <h1>Mes calendriers</h1>
        <a href="/calendars/create" class="btn btn-fond-fonce">➕ Créer un nouveau calendrier</a>
    </div>

    <?php if (empty($calendars)): ?>
        <div class="empty-state">
            <p>🎄 Vous n'avez pas encore de calendrier de l'Avent.</p>
            <p>Créez votre premier calendrier pour commencer !</p>
            <a href="/calendars/create" class="btn btn-fond-clair">Créer mon premier calendrier</a>
        </div>
    <?php else: ?>
        <div class="calendars-grid">
            <?php foreach ($calendars as $calendar): ?>
                <div class="calendar-card">
                    <div class="calendar-header">
                        <h3><?php echo htmlspecialchars($calendar['title']) ?></h3>
                    </div>

                    <div class="calendar-body">
                        <div class="calendar-theme" style="background-image: url('/assets/images/themes/<?php echo htmlspecialchars($calendar['image_path']) ?>')">
                            <span class="theme-label"><?php echo htmlspecialchars($calendar['theme_name']) ?></span>
                        </div>

                        <div class="calendar-info">
                            <p>📅 Créé le
                                <?php echo date('d/m/Y', strtotime($calendar['created_at'])) ?></p>
                        </div>
                    </div>

                    <div class="calendar-actions">
                        <a href="/calendars/<?php echo $calendar['calendar_id'] ?>" class="btn btn-sm btn-fond-clair">
                            👁️ Voir
                        </a>
                        <a href="/calendars/<?php echo $calendar['calendar_id'] ?>/edit" class="btn btn-sm btn-fond-clair">
                            ✏️ Modifier
                        </a>
                        <a href="/calendars/<?php echo $calendar['calendar_id'] ?>/share" class="btn btn-sm btn-fond-fonce">
                            🔗 Partager
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>


