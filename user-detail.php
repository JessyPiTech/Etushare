<?php 
require_once "./securiter/session.php";
require_once "./component/head.php";

$user_id = $_GET['user_id'] ?? null;
if (!$user_id) die("Invalid user ID");
?>
<style>

.user-profil{
    display: flex;
    flex-direction: column;
    align-items: center;
}
#user-annonces{
    display: flex;
    flex-wrap: wrap;
}
</style>

<main>
    <div id="user-profile" class="user-profil">
        <div id="user-info"></div>
        <div id="user-actions" class="actions-container"></div>
        <div id="user-annonces" class="annonces-container"></div>
    </div>
</main>

<script src="./static/js/postData.js"></script>
<script src="./static/js/likeParticipation.js"></script>
<script>
    const user_id = '<?php echo $_SESSION['user_id'] ?>';
    const defaultImage = './upload/default.png';
    async function loadUserDetails(user_id) {
        try {
            const requestData = { 
                action: "User_Detail", 
                user_id: <?php echo $user_id; ?>,
                current_user_id: <?php echo $_SESSION['user_id']; ?>
            };

            const data = await postData(apiUrl, requestData);

            if (!data || !data.user) {
                throw new Error("User data not found");
            }

            const { user, annonces, friendStatus } = data;
            document.getElementById('user-info').innerHTML = ` 
                <div class="user-profile-header">
                    <img src="${user.user_image_profil}" alt="${user.user_name}" class="user-image">
                    <h2>${user.user_name}</h2>
                </div>
                <p><strong>Email:</strong> ${user.user_mail}</p>
                <p><strong>Description:</strong> ${user.user_description_profil || 'No description provided'}</p>
            `;

            const actionsDiv = document.getElementById('user-actions');
            actionsDiv.innerHTML = '';
            if (friendStatus === 'not_friends' && '<?php echo $user_id; ?>' != user_id) {
                const addFriendBtn = document.createElement('button');
                addFriendBtn.textContent = 'Add Friend';
                addFriendBtn.classList.add('btn', 'special');
                addFriendBtn.onclick = () => sendFriendRequest(user_id, <?php echo $user_id; ?>);
                actionsDiv.appendChild(addFriendBtn);
            } else if (friendStatus === 'friends') {
                const messageBtn = document.createElement('button');
                messageBtn.textContent = 'Message';
                messageBtn.classList.add('btn', 'special');
                messageBtn.onclick = () => openConversation(<?php echo $user_id; ?>);
                actionsDiv.appendChild(messageBtn);
            }

            displayData(user_id, annonces, 'user-annonces', 'annonce', true);
            
        } catch (error) {
            console.error('Failed to load user details:', error);
            document.getElementById('user-info').innerHTML = `<p class="error">Failed to load user details. Please try again later.</p>`;
        }
    }

    async function displayData(user_id, items, containerId, type, clear = false) { 
        try {
            const container = document.getElementById(containerId);
            if (!container) {
                console.error(`Container with ID '${containerId}' not found.`);
                return;
            }

            if (clear) container.innerHTML = '';

            if (items && items.length > 0) {
                for (const item of items) {
                    const div = document.createElement('div');
                    div.classList.add('annonce-item');

                    const title = document.createElement('h3');
                    title.textContent = item.annonce_title || 'No title';

                    const description = document.createElement('p');
                    description.textContent = item.annonce_description || 'No description available';

                    const value = document.createElement('p');
                    value.textContent = `Value: ${item.annonce_value || 'N/A'} €`;

                    const image = document.createElement('img');
                    image.src = item.image_lien || defaultImage;
                    image.alt = item.annonce_title || 'Image not available';
                    image.classList.add('annonce-image');

                    const interactionDiv = await affichageInteraction(item, user_id);

                    const divContainer = document.createElement('div');
                    divContainer.addEventListener('click', () => {
                        window.location.href = `annonce-detail.php?annonce_id=${item.annonce_id}`;
                    });
                    divContainer.style.cursor = 'pointer';

                    div.appendChild(title);
                    div.appendChild(description);
                    div.appendChild(value);
                    div.appendChild(image);
                    div.appendChild(interactionDiv);

                    container.appendChild(div);
                }
            } else if (clear) {
                container.innerHTML = '<p>No data available</p>';
            }
        } catch (error) {
            console.error("Error in displayData:", error);
        }
    }




    function sendFriendRequest(user_id, targetUserId) {
        const requestData = {
            action: "Notification_Handle",
            notification_type: 'friend_request',
            sender_id: user_id,
            receiver_id: targetUserId
        };

        postData(apiUrl, requestData)
            .then(response => {
                if (response.success) {
                    alert('Friend request sent!');
                    loadUserDetails(user_id);
                } else {
                    alert('Failed to send friend request.');
                }
            })
            .catch(error => {
                console.error('Error sending friend request:', error);
            });
    }

    function openConversation(userId) {
        window.location.href = `conversation.php?user_id=${userId}`;
    }

    window.onload = loadUserDetails(user_id);
</script>

<?php require_once "./component/foot.php"; ?>