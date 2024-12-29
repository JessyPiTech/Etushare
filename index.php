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
                
            </div>
        </div>
    </div>
</main>
<script src="./static/js/postData.js"></script>
<script src="./static/js/likeParticipation.js"></script>
<script src="./static/js/carrouselMove.js"></script>
<script src="./static/js/displayAnnonces.js"></script>

<script>
    
    
    const category_id = <?php 
    if (!isset($_SESSION['last_category_id']) || empty($_SESSION['last_category_id'])) {echo 0; } else {echo $_SESSION['last_category_id'];}?>;
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
                category_id: category_id,  
                user_id: user_id,
            };
            const data = await postData(apiUrl, requestData);

            if (data.error) throw new Error(data.error);

            await displayAnnonces(user_id, data.allAnnonces, 'all-annonces', {
                displayType: 'carousel',
                showRedirectionButton: true,
                redirectionUrl: './recherche.php?Recherche=all'
            });
            
            await displayAnnonces(user_id, data.categoryAnnonces, 'category-annonces', {
                displayType: 'carousel',
                showRedirectionButton: true, 
                redirectionUrl: `./recherche.php?Recherche=0&Category=${category_id}`
            });

            await displayAnnonces(user_id, data.likeAnnonces, 'like-annonces', {
                displayType: 'carousel',
                showRedirectionButton: true,
                redirectionUrl: './favoris.php'
            });

            await displayAnnonces(user_id, data.participantAnnonces, 'participant-annonces', {
                displayType: 'carousel',
                showRedirectionButton: true,
                redirectionUrl: './favoris.php'
            });

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
            let actions = '';

            switch (notif.type) {
                case 'Like':
                    notifText = `${notif.sender_name} liked your announcement: "${notif.annonce_title}"`;
                    actions = `<button onclick="markAsViewed(${notif.id}, 'Validation', '${notif.type}', ${user_id})">OK</button>`;
                    break;
                case 'Avis':
                    notifText = `${notif.sender_name} left a review: "${notif.avis_text}" with a rating of ${notif.avis_note}/5`;
                    actions = `<button onclick="markAsViewed(${notif.id}, 'Validation', '${notif.type}', ${user_id})">OK</button>`;
                    break;
                case 'Message':
                    notifText = `${notif.sender_name} sent you a message: "${notif.message_text}"`;
                    actions = `<button onclick="markAsViewed(${notif.id}, 'Validation', '${notif.type}', ${user_id})">OK</button>`;
                    break;
                case 'Friend_Request':
                    notifText = `New friend request from ${notif.sender_name}`;
                    actions = `
                        <button onclick="handleNotificationAction(${notif.id}, 'Friend_Respond',  null, 'accept', ${user_id})">Accept</button>
                        <button onclick="handleNotificationAction(${notif.id}, 'Friend_Respond',  null, 'reject', ${user_id})">Reject</button>
                    `;
                    break;
                case 'Friend_Respond':
                    notifText = `${notif.sender_name} responded to your friend request: ` +
                                `${notif.status === 'accept' ? 'accept' : 'reject'}`;
                    actions = `
                        <button onclick="markAsViewed(${notif.id}, 'Validation', '${notif.type}',${user_id})">OK</button>
                    `;
                    break;

                case 'Participant_Respond':
                    notifText = `${notif.participant_name} wants to participate in your announcement: "${notif.annonce_title}" ` +
                                `and the request was ${notif.status === 'accept' ? 'accept' : 'reject'}`;
                    actions = `
                        <button onclick="markAsViewed(${notif.id}, 'Validation', '${notif.type}', ${user_id})">OK</button>
                    `;
                    break;
                case 'Participant_Request':
                    console.log(notif.participant_id);
                    notifText = `${notif.participant_name} wants to participate in your announcement: "${notif.annonce_title}"`;
                    actions = `
                        <button onclick="handleNotificationAction(${notif.id}, 'Participant_Respond', null, 'accept', ${user_id}, ${notif.annonce_id}, ${notif.participant_id})">Accept</button>
                        <button onclick="handleNotificationAction(${notif.id}, 'Participant_Respond', null, 'reject', ${user_id}, ${notif.annonce_id}, ${notif.participant_id})">Reject</button>
                    `;
                    break;
                default:
                    notifText = 'New notification';
                    actions = `<button onclick="markAsViewed(${notif.id}, '${notif.type}', '${notif.type}', ${user_id})">OK</button>`;
            }

            notifItem.innerHTML = `
                <span>${notifText}</span>
                <div class="notification-actions">
                    ${actions}
                </div>
            `;
            notifList.appendChild(notifItem);
        });

        notifContainer.appendChild(notifList);
    }

    function markAsViewed(notification_id, type, type2 = null , user_id) {
        const requestData = {
            action: "Notification",
            notification_id: notification_id,
            notification_type: type,
            user_id: user_id
        };
        if (type2) requestData.notification_type_2 = type2;

        postData(apiUrl, requestData)
            .then(response => {
                if (response.success) {
                    reloadNotifications(user_id);
                    alert('Notification marked as viewed');
                } else {
                    console.error('Failed to mark notification as viewed', response);
                }
            })
            .catch(error => {
                console.error('Error marking notification as viewed', error);
            });
    }

    function handleNotificationAction(id, type, type2 = null ,action, user_id, annonce_id = null, participant_id = null) {
        console.log(participant_id);
        const requestData = {
            action: "Notification",
            notification_id: id,
            notification_type: type,
            notification_action: action,
            user_id: user_id,
        };
        
        if (annonce_id) requestData.annonce_id = annonce_id;
        if (type2) requestData.notification_type_2 = type2;
        if (participant_id) requestData.participant_id = participant_id;

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



    async function displayData(user_id, items, containerId, type, clear = false, redirection) {
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
            autre.style.width = '15em';
            autre.style.cursor = 'pointer';
            
            autre.addEventListener('click', () => {
                window.location.href = redirection;
            });

            const icon = document.createElement('ion-icon');
            icon.setAttribute('name', 'add-circle-outline');
            autre.appendChild(icon);

            
            container.appendChild(autre);
        } else if (clear) {
            container.innerHTML = '<li>No data available</li>';
        }
    }

   

    window.onload = loadData(user_id);

</script>

<?php require_once "./component/foot.php"; ?>