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
    document.getElementById('surpriseModal').style.display = 'block';
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

    // Afficher le badge ✏️
    document.getElementById('badge-' + currentDay).style.display = 'inline';

    // Afficher l'icône dans l'aperçu
    const editCase = document.querySelector(`.edit-case[data-day="${currentDay}"] .edit-icon`);
    if (editCase) {
        editCase.style.display = 'inline';
    }

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

/**
 * Changer l'aperçu du calendrier selon le thème sélectionné
 */
document.querySelectorAll('input[name="theme_id"]').forEach(radio => {
    radio.addEventListener('change', function () {
        const imagePath = this.dataset.image;
        const preview = document.getElementById('calendarPreview');
        preview.style.backgroundImage = `url('/assets/images/themes/${imagePath}')`;
    });
});

// Fermer la modal en cliquant en dehors
window.onclick = function (event) {
    const modal = document.getElementById('surpriseModal');
    if (event.target === modal) {
        closeSurpriseModal();
    }
}