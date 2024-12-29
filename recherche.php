<?php 
require_once "./securiter/session.php";
require_once "./component/head.php";
?>

<style>
main {
    margin-top: 5em;
}
.pagination {
    display: flex;
    justify-content: center;
    gap: 5px;
    margin-top: 20px;
}

.pagination button {
    padding: 5px 10px;
    border: 1px solid #ddd;
    background-color: #f9f9f9;
    cursor: pointer;
}

.pagination button.active {
    font-weight: bold;
    background-color: #e0e0e0;
}

.search-controls {
    display: flex;
    justify-content: center;
    gap: 1em;
    margin-bottom: 2em;
}

.search-controls input {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 300px;
}

.search-controls select {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.search-controls button {
    padding: 8px 16px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
</style>

<main>
    <div class="search-controls">
        <input type="text" id="searchInput" placeholder="Rechercher...">
        <select id="categorySelect">
            <option value="0">Toutes les catégories</option>
            <!-- Categories will be loaded dynamically -->
        </select>
        <button onclick="performSearch()">Rechercher</button>
    </div>

    <div id="search-results" class="groupe_annonce flex-complete">
    </div>

    <div id="pagination-controls" class="pagination">
    </div>
</main>

<script src="./static/js/postData.js"></script>
<script src="./static/js/likeParticipation.js"></script>
<script src="./static/js/displayAnnonces.js"></script>

<script>
    const user_id = '<?php echo $_SESSION['user_id'] ?>';
    const defaultImage = './upload/default.png';
    const limit = 10;
    let currentPage = 1;
    let totalPages = 1;
    const urlParams = new URLSearchParams(window.location.search);
    const searchQuery = urlParams.get('Recherche') || '';
    const categoryId = urlParams.get('Category') || '0';
    document.getElementById('searchInput').value = searchQuery;
    document.getElementById('categorySelect').value = categoryId;
    

    async function loadCategories() {
        try {
            const requestData = {
                action: 'Categories_Get'
            };
            const data = await postData(apiUrl, requestData);
            const categorySelect = document.getElementById('categorySelect');
            
            data.categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.category_id;
                option.textContent = category.category_name;
                categorySelect.appendChild(option);
            });
            categorySelect.value = categoryId;
        } catch (error) {
            console.error('Failed to load categories:', error);
        }
    }

    async function loadSearchResults(page = 1) {
        const searchInput = document.getElementById('searchInput');
        const categorySelect = document.getElementById('categorySelect');
        const container = document.getElementById('search-results');
        const offset = (page - 1) * limit;

        container.innerHTML = '<div class="loading">Chargement...</div>';

        try {
            const requestData = {
                action: 'Search_Annonces',
                search: searchInput.value,
                category_id: categorySelect.value,
                offset: offset,
                limit: limit
            };

            const data = await postData(apiUrl, requestData);
            
            if (data.error) throw new Error(data.error);

            const items = data.annonces || [];
            totalPages = Math.ceil((data.totalCount || 0) / limit) || 1;

            container.innerHTML = '';

            if (items.length > 0) {
                await displayAnnonces(user_id, items, 'search-results', {
                    displayType: 'grid',
                    imageSize: 'big',
                    clear: true
                });
                updatePagination();
            } else {
                container.innerHTML = '<div class="no-data">Aucune annonce trouvée.</div>';
            }
        } catch (error) {
            console.error('Failed to load search results:', error);
            container.innerHTML = '<div class="error">Une erreur est survenue lors de la recherche.</div>';
        }
    }

    async function displayResults(items) {
        const container = document.getElementById('search-results');
        
        for (const item of items) {
            const li = document.createElement('li');
            li.classList.add('annonce-item');

            const title = document.createElement('h4');
            title.textContent = item.annonce_title || 'No title';

            const description = document.createElement('p');
            description.textContent = item.annonce_description || 'No description available';

            const value = document.createElement('p');
            value.textContent = `Value: ${item.annonce_value !== undefined ? item.annonce_value : 'N/A'} point`;

            const category = document.createElement('p');
            category.textContent = `Category: ${item.category_name || 'No category'}`;
            category.classList.add('category-name');

            const image = document.createElement('img');
            image.src = item.image_path || defaultImage;
            image.alt = item.image_name || 'Image not available';
            image.classList.add('big-annonce-image');

            const interactionDiv = await affichageInteraction(item, user_id);

            const divContainer = document.createElement('div');
            divContainer.classList.add('annonce-details');
            divContainer.addEventListener('click', () => {
                window.location.href = `annonce-detail.php?annonce_id=${item.annonce_id}`;
            });
            divContainer.style.cursor = 'pointer';

            divContainer.appendChild(image);
            divContainer.appendChild(title);
            divContainer.appendChild(description);
            divContainer.appendChild(value);
            divContainer.appendChild(category);
            
            li.appendChild(divContainer);
            li.appendChild(interactionDiv);

            container.appendChild(li);
        }
    }

    function updatePagination() {
        const paginationControls = document.getElementById('pagination-controls');
        paginationControls.innerHTML = '';

        if (totalPages > 1) {
            const prevButton = document.createElement('button');
            prevButton.innerHTML = '<ion-icon  name="chevron-back-outline"></ion-icon>';
            prevButton.disabled = currentPage === 1;
        prevButton.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                    loadSearchResults(currentPage);
                }
            });
            paginationControls.appendChild(prevButton);
        }

        for (let i = 1; i <= totalPages; i++) {
            if (
                i === 1 || 
                i === totalPages || 
                (i >= currentPage - 1 && i <= currentPage + 1)
            ) {
                const button = document.createElement('button');
                button.textContent = i;
                button.classList.toggle('active', i === currentPage);
                button.addEventListener('click', () => {
                    currentPage = i;
                    loadSearchResults(currentPage);
                });
                paginationControls.appendChild(button);
            } else if (
                (i === currentPage - 2 && currentPage > 3) ||
                (i === currentPage + 2 && currentPage < totalPages - 2)
            ) {
                const ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                paginationControls.appendChild(ellipsis);
            }
        }

        if (totalPages > 1) {
            const nextButton = document.createElement('button');
            nextButton.innerHTML = '<ion-icon name="chevron-forward-outline"></ion-icon>';
            nextButton.disabled = currentPage === totalPages;
            nextButton.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    loadSearchResults(currentPage);
                }
            });
            paginationControls.appendChild(nextButton);
        }
    }

    function performSearch() {
        const searchInput = document.getElementById('searchInput');
        const categorySelect = document.getElementById('categorySelect');
        
        const newUrl = `./recherche.php?Recherche=${encodeURIComponent(searchInput.value)}&Category=${categorySelect.value}`;
        history.pushState({}, '', newUrl);
        
        currentPage = 1;
        loadSearchResults(currentPage);
    }
    loadCategories();
    loadSearchResults();
    
</script>

<?php require_once "./component/foot.php"; ?>