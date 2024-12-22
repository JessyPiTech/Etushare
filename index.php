<?php 
require_once "./securiter/session.php";
require_once "./component/head.php";
?>

<style>
    
</style>
<main>
    <div class="dashboard-content">
        <h2>Dashboard</h2>
        <p>This is your personal dashboard where you can manage your account and view your information.</p>
        
        <div id="notif">

        </div>
        <!-- go faire carrouselle -->
        <div id="data-container">
            <div>
                <h3>All Annonces</h3>
                <div  class="carrousel flex-align-center" >
                    <button onclick="gauche('all-annonces')"  class="carousel-button carousel-button-left"><ion-icon  name="chevron-back-outline"></ion-icon></button>
                    <div id="all-annonces" class="groupe_annonce flex-align-center">
                        <!-- faut change ici -->
                    </div>
                    <button onclick="droit('all-annonces')" class="carousel-button carousel-button-right"><ion-icon  name="chevron-forward-outline"></ion-icon></button>
                </div>
                
            </div>
            
            <div>
                <h3>Category Annonces</h3>
                <div  class="carrousel flex-align-center" >
                    <button onclick="gauche('category-annonces')"  class="carousel-button carousel-button-left"><ion-icon  name="chevron-back-outline"></ion-icon></button>
                    <div id="category-annonces" class="groupe_annonce flex-align-center">
                        
                        <!-- faut change ici -->
                    </div>
                    <button onclick="droit('category-annonces')" class="carousel-button carousel-button-right"><ion-icon  name="chevron-forward-outline"></ion-icon></button>
                </div>
                <button onclick="loadMore('Category', 'category-annonces')">Load More Category Annonces</button>
            </div>
            
            <div>
                <h3>Like Annonces</h3>
                <div  class="carrousel flex-align-center" >
                    <button onclick="gauche('like-annonces')"  class="carousel-button carousel-button-left"><ion-icon  name="chevron-back-outline"></ion-icon></button>
                    <div id="like-annonces" class="groupe_annonce flex-align-center">
                        <!-- faut change ici -->
                    </div>
                    <button onclick="droit('like-annonces')" class="carousel-button carousel-button-right"><ion-icon  name="chevron-forward-outline"></ion-icon></button>
                </div>
                <button onclick="loadMore('Like', 'like-annonces')">Load More Like Annonces</button>
            </div>
            <div>
                <h3>Participant Annonces</h3>
                <div  class="carrousel flex-align-center" >
                    <button onclick="gauche('participant-annonces')"  class="carousel-button carousel-button-left"><ion-icon  name="chevron-back-outline"></ion-icon></button>
                    <div  id="participant-annonces" class="groupe_annonce flex-align-center">
                        <!-- faut change ici -->
                    </div>
                    <button onclick="droit('participant-annonces')" class="carousel-button carousel-button-right"><ion-icon  name="chevron-forward-outline"></ion-icon></button>
                </div>
                <button onclick="loadMore('Participant', 'participant-annonces')">Load More Participant Annonces</button>
            </div>
        </div>
    </div>
</main>
<script src="./static/js/postData.js"></script>
<script src="./static/js/likeParticipation.js"></script>
<script src="./static/js/carrouselMove.js"></script>

