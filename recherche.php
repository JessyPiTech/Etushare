<?php 
require_once "./securiter/session.php";
require_once "./component/head.php";
/*en cours*/ 
?>




<main>

</main>
<script src="./static/js/postData.js"></script>
<script>
const urlParams = new URLSearchParams(window.location.search);
const rechercheName = urlParams.get('Recherche');
const pageNum = urlParams.get('Page') || '1';
const defaultImage = './upload/default.png';
let offsets = {
    "all-annonces": 0,
    "category-annonces": 0,
    "like-annonces": 0, 
    "participant-annonces": 0
};

const limit = 20; // nombre d'annonce à charger


async function loadData() {
    offsets[containerId] += limit;

    try {
        const requestData = {
            action: `Dashboard_More_Annonce_${type}`,
            part: type,
            category_id: 2, 
            offset: offsets[containerId],
            user_id: <?php /*echo $_SESSION['user_id'];*/?>,
            limit: limit
        };
        const data = await postData(apiUrl, requestData);

        if (data.error) throw new Error(data.error);
        console.log(data);
        
            displayData(data.allAnnonces , containerId); 
    
    } catch (error) {
        console.error('Failed to load more data:', error);
    }
}
function displayData(items, containerId, type, clear = false) {
    const container = document.getElementById(containerId);
    if (clear) container.innerHTML = ''; 
    if (items && items.length > 0) {
        // Afficher toutes les annonces
        items.forEach(item => {
            const li = document.createElement('li');
            li.classList.add('annonce-item');
            
            const title = document.createElement('h4');
            title.textContent = item.annonce_id || 'No title';
            const description = document.createElement('p');
            description.textContent = item.annonce_description || 'No description available';
            const value = document.createElement('p');
            value.textContent = `Value: ${item.annonce_value !== undefined ? item.annonce_value : 'N/A'} €`;
            const image = document.createElement('img');
            image.src = item.image_lien || defaultImage; 
            image.alt = item.image_name || 'Image not available';
            image.classList.add('annonce-image'); 
            li.appendChild(image);
            li.appendChild(title);
            li.appendChild(description);
            li.appendChild(value);
            container.appendChild(li);
        });
       
        // Ajouter un élément HTML à la fin
        const autre = document.createElement('li');
        autre.classList.add('annonce-item');
        autre.style.backgroundColor = 'aqua';  // Vous pouvez modifier ce style à votre guise
        const contentBouton = document.createElement('button');
        contentBouton.setAttribute('onclick', `loadMore('${type}', '${containerId}')`);
        const icon = document.createElement('ion-icon');
        icon.setAttribute('name', 'add-circle-outline'); // L'icône
        contentBouton.appendChild(icon);
        
        autre.appendChild(contentBouton);
        container.appendChild(autre);  // Ajouter à la fin du conteneur
    } else if (clear) {
        container.innerHTML = '<li>No data available</li>';
    }
} 
window.onload = loadData();

</script>
<?php require_once "./component/foot.php"; ?>