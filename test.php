<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

function renderForm($id, $title, $fields, $submitText) {
    echo "<h2>$title</h2>";
    echo "<form id=\"$id\" class=\"form-container\">";
    foreach ($fields as $field) {
        echo "<div class=\"form-group\">";
        echo "<label for=\"{$field['id']}\">{$field['label']}</label>";
        echo "<input type=\"{$field['type']}\" id=\"{$field['id']}\" placeholder=\"{$field['placeholder']}\" required>";
        echo "</div>";
    }
    echo "<button class='button_spe' type=\"submit\">$submitText</button>";
    echo "<p id=\"{$id}Error\" class=\"error\"></p>";
    echo "</form>";
}
?>
<?php require_once "./securiter/session.php";?>
<?php require_once "./component/head.php";?>
    <main>
        <div class="corp">

            
            <?php
            // User Forms
            renderForm("createUserForm", "Create User", [
                ['id' => 'createUsername', 'label' => 'Username', 'type' => 'text', 'placeholder' => 'Enter username'],
                ['id' => 'createEmail', 'label' => 'Email', 'type' => 'email', 'placeholder' => 'Enter email'],
                ['id' => 'createUserPassword', 'label' => 'Password', 'type' => 'password', 'placeholder' => 'Enter password'],
                
                ['id' => 'createUserImage', 'label' => 'Profile Image URL', 'type' => 'text', 'placeholder' => 'Enter image URL'],
                ['id' => 'createUserDescription', 'label' => 'Profile Description', 'type' => 'text', 'placeholder' => 'Enter profile description'],
            ], "Create User");

            renderForm("deleteUserForm", "Delete User", [
                ['id' => 'deleteUser Id', 'label' => 'User  ID', 'type' => 'number', 'placeholder' => 'Enter user ID'],
            ], "Delete User");

            renderForm("updateUserForm", "Update User", [
                ['id' => 'updateUser Id', 'label' => 'User  ID', 'type' => 'number', 'placeholder' => 'Enter user ID'],
                ['id' => 'updateUsername', 'label' => 'New Username', 'type' => 'text', 'placeholder' => 'Enter new username'],
                ['id' => 'updateEmail', 'label' => 'New Email', 'type' => 'email', 'placeholder' => 'Enter new email'],
                ['id' => 'updateUser Password', 'label' => 'New Password', 'type' => 'password', 'placeholder' => 'Enter new password'],
                
                ['id' => 'updateUser Image', 'label' => 'New Profile Image URL', 'type' => 'text', 'placeholder' => 'Enter new image URL'],
                ['id' => 'updateUser Description', 'label' => 'New Profile Description', 'type' => 'text', 'placeholder' => 'Enter new profile description'],
            ], "Update User");

            // Announcement
            renderForm("createAnnonceForm", "Create Announcement", [
                ['id' => 'createAnnonceTitle', 'label' => 'Title', 'type' => 'text', 'placeholder' => 'Enter the title'],
                ['id' => 'createAnnonceDescription', 'label' => 'Description', 'type' => 'text', 'placeholder' => 'Enter the description'],
                ['id' => 'createAnnonceValue', 'label' => 'Value', 'type' => 'number', 'placeholder' => 'Enter the value'],
                ['id' => 'createAnnonceUserId', 'label' => 'User ID', 'type' => 'number', 'placeholder' => 'Enter user ID']
            ], "Create Announcement");

            renderForm("deleteAnnonceForm", "Delete Announcement", [
                ['id' => 'deleteAnnonceId', 'label' => 'Announcement ID', 'type' => 'number', 'placeholder' => 'Enter announcement ID']
            ], "Delete Announcement");

            renderForm("updateAnnonceForm", "Update Announcement", [
                ['id' => 'updateAnnonceId', 'label' => 'Announcement ID', 'type' => 'number', 'placeholder' => 'Enter announcement ID'],
                ['id' => 'updateAnnonceTitle', 'label' => 'New Title', 'type' => 'text', 'placeholder' => 'Enter new title'],
                ['id' => 'updateAnnonceDescription', 'label' => 'New Description', 'type' => 'text', 'placeholder' => 'Enter new description'],
                ['id' => 'updateAnnonceValue', 'label' => 'New Value', 'type' => 'number', 'placeholder' => 'Enter new value'],
                ['id' => 'updateAnnonceUserId', 'label' => 'New User ID', 'type' => 'number', 'placeholder' => 'Enter new user ID']
            ], "Update Announcement");

            // Gestion des Commentaires
            renderForm("createCommentForm", "Create Comment", [
                ['id' => 'createCommentAnnonceId', 'label' => 'Announcement ID', 'type' => 'number', 'placeholder' => 'Enter announcement ID'],
                ['id' => 'createCommentUserId', 'label' => 'User ID', 'type' => 'number', 'placeholder' => 'Enter user ID'],
                ['id' => 'createCommentText', 'label' => 'Comment Text', 'type' => 'text', 'placeholder' => 'Enter your comment'],
            ], "Create Comment");

            renderForm("updateCommentForm", "Update Comment", [
                ['id' => 'updateCommentId', 'label' => 'Comment ID', 'type' => 'number', 'placeholder' => 'Enter comment ID'],
                ['id' => 'updateCommentAnnonceId', 'label' => 'New Announcement ID', 'type' => 'number', 'placeholder' => 'Enter new announcement ID'],
                ['id' => 'updateCommentUserId', 'label' => 'New User ID', 'type' => 'number', 'placeholder' => 'Enter new user ID'],
                ['id' => 'updateCommentText', 'label' => 'New Comment Text', 'type' => 'text', 'placeholder' => 'Enter new comment text'],
            ], "Update Comment");

            renderForm("deleteCommentForm", "Delete Comment", [
                ['id' => 'deleteCommentId', 'label' => 'Comment ID', 'type' => 'number', 'placeholder' => 'Enter comment ID'],
            ], "Delete Comment");

            // Gestion des Amis utilisateurs
            renderForm("createUserFriendForm", "Create User Friend", [
                ['id' => 'createUserFriendId1', 'label' => 'User ID 1', 'type' => 'number', 'placeholder' => 'Enter first user ID'],
                ['id' => 'createUserFriendId2', 'label' => 'User ID 2', 'type' => 'number', 'placeholder' => 'Enter second user ID'],
                ['id' => 'createUserFriendIcon', 'label' => 'Friend Icon', 'type' => 'text', 'placeholder' => 'Enter friend icon URL'],
            ], "Create User Friend");

            renderForm("updateUserFriendForm", "Update User Friend", [
                ['id' => 'updateUserFriendId', 'label' => 'User Friend ID', 'type' => 'number', 'placeholder' => 'Enter user friend ID'],
                ['id' => 'updateUserFriendId1', 'label' => 'New User ID 1', 'type' => 'number', 'placeholder' => 'Enter new first user ID'],
                ['id' => 'updateUserFriendId2', 'label' => 'New User ID 2', 'type' => 'number', 'placeholder' => 'Enter new second user ID'],
                ['id' => 'updateUserFriendIcon', 'label' => 'New Friend Icon', 'type' => 'text', 'placeholder' => 'Enter new friend icon URL'],
            ], "Update User Friend");

            renderForm("deleteUserFriendForm", "Delete User Friend", [
                ['id' => 'deleteUserFriendId', 'label' => 'User Friend ID', 'type' => 'number', 'placeholder' => 'Enter user friend ID'],
            ], "Delete User Friend");

            // Gestion des Catégories
            renderForm("createCategoryForm", "Create Category", [
                ['id' => 'createCategoryName', 'label' => 'Category Name', 'type' => 'text', 'placeholder' => 'Enter category name'],
                ['id' => 'createCategoryIcon', 'label' => 'Category Icon', 'type' => 'number', 'placeholder' => 'Enter category icon'],
            ], "Create Category");

            renderForm("updateCategoryForm", "Update Category", [
                ['id' => 'updateCategoryId', 'label' => 'Category ID', 'type' => 'number', 'placeholder' => 'Enter category ID'],
                ['id' => 'updateCategoryName', 'label' => 'New Category Name', 'type' => 'text', 'placeholder' => 'Enter new category name'],
                ['id' => 'updateCategoryIcon', 'label' => 'New Category Icon', 'type' => 'number', 'placeholder' => 'Enter new category icon'],
            ], "Update Category");

            renderForm("deleteCategoryForm", "Delete Category", [
                ['id' => 'deleteCategoryId', 'label' => 'Category ID', 'type' => 'number', 'placeholder' => 'Enter category ID'],
            ], "Delete Category");

            // Gestion des Mots-clés de catégories
            renderForm("createCategoryKeyWordForm", "Create Category Key Word", [
                ['id' => 'createCategoryKeyWord', 'label' => 'Key Word', 'type' => 'text', 'placeholder' => 'Enter category key word'],
                ['id' => 'createCategoryKeyWordCategoryId', 'label' => 'Category ID', 'type' => 'number', 'placeholder' => 'Enter category ID'],
            ], "Create Category Key Word");

            renderForm("updateCategoryKeyWordForm", "Update Category Key Word", [
                ['id' => 'updateCategoryKeyWordId', 'label' => 'Category Key Word ID', 'type' => 'number', 'placeholder' => 'Enter category key word ID'],
                ['id' => 'updateCategoryKeyWord', 'label' => 'New Key Word', 'type' => 'text', 'placeholder' => 'Enter new key word'],
                ['id' => 'updateCategoryKeyWordCategoryId', 'label' => 'New Category ID', 'type' => 'number', 'placeholder' => 'Enter new category ID'],
            ], "Update Category Key Word");

            renderForm("deleteCategoryKeyWordForm", "Delete Category Key Word", [
                ['id' => 'deleteCategoryKeyWordId', 'label' => 'Category Key Word ID', 'type' => 'number', 'placeholder' => 'Enter category key word ID'],
            ], "Delete Category Key Word");

            // Gestion des Images d'annonces
            renderForm("createAnnonceImageForm", "Create Announcement Image", [
                ['id' => 'createAnnonceImageAnnonceId', 'label' => 'Announcement ID', 'type' => 'number', 'placeholder' => 'Enter announcement ID'],
                ['id' => 'createAnnonceImageName', 'label' => 'Image Name', 'type' => 'text', 'placeholder' => 'Enter image name'],
                ['id' => 'createAnnonceImageLink', 'label' => 'Image Link', 'type' => 'text', 'placeholder' => 'Enter image link'],
            ], "Create Announcement Image");

            renderForm("updateAnnonceImageForm", "Update Announcement Image", [
                ['id' => 'updateAnnonceImageId', 'label' => 'Image ID', 'type' => 'number', 'placeholder' => 'Enter image ID'],
                ['id' => 'updateAnnonceImageAnnonceId', 'label' => 'New Announcement ID', 'type' => 'number', 'placeholder' => 'Enter new announcement ID'],
                ['id' => 'updateAnnonceImageName', 'label' => 'New Image Name', 'type' => 'text', 'placeholder' => 'Enter new image name'],
                ['id' => 'updateAnnonceImageLink', 'label' => 'New Image Link', 'type' => 'text', 'placeholder' => 'Enter new image link'],
            ], "Update Announcement Image");

            renderForm("deleteAnnonceImageForm", "Delete Announcement Image", [
                ['id' => 'deleteAnnonceImageId', 'label' => 'Image ID', 'type' => 'number', 'placeholder' => 'Enter image ID'],
            ], "Delete Announcement Image");

            // Gestion des Likes d'annonces
            renderForm("createAnnonceLikeForm", "Create Announcement Like", [
                ['id' => 'createAnnonceLikeAnnonceId', 'label' => 'Announcement ID', 'type' => 'number', 'placeholder' => 'Enter announcement ID'],
                ['id' => 'createAnnonceLikeUserId', 'label' => 'User ID', 'type' => 'number', 'placeholder' => 'Enter user ID'],
            ], "Create Announcement Like");

            renderForm("deleteAnnonceLikeForm", "Delete Announcement Like", [
                ['id' => 'deleteAnnonceLikeId', 'label' => 'Announcement Like ID', 'type' => 'number', 'placeholder' => 'Enter announcement like ID'],
            ], "Delete Announcement Like");

            // Gestion des Participants aux annonces
            renderForm("createAnnonceParticipantForm", "Create Announcement Participant", [
                ['id' => 'createAnnonceParticipantAnnonceId', 'label' => 'Announcement ID', 'type' => 'number', 'placeholder' => 'Enter announcement ID'],
                ['id' => 'createAnnonceParticipantUserId', 'label' => 'User ID', 'type' => 'number', 'placeholder' => 'Enter user ID'],
            ], "Create Announcement Participant");

            renderForm("deleteAnnonceParticipantForm", "Delete Announcement Participant", [
                ['id' => 'deleteAnnonceParticipantId', 'label' => 'Participant Link ID', 'type' => 'number', 'placeholder' => 'Enter participant link ID'],
            ], "Delete Announcement Participant");
            ?>
        </div>
    </main>
    
    <script src="./static/js/postData.js"></script>
    <script src="./static/js/prepaForms.js"></script>
    <script>
        // Form configuration mappings
        const formConfigurations = {
            // User Management
            "createUserForm": {
                action: "User_Create",
                fields: [
                    { id: "createUsername", key: "user_name" },
                    { id: "createEmail", key: "user_mail" },
                    { id: "createUserPassword", key: "user_password" },
                   
                    { id: "createUserImage", key: "user_image_profil" },
                    { id: "createUserDescription", key: "user_description_profil" }
                ]
            },
            "deleteUserForm": {
                action: "User_Delete",
                fields: [
                    { id: "deleteUserId", key: "user_id" }
                ],
                preprocessData: (data) => {
                    data.user_id = Number(data.user_id);
                }
            },
            "updateUserForm": {
                action: "User_Update",
                fields: [
                    { id: "updateUserId", key: "user_id" },
                    { id: "updateUsername", key: "user_name" },
                    { id: "updateEmail", key: "user_mail" },
                    { id: "updateUserPassword", key: "user_password" },
                   
                    { id: "updateUserImage", key: "user_image_profil" },
                    { id: "updateUserDescription", key: "user_description_profil" }
                ]
            },
            // Announcement Management
            "createAnnonceForm": {
                action: "Annonce_Create",
                fields: [
                    { id: "createAnnonceTitle", key: "annonce_title" },
                    { id: "createAnnonceDescription", key: "annonce_description" },
                    { id: "createAnnonceValue", key: "annonce_value" },
                    { id: "createAnnonceUserId", key: "user_id" }
                ]
            },
            "deleteAnnonceForm": {
                action: "Annonce_Delete",
                fields: [
                    { id: "deleteAnnonceId", key: "annonce_id" }
                ],
                preprocessData: (data) => {
                    data.annonce_id = Number(data.annonce_id);
                }
            },
            "updateAnnonceForm": {
                action: "Annonce_Update",
                fields: [
                    { id: "updateAnnonceId", key: "annonce_id" },
                    { id: "updateAnnonceTitle", key: "annonce_title" },
                    { id: "updateAnnonceDescription", key: "annonce_description" },
                    { id: "updateAnnonceValue", key: "annonce_value" },
                    { id: "updateAnnonceUserId", key: "user_id" }
                ]
            },
             // Comment Management
            "createCommentForm": {
                action: "Comment_Create",
                fields: [
                    { id: "createCommentAnnonceId", key: "annonce_id" },
                    { id: "createCommentUserId", key: "user_id" },
                    { id: "createCommentText", key: "commantaire_text" }
                ]
            },
            "updateCommentForm": {
                action: "Comment_Update",
                fields: [
                    { id: "updateCommentId", key: "commantaire_id" },
                    { id: "updateCommentAnnonceId", key: "annonce_id" },
                    { id: "updateCommentUserId", key: "user_id" },
                    { id: "updateCommentText", key: "commantaire_text" }
                ]
            },
            "deleteCommentForm": {
                action: "Comment_Delete",
                fields: [
                    { id: "deleteCommentId", key: "commantaire_id" }
                ],
                preprocessData: (data) => {
                    data.commantaire_id = Number(data.commantaire_id);
                }
            },

            // User Friend Management
            "createUserFriendForm": {
                action: "UserFriend_Create",
                fields: [
                    { id: "createUserFriendId1", key: "user_id_1" },
                    { id: "createUserFriendId2", key: "user_id_2" },
                    { id: "createUserFriendIcon", key: "user_friend_icon" }
                ]
            },
            "updateUserFriendForm": {
                action: "UserFriend_Update",
                fields: [
                    { id: "updateUserFriendId", key: "user_friend_id" },
                    { id: "updateUserFriendId1", key: "user_id_1" },
                    { id: "updateUserFriendId2", key: "user_id_2" },
                    { id: "updateUserFriendIcon", key: "user_friend_icon" }
                ]
            },
            "deleteUserFriendForm": {
                action: "UserFriend_Delete",
                fields: [
                    { id: "deleteUserFriendId", key: "user_friend_id" }
                ],
                preprocessData: (data) => {
                    data.user_friend_id = Number(data.user_friend_id);
                }
            },

            // Category Management
            "createCategoryForm": {
                action: "Category_Create",
                fields: [
                    { id: "createCategoryName", key: "category_name" },
                    { id: "createCategoryIcon", key: "category_icon" }
                ]
            },
            "updateCategoryForm": {
                action: "Category_Update",
                fields: [
                    { id: "updateCategoryId", key: "category_id" },
                    { id: "updateCategoryName", key: "category_name" },
                    { id: "updateCategoryIcon", key: "category_icon" }
                ]
            },
            "deleteCategoryForm": {
                action: "Category_Delete",
                fields: [
                    { id: "deleteCategoryId", key: "category_id" }
                ],
                preprocessData: (data) => {
                    data.category_id = Number(data.category_id);
                }
            },

            // Category Key Word Management
            "createCategoryKeyWordForm": {
                action: "CategoryKeyWord_Create",
                fields: [
                    { id: "createCategoryKeyWord", key: "category_key_word" },
                    { id: "createCategoryKeyWordCategoryId", key: "category_id" }
                ]
            },
            "updateCategoryKeyWordForm": {
                action: "CategoryKeyWord_Update",
                fields: [
                    { id: "updateCategoryKeyWordId", key: "category_key_word_id" },
                    { id: "updateCategoryKeyWord", key: "category_key_word" },
                    { id: "updateCategoryKeyWordCategoryId", key: "category_id" }
                ]
            },
            "deleteCategoryKeyWordForm": {
                action: "CategoryKeyWord_Delete",
                fields: [
                    { id: "deleteCategoryKeyWordId", key: "category_key_word_id" }
                ],
                preprocessData: (data) => {
                    data.category_key_word_id = Number(data.category_key_word_id);
                }
            },

            // Announcement Image Management
            "createAnnonceImageForm": {
                action: "AnnonceImage_Create",
                fields: [
                    { id: "createAnnonceImageAnnonceId", key: "annonce_id" },
                    { id: "createAnnonceImageName", key: "image_name" },
                    { id: "createAnnonceImageLink", key: "image_path" }
                ]
            },
            "updateAnnonceImageForm": {
                action: "AnnonceImage_Update",
                fields: [
                    { id: "updateAnnonceImageId", key: "image_id" },
                    { id: "updateAnnonceImageAnnonceId", key: "annonce_id" },
                    { id: "updateAnnonceImageName", key: "image_name" },
                    { id: "updateAnnonceImageLink", key: "image_path" }
                ]
            },
            "deleteAnnonceImageForm": {
                action: "AnnonceImage_Delete",
                fields: [
                    { id: "deleteAnnonceImageId", key: "image_id" }
                ],
                preprocessData: (data) => {
                    data.image_id = Number(data.image_id);
                }
            },

            // Announcement Like Management
            "createAnnonceLikeForm": {
                action: "AnnonceLike_Create",
                fields: [
                    { id: "createAnnonceLikeAnnonceId", key: "annonce_id" },
                    { id: "createAnnonceLikeUserId", key: "user_id" }
                ]
            },
            "deleteAnnonceLikeForm": {
                action: "AnnonceLike_Delete",
                fields: [
                    { id: "deleteAnnonceLikeId", key: "annonce_like_id" }
                ],
                preprocessData: (data) => {
                    data.annonce_like_id = Number(data.annonce_like_id);
                }
            },

            // Announcement Participant Management
            "createAnnonceParticipantForm": {
                action: "AnnonceParticipant_Create",
                fields: [
                    { id: "createAnnonceParticipantAnnonceId", key: "annonce_id" },
                    { id: "createAnnonceParticipantUserId", key: "user_id" }
                ]
            },
            "deleteAnnonceParticipantForm": {
                action: "AnnonceParticipant_Delete",
                fields: [
                    { id: "deleteAnnonceParticipantId", key: "annonce_participant_id" }
                ],
                preprocessData: (data) => {
                    data.annonce_participant_id = Number(data.annonce_participant_id);
                }
            }
            
        };

        
        

    </script>
<?php require_once "./component/foot.php"; ?>