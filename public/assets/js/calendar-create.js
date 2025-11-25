// calendar-create.js

// Stocker temporairement les surprises
let surprises = {};
let currentDay = null;

/**
 * Ouvrir la modal pour ajouter/modifier une surprise
 */
function openSurpriseModal(day) {
    currentDay = day;

    // Mettre à jour le titre
    document.getElementById('modalDay').textContent = day;

    // Pré-remplir si la surprise existe déjà
    if (surprises[day]) {
        document.getElementById('surpriseType').value = surprises[day].type;
        document.getElementById('surpriseContent').value = surprises[day].content;
    } else {
        document.getElementById('surpriseType').value = '';
        document.getElementById('surpriseContent').value = '';
    }

    // Afficher la modal
    document.getElementById('surpriseModal').style.display = 'flex';
}

/**
 * Fermer la modal
 */
function closeSurpriseModal() {
    document.getElementById('surpriseModal').style.display = 'none';
    currentDay = null;
}

/**
 * Enregistrer la surprise (temporairement en JavaScript)
 */
function saveSurprise() {
    const type = document.getElementById('surpriseType').value;
    const content = document.getElementById('surpriseContent').value;

    // Validation
    if (!type || !content.trim()) {
        alert('Veuillez remplir tous les champs');
        return;
    }

    // Stocker dans l'objet
    surprises[currentDay] = {
        type: type,
        content: content
    };

    // Marquer la case comme remplie
    const caseBtn = document.querySelector(`.case-btn[data-day="${currentDay}"]`);
    caseBtn.classList.add('filled');

    // Fermer la modal
    closeSurpriseModal();

    console.log('Surprises actuelles:', surprises);
}

/**
 * Avant de soumettre le formulaire, ajouter les surprises en champs cachés
 */
document.getElementById('calendarForm').addEventListener('submit', function (e) {
    const surprisesDataDiv = document.getElementById('surprisesData');
    surprisesDataDiv.innerHTML = ''; // Vider

    // Créer un champ caché pour chaque surprise
    for (let day in surprises) {
        const surprise = surprises[day];

        // Input pour le type
        const typeInput = document.createElement('input');
        typeInput.type = 'hidden';
        typeInput.name = `surprises[${day}][type]`;
        typeInput.value = surprise.type;
        surprisesDataDiv.appendChild(typeInput);

        // Input pour le contenu
        const contentInput = document.createElement('input');
        contentInput.type = 'hidden';
        contentInput.name = `surprises[${day}][content]`;
        contentInput.value = surprise.content;
        surprisesDataDiv.appendChild(contentInput);
    }
});

// Mettre à jour le titre de l'aperçu en temps réel
document.getElementById('title')?.addEventListener('input', function (e) {
    document.getElementById('calendarTitle').textContent = e.target.value || 'Joyeux noël !';
});

/**
 * Changer l'aperçu du calendrier selon le thème sélectionné
 */
document.querySelectorAll('input[name="theme_id"]').forEach(radio => {
    radio.addEventListener('change', function () {
        const imagePath = this.dataset.image;
        const calendarEdit = document.getElementById('calendarEdit');
        calendarEdit.style.backgroundImage = `url('${imagePath}')`;
        calendarEdit.style.backgroundSize = 'cover';
        calendarEdit.style.backgroundPosition = 'center';

        document.querySelectorAll('.calendar-preview').forEach(preview => {
            preview.style.backgroundImage = `url('${imagePath}')`;
        });
    });
});

/**
 * Changer le style des cases de l'aperçu selon le choix
 */
document.querySelectorAll('input[name="case_style"]').forEach(radio => {
    radio.addEventListener('change', function () {
        const selectedStyle = this.value;
        const calendarEdit = document.getElementById('calendarEdit');

        // Retirer tous les styles
        calendarEdit.classList.remove('style1', 'style2');

        // Appliquer le style choisi
        calendarEdit.classList.add(selectedStyle);
    });
});

// Initialiser avec le style par défaut (style1)
document.addEventListener('DOMContentLoaded', function () {
    const calendarEdit = document.getElementById('calendarEdit');
    calendarEdit.classList.add('style1');
});

// Fermer la modal en cliquant en dehors
window.onclick = function (event) {
    const modal = document.getElementById('surpriseModal');
    if (event.target === modal) {
        closeSurpriseModal();
    }
}