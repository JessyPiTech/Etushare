<?php 
require_once "./securiter/session.php";
require_once "./component/head.php";
?>

<style>

main{
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
</style>
<style>
miniHeader button {
    background-color: #f0f0f0;
    transition: background-color 0.3s ease;
}

miniHeader button.active {
    background-color: #007bff;
    color: white;
}
</style>

<main>
    <div id="like-annonces" class="groupe_annonce flex-complete">
    </div>

    <div id="participant-annonces" class="groupe_annonce flex-complete">
    </div>
    <div id="mesannonces-annonces" class="groupe_annonce flex-complete">
    </div>

    <!-- Pagination Controls -->
    <div id="pagination-controls" class="pagination">
        tzdegcsuydgycb
    </div>
</main>

<script src="./static/js/postData.js"></script>
<script src="./static/js/likeParticipation.js"></script>

<script>
    const user_id = '<?php echo $_SESSION['user_id'] ?>';
    let currentView = 'Like';
    const header = document.querySelector('header');
    const miniHeader = document.createElement('sous-header');

    miniHeader.innerHTML = `
        <button onclick="changePage(${user_id},'Like')" id="like" class="active"><h2>Like</h2></button>
        <button onclick="changePage(${user_id},'Participant')" id="participent"><h2>Participant</h2></button>
        <button onclick="changePage(${user_id},'MesAnnonces')" id="mesannonces"><h2>Mes annonces</h2></button>
    `;
    header.appendChild(miniHeader);

    const defaultImage = './upload/default.png';
    const limit = 2;
    let currentPage = 1;
    let totalPages = 1;
    let currentType = 'Like';

    async function loadMore(user_id,type, containerId, page = 1) {
        const offset = (page - 1) * limit;
        const container = document.getElementById(containerId);

        if (!container) {
            console.error(`Element with ID ${containerId} not found.`);
            return;
        }

        container.innerHTML = '<div class="loading">Chargement...</div>';

        try {
            const requestData = {
                action: `Dashboard_More_Annonce_${type}`,
                part: type,
                category_id: 2, 
                offset: offset,
                user_id: user_id,
                limit: limit
            };



            const data = await postData(apiUrl, requestData);
            if (data.error) throw new Error(data.error);

            let items;
            document.getElementById('like-annonces').style.display = 'none';
            document.getElementById('participant-annonces').style.display = 'none';
            document.getElementById('mesannonces-annonces').style.display = 'none';

            container.style.display = 'flex';

            if (
                !data || 
                (!Array.isArray(data.likeAnnonces) &&
                !Array.isArray(data.participantAnnonces) &&
                !Array.isArray(data.mesAnnonces))
            ) {
                throw new Error('Invalid or incomplete response from API');
            }

            switch (type) {
                case 'Like':
                    items = data.likeAnnonces || [];
                    totalPages = Math.ceil((data.totalCount || 0) / limit) || 1;
                    break;
                case 'Participant':
                    items = data.participantAnnonces || [];
                    totalPages = Math.ceil((data.totalCount || 0) / limit) || 1;
                    break;
                case 'MesAnnonces':
                    items = data.mesAnnonces || [];
                    totalPages = Math.ceil((data.totalCount || 0) / limit) || 1;
                    break;
                default:
                    items = [];
            }

            displayData(user_id,items, containerId, type, true);
            updatePagination();

            if (items.length === 0) {
                container.innerHTML = `<div class="no-data">Aucune annonce de type "${type}" disponible.</div>`;
            }
        } catch (error) {
            console.error('Failed to load data:', error);
        }
    }

    async function displayData(user_id,items, containerId, type, clear = false) { 
        try {
            const container = document.getElementById(containerId);
            if (!container) {
                console.error(`Container with ID '${containerId}' not found.`);
                return;
            }

            if (clear) container.innerHTML = '';

            if (items && items.length > 0) {
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
            } else if (clear) {
                container.innerHTML = '<li>No data available</li>';
            }
        } catch (error) {
            console.error("Error in displayData:", error);
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
                    loadMore(user_id, currentType, `${currentType.toLowerCase()}-annonces`, currentPage);
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
                    loadMore(user_id, currentType, `${currentType.toLowerCase()}-annonces`, currentPage);
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
                    loadMore(user_id,currentType, `${currentType.toLowerCase()}-annonces`, currentPage);
                }
            });
            paginationControls.appendChild(nextButton);
        }
    }

    function changePage(user_id, type) {
        currentType = type;
        currentPage = 1;

        document.getElementById('like').classList.toggle('active', type === 'Like');
        document.getElementById('participent').classList.toggle('active', type === 'Participant');
        document.getElementById('mesannonces').classList.toggle('active', type === 'MesAnnonces');

        loadMore(user_id, type, `${type.toLowerCase()}-annonces`, currentPage);
    }

    loadMore(user_id, 'Like', 'like-annonces');
</script>

<?php require_once "./component/foot.php"; ?>