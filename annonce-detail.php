<?php 
require_once "./securiter/session.php";
require_once "./component/head.php";

$annonce_id = $_GET['annonce_id'] ?? null;
if (!$annonce_id) die("Invalid announcement");
?>
<style>
.annonce-item-container {
    display: flex;
    gap: 16px;
    padding: 0;
    flex-wrap: wrap;

}

.annonce-image {
    max-width: 500px;
    max-height: 500px;
    object-fit: cover;
    border-radius: 5px;
}

.annonce-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.annonce-title {
    font-size: 1.5rem;
    font-weight: bold;
    margin: 8px 0;
}

.annonce-description, .annonce-value, .category-name {
    font-size: 1rem;
    color: #555;
}

.user-info-container {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 16px 0;
}

.user-image {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid #ccc;
}

.user-name {
    font-weight: bold;
    font-size: 1rem;
    color: #333;
}

.interaction-container {
    display: flex;
    justify-content: space-between;
    margin-top: 16px;
    gap: 16px;
}

.interaction-item {
    display: flex;
    align-items: center;
    gap: 8px;
}




.error {
    color: red;
    font-weight: bold;
}

.participant-user{
    display: flex
;
    flex-direction: column;
    align-items: flex-start;
}



.annonce-big{
    margin: 2em;
    padding: 2em;
    border-radius: 15px;
    box-shadow: 0px 7px 10px 0px var(--ombre);
}
</style>

<main>
    <div id="annonce-details" class="annonce-big">
        <div id="annonce-main-content" class="annonce-item-container">
            <img src="" alt="" class="annonce-image" id="annonce-image">
            <div class="annonce-info">
                <h4 class="annonce-title" id="annonce-title"></h4>
                <p class="annonce-description" id="annonce-description"></p>
                <p class="annonce-value" id="annonce-value"></p>
                <p class="category-name" id="annonce-category"></p>
            </div>
        </div>
        <a id="annonce-user-info" class="user-info-container">
            <img src="" alt="" class="user-image" id="user-image">
            <span class="user-name" id="user-name"></span>
        </a>
        
        <div id="validated-participants">
            <h3>Validated Participants</h3>
            <div id="participants-list"></div>
        </div>
    </div>
</main>



<script src="./static/js/postData.js"></script>
<script src="./static/js/likeParticipation.js"></script>
<script>
const defaultImage = './upload/default.png';
const user_id = '<?php echo $_SESSION['user_id'] ?>';
async function loadAnnonceDetails() {
    try {
        const requestData = { 
            action: "Annonce_Details", 
            annonce_id: <?php echo $annonce_id; ?>,
            user_id: <?php echo $_SESSION['user_id']; ?>
        };
        const data = await postData(apiUrl, requestData);

        const { annonce, likeCount, participantCount } = data;
        document.getElementById('annonce-image').src = annonce.image_path || defaultImage;
        document.getElementById('annonce-image').alt = annonce.annonce_title;
        document.getElementById('annonce-title').textContent = annonce.annonce_title;
        document.getElementById('annonce-description').textContent = annonce.annonce_description;
        document.getElementById('annonce-value').textContent = `Value: ${annonce.annonce_value} point`;
        document.getElementById('annonce-category').textContent = `Category: ${annonce.category_name}`;
        document.getElementById('annonce-user-info').href = `user-detail.php?user_id=${annonce.user_id}`;
        document.getElementById('user-image').src = annonce.user_image_profil || defaultImage;
        document.getElementById('user-image').alt = annonce.user_name;
        document.getElementById('user-name').textContent = annonce.user_name;
        const div = document.getElementById('annonce-details');
        if (user_id == annonce.user_id){
            loadValidatedParticipants()
        }
        const interactionDiv = await affichageInteraction(annonce, user_id);
        div.append(interactionDiv);

    } catch (error) {
        console.error('Failed to load announcement details:', error);
        document.getElementById('annonce-main-content').innerHTML = '<div class="error">Failed to load the details. Please try again later.</div>';
    }
}
async function loadValidatedParticipants() {
    try {
        const requestData = { 
            action: "Transfer_Participants", 
            annonce_id: <?php echo $annonce_id; ?>,
            user_id: <?php echo $_SESSION['user_id']; ?>
        };
        const { participants, isCreator } = await postData(apiUrl, requestData);

        const participantsList = document.getElementById('participants-list');

        const generateUserInfo = (user) => `
            <div class="user-info-container">
                <img src="${user.user_image_profil}" alt="${user.user_name}" class="user-image">
                <span>${user.user_name}</span>
                <span>Amount: ${user.transfer_amount}</span>
            </div>
        `;

        const generateActionButtons = (transferId) => `
            <div class="interaction-container">
                <button onclick="validateTransfer(${transferId})">Validate Work</button>
                <button onclick="rejectTransfer(${transferId})">Reject Work</button>
            </div>
        `;

        participantsList.innerHTML = participants.map(p => {
            const userInfo = generateUserInfo(p);
            const actionButtons = isCreator ? generateActionButtons(p.transfer_id) : '';
            let statusContent = '';

            if (p.transfer_status === 'approved') {
                statusContent = '<div>Travail confirmé</div>';
            } else if (p.transfer_status === 'pending') {
                statusContent = actionButtons;
            }

            return `
                <div class="participant-user">
                    ${userInfo}
                    ${statusContent}
                </div>
            `;
        }).join('');
        window.validateTransfer = async (transferId) => {
            try {
                const response = await postData(apiUrl, {
                    action: "Transfer_Validate",
                    transfer_id: transferId
                });
                if (response.success) {
                    loadValidatedParticipants();
                }
            } catch (error) {
                console.error('Failed to validate transfer:', error);
            }
        };

        window.rejectTransfer = async (transferId) => {
            try {
                const response = await postData(apiUrl, {
                    action: "Transfer_Reject",
                    transfer_id: transferId
                });
                if (response.success) {
                    loadValidatedParticipants();
                }
            } catch (error) {
                console.error('Failed to reject transfer:', error);
            }
        };
    } catch (error) {
        console.error('Failed to load participants:', error);
    }
}

window.onload = () => {
    loadAnnonceDetails();
};
</script>

<?php require_once "./component/foot.php"; ?>