<script>
    const user_id = '<?php echo $_SESSION['user_id'] ?>';
    const defaultImage = './upload/default.png';
    let offsets = {
        "all-annonces": 0,
        "category-annonces": 0,
        "like-annonces": 0, 
        "participant-annonces": 0
    };
    const limit = 5; // nombre d'annonce à charger

    async function loadData(user_id) {
        try {
            const requestData = { 
                action: "Dashboard_Load", 
                category_id: 2,  
                user_id: user_id,
            };
            const data = await postData(apiUrl, requestData);

            if (data.error) throw new Error(data.error);

            displayData(user_id, data.allAnnonces, 'all-annonces', 'Annonce');
            displayData(user_id, data.categoryAnnonces, 'category-annonces', 'Category');
            displayData(user_id, data.likeAnnonces, 'like-annonces', 'Like');
            displayData(user_id, data.participantAnnonces, 'participant-annonces', 'Participant');

            if (data.notifications) {
                displayNotifications(data.notifications, user_id);
            }
        } catch (error) { 
            console.error('Failed to load data:', error);
        }
    }

    function displayNotifications(notifications, user_id) {
        const notifContainer = document.getElementById('notif');
        notifContainer.innerHTML = '';

        if (notifications.length === 0) {
            notifContainer.innerHTML = '<p>No new notifications</p>';
            return;
        }

        const notifList = document.createElement('ul');
        notifList.classList.add('notification-list');

        notifications.forEach(notif => {
            const notifItem = document.createElement('li');
            notifItem.classList.add('notification-item');

            let notifText = '';
            switch (notif.type) {
                case 'friend_request':
                    notifText = `New friend request from ${notif.sender_name}`;
                    break;
                case 'like':
                    notifText = `${notif.sender_name} liked your announcement`;
                    break;
                case 'Participant_Respond':
                    notifText = `${notif.participant_name} wants to participate in your announcement`;
                    break;
                case 'transfer':
                    notifText = `New transfer notification from ${notif.sender_name}`;
                    break;
                default:
                    notifText = 'New notification';
            }

            notifItem.innerHTML = `
                <span>${notifText}</span>
                <div class="notification-actions">
                    <button onclick="handleNotificationAction(${notif.id}, '${notif.type}', 'accept', ${user_id}, ${notif.annonce_id || null}, ${notif.participant_id || null})">Accept</button>
                    <button onclick="handleNotificationAction(${notif.id}, '${notif.type}', 'reject', ${user_id}, ${notif.annonce_id || null}, ${notif.participant_id || null})">Reject</button>
                </div>
            `;

            notifList.appendChild(notifItem);
        });

        notifContainer.appendChild(notifList);
    }

    function handleNotificationAction(id, type, action, user_id, annonce_id = null, participant_id = null) {
        const requestData = {
            action: "Notification",
            notification_id: id,
            notification_type: type,
            notification_action: action,
            user_id: user_id
        };

        if (annonce_id) {
            requestData.annonce_id = annonce_id;
        }
        if (participant_id) {
            requestData.participant_id = participant_id;
        }

        postData(apiUrl, requestData)
            .then(response => {
                if (response.success) {
                    reloadNotifications(user_id);
                    alert(`${type} ${action}ed successfully`);
                } else {
                    console.error('Failed to process notification', response);
                }
            })
            .catch(error => {
                console.error('Error processing notification', error);
            });
    }

    function reloadNotifications(user_id) {
        const requestData = {
            action: "Dashboard_Load",
            category_id: 2,
            user_id: user_id
        };

        postData(apiUrl, requestData)
            .then(data => {
                if (data.notifications) {
                    displayNotifications(data.notifications, user_id);
                }
            })
            .catch(error => {
                console.error('Failed to reload notifications:', error);
            });
    }

    

    async function displayData(user_id, items, containerId, type, clear = false) {
        const container = document.getElementById(containerId);
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
                image.classList.add('annonce-image'); 

                const interactionDiv = await affichageInteraction(item, user_id);

                const divContainer = document.createElement('div');
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

            const autre = document.createElement('li');
            autre.classList.add('annonce-item');
            autre.style.backgroundColor = 'aqua';
            autre.style.height = '100%';
            autre.style.alignItems = 'flex';
            autre.style.justifyContent = 'center';
            autre.style.display = 'flex';
            autre.style.width = '15em'
            
            const contentBouton = document.createElement('button');
            contentBouton.setAttribute('onclick', `loadMore('${type}', '${containerId}')`);

            const icon = document.createElement('ion-icon');
            icon.setAttribute('name', 'add-circle-outline');
            contentBouton.appendChild(icon);

            autre.appendChild(contentBouton);
            container.appendChild(autre);
        } else if (clear) {
            container.innerHTML = '<li>No data available</li>';
        }
    }

   

    window.onload = loadData(user_id);

</script>

<?php require_once "./component/foot.php"; ?>