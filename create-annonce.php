<?php 
require_once "./securiter/session.php";
require_once "./component/head.php";
?>

<main>
    <div class="create-annonce-container">
        <h2>Créer une nouvelle annonce</h2>
        
        <form id="create-annonce-form" enctype="multipart/form-data">

            <div class="form-group">
                <label for="annonce_title">Titre de l'annonce</label>
                <input type="text" id="annonce_title" name="annonce_title" required>
            </div>
            <div class="form-group">
                <label for="annonce_participant_number">Nombre de participant</label>
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
                    <!-- Categories will be loaded dynamically -->
                </select>
            </div>

            

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Créer l'annonce</button>
                <button type="reset" class="btn btn-secondary">Réinitialiser</button>
            </div>
        </form>

        <div id="message-container" class="message-container"></div>
    </div>
</main>

<script src="./static/js/postData.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('create-annonce-form');
    const messageContainer = document.getElementById('message-container');
    const imageInput = document.getElementById('annonce_image');
    const imagePreviewContainer = document.getElementById('image-preview-container');
    const imagePreview = document.getElementById('image-preview');
    const categorySelect = document.getElementById('category_id');
    
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

    // Charger les catégories depuis l'API
    async function loadCategories() {
        try {
            const requestData = {
                action: 'Categories_Get'
            };
            const data = await postData(apiUrl, requestData);

            // Ajouter les options des catégories au select
            data.categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.category_id;
                option.textContent = category.category_name;
                categorySelect.appendChild(option);
            });
        } catch (error) {
            console.error('Erreur de récupération des catégories :', error);
        }
    }

    // Initialiser la page
    loadCategories();

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const formData = new FormData();
        formData.append("action", "Annonce_Create");
        formData.append("user_id", <?php echo $_SESSION['user_id']; ?>);
        formData.append("annonce_title", document.getElementById("annonce_title").value.trim());
        formData.append("annonce_participant_number", document.getElementById("annonce_participant_number").value.trim());
        formData.append("annonce_description", document.getElementById("annonce_description").value.trim());
        formData.append("annonce_value", document.getElementById("annonce_value").value.trim());
        formData.append("category_id", document.getElementById("category_id").value.trim());

        const imageInput = document.getElementById("annonce_image");
        if (imageInput.files.length > 0) {
            formData.append("annonce_image", imageInput.files[0]);
        }

        try {
            const response = await fetch(apiUrl, {
                method: "POST",
                body: formData,
            });

            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

            const jsonResponse = await response.json();
            console.log("API Response:", jsonResponse);

            if (jsonResponse.success) {
                messageContainer.innerHTML = ` 
                    <div class="alert alert-success">
                        Annonce créée avec succès ! <a href="./">Voir le tableau de bord</a>
                    </div>
                `;
                form.reset();
                imagePreviewContainer.style.display = 'none';
            } else {
                messageContainer.innerHTML = `
                    <div class="alert alert-danger">
                        Erreur lors de la création de l'annonce : ${jsonResponse.error}
                    </div>
                `;
            }
        } catch (error) {
            console.error("Erreur:", error);
            messageContainer.innerHTML = `
                <div class="alert alert-danger">
                    Une erreur s'est produite : ${error.message}
                </div>
            `;
        }
    });
});
</script>

<?php require_once "./component/foot.php"; ?>
