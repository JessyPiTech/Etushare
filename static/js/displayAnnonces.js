async function displayAnnonces(user_id, items, containerId, options = {}) {
    const {
        displayType = 'grid', // 'grid', 'carousel', or 'list'
        imageSize = 'normal', // 'normal', 'big'
        clear = true,
        showRedirectionButton = false,
        redirectionUrl = '',
        limit = null
    } = options;

    try {
        const container = document.getElementById(containerId);
        if (!container) {
            console.error(`Container with ID '${containerId}' not found.`);
            return;
        }

        if (clear) container.innerHTML = '';
        if (!items || items.length === 0) {
            container.innerHTML = '<div class="no-data">Aucune annonce disponible.</div>';
            return;
        }

        const itemsToDisplay = limit ? items.slice(0, limit) : items;

        for (const item of itemsToDisplay) {
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
            image.src = item.image_path || './upload/default.png';
            image.alt = item.image_name || 'Image not available';
            image.classList.add(imageSize === 'big' ? 'big-annonce-image' : 'annonce-image');

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

        if (showRedirectionButton) {
            const redirectionButton = document.createElement('li');
            redirectionButton.classList.add('annonce-item');
            redirectionButton.style.cssText = `
                background-color: aqua;
                height: 100%;
                align-items: center;
                justify-content: center;
                display: flex;
                width: 15em;
                cursor: pointer;
            `;
            
            redirectionButton.addEventListener('click', () => {
                window.location.href = redirectionUrl;
            });

            const icon = document.createElement('ion-icon');
            icon.setAttribute('name', 'add-circle-outline');
            redirectionButton.appendChild(icon);
            
            container.appendChild(redirectionButton);
        }

        if (displayType === 'carousel') {
            container.style.display = 'flex';
            
        }
    } catch (error) {
        console.error("Error in displayAnnonces:", error);
        container.innerHTML = '<div class="error">Une erreur est survenue lors de l\'affichage des annonces.</div>';
    }
}