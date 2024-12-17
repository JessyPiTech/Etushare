<?php  
require_once "./securiter/session.php";
require_once "./component/head.php";
?>

<main>
    <div class="update-annonce-container">
        <h2>Modifier l'annonce</h2>
        
        <form id="update-annonce-form" enctype="multipart/form-data">

            <div class="form-group">
                <label for="annonce_title">Titre de l'annonce</label>
                <input type="text" id="annonce_title" name="annonce_title" required>
            </div>
            <div class="form-group">
                <label for="annonce_participant_number">Nombre de participants</label>
                <input type="number" id="annonce_participant_number" name="annonce_participant_number" step="1" required>
            </div>
            
            <div class="form-group">
                <label for="annonce_description">Description</label>
                <textarea id="annonce_description" name="annonce_description" rows="4" required></textarea>
            </div>

            <div class="form-group">
                <label for="annonce_value">Valeur estimée (€)</label>
                <input type="number" id="annonce_value" name="annonce_value" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="annonce_image">Image de l'annonce</label>
                <input type="file" id="annonce_image" name="annonce_image" accept="image/jpeg,image/png,image/gif">
                <div id="image-preview-container" style="display: none;">
                    <img id="image-preview" src="#" alt="Image preview" style="max-width: 300px; max-height: 300px;">
                </div>
            </div>

            <div class="form-group">
                <label for="category_id">Catégorie</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Sélectionnez une catégorie</option>
                    <option value="1">Technologie</option>
                    <option value="2">Meubles</option>
                    <option value="3">Vêtements</option>
                    <option value="4">Autres</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Mettre à jour l'annonce</button>
                <button type="reset" class="btn btn-secondary">Réinitialiser</button>
            </div>
        </form>

        <div id="message-container" class="message-container"></div>
    </div>
</main>

<script src="./static/js/postData.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('update-annonce-form');
    const messageContainer = document.getElementById('message-container');
    const imageInput = document.getElementById('annonce_image');
    const imagePreviewContainer = document.getElementById('image-preview-container');
    const imagePreview = document.getElementById('image-preview');
    const user_id = '<?php echo $_SESSION['user_id'] ?>';


    async function populateForm() {
        const annonceId = new URLSearchParams(window.location.search).get("annonce_id");
        if (!annonceId) {
            messageContainer.innerHTML = `<div class="alert alert-danger">ID d'annonce manquant.</div>`;
            return;
        }

        const data = await postData(apiUrl, {
            action: "Annonce_Detail",
            annonce_id: annonceId,
        });

        if (data.error) {
            messageContainer.innerHTML = `<div class="alert alert-danger">Erreur lors du chargement des données : ${data.error}</div>`;
            return;
        }

        document.getElementById("annonce_title").value = data.annonce.annonce_title || '';
        document.getElementById("annonce_participant_number").value = data.annonce.annonce_participant_number || '';
        document.getElementById("annonce_description").value = data.annonce.annonce_description || '';
        document.getElementById("annonce_value").value = data.annonce.annonce_value || '';
        document.getElementById("category_id").value = data.annonce.category_id || '';

        if (data.annonce.image_lien) {
            imagePreview.src = data.annonce.image_lien;
            imagePreviewContainer.style.display = 'block';
        }
    }
    populateForm();

    imageInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreviewContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const formData = new FormData(form);
        formData.append("action", "Annonce_Update");
        formData.append("user_id", user_id);
        formData.append("annonce_id", new URLSearchParams(window.location.search).get("annonce_id"));

        try {
            const response = await fetch(apiUrl, {
                method: "POST",
                body: formData,
            });

            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const jsonResponse = await response.json();

            if (jsonResponse.success) {
                messageContainer.innerHTML = `
                    <div class="alert alert-success">
                        Annonce mise à jour avec succès ! <a href="./">Voir le tableau de bord</a>
                    </div>
                `;
                form.reset();
                imagePreviewContainer.style.display = 'none';
            } else {
                messageContainer.innerHTML = `<div class="alert alert-danger">${jsonResponse.error}</div>`;
            }
        } catch (error) {
            console.error("Error:", error);
            messageContainer.innerHTML = `<div class="alert alert-danger">Erreur : ${error.message}</div>`;
        }
    });
});
</script>

<?php require_once "./component/foot.php"; ?>