<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etushare</title>
    <link type="text/css" rel="stylesheet" href="./static/css/normalize.css">
    <link type="text/css" rel="stylesheet" href="./static/css/style.css">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    
</head>
<body>
<header>
    <div>
        <a href="./"><img id="logo" src="./static/asset/logo_etushare-removebg-preview.png" alt=""></a>
        <div>
            
            <div class="shearch-bar">
                <input type="text" placeholder="Search..." class="shearch-input" />
                <ion-icon style="color: var(--black);" name="search-outline" onclick="toggleSearch()"></ion-icon>
            </div>
            <mini-header>
                <a href="create-annonce.php"  class="special flex-align-center" >
                    <ion-icon name="add-outline"></ion-icon>
                    <h3>Ajouter une annonce</h3>
                </a>
                <a href="Favoris.php" class="section  flex-complete">
                    <ion-icon name="heart-outline"></ion-icon>
                    <p>Favoris</p>
                </a>
                
                <a href="Chat.php" class="section  flex-complete">
                    <ion-icon name="chatbox-outline"></ion-icon>
                    <p>Chat</p>
                </a>
                <a href="auth.php" class="section  flex-complete">
                    <ion-icon name="person-outline"></ion-icon>
                    <p>Profil</p>
                </a>
            </mini-header>  
        </div>
    </div>
</header>
<script>
    function toggleSearch() {
        const searchBar = document.querySelector('.shearch-bar');
        const input = document.querySelector('.shearch-input');
        const logo = document.getElementById('logo');
        if (!searchBar.classList.contains('active')) {
            searchBar.classList.add('active');
            input.focus();
            logo.src = "./static/asset/logo_mini_edushare-removebg-preview.png";
            document.addEventListener('click', handleOutsideClick);
        }
    }

    function handleOutsideClick(event) {
        const searchBar = document.querySelector('.shearch-bar');
        const logo = document.getElementById('logo');
        if (!searchBar.contains(event.target)) {
            searchBar.classList.remove('active');
            logo.src = "./static/asset/logo_etushare-removebg-preview.png";
            document.removeEventListener('click', handleOutsideClick);
        }
    }
</script>
        <style>
            .connexion {
                display: flex;
                flex-direction: column;
                align-items: center;
                background: var(--back);
                max-width: 500px;
                margin: 50px auto;
                padding: 20px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                border-radius: 10px;
            }
            #switch_forms {
                margin-top: 20px;
                background: #007BFF;
                color: white;
                border: none;
                padding: 10px 15px;
                border-radius: 5px;
                cursor: pointer;
                transition: background 0.3s;
            }

            #switch_forms:hover {
                background: #0056b3;
            }
            .part{
                flex-direction: column;
            }
            .inscription{
                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
                gap: 10px;

            }
        </style>
        <main >
            <div class="connexion" >
                <div id="inscription" style="flex-wrap: wrap;width: 100%;" style="display: flex;" class="part">
                    <h2>Create User</h2>
                    <form id="createUserForm" class="inscription form-container">
                        <div class="form-group">
                            <label for="createUsername">Username</label>
                            <input type="text" id="createUsername" placeholder="Enter username" required>
                        </div>
                        <div class="form-group">
                            <label for="createEmail">Email</label>
                            <input type="email" id="createEmail" placeholder="Enter email" required>
                        </div>
                        <div class="form-group">
                            <label for="createUserPassword">Password</label>
                            <input type="password" id="createUserPassword" placeholder="Enter password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="createUserImageFile">Profile Image</label>
                            <input type="file" id="createUserImageFile" accept="image/*" required>
                        </div>
                        <div class="form-group">
                            <label for="createUserDescription">Profile Description</label>
                            <input type="text" id="createUserDescription" placeholder="Enter profile description" required>
                        </div>
                        <button class='button_spe' type="submit">Create User</button>
                        <p id="createUserFormError" class="error"></p>
                    </form>                
                </div>
                <div id="connexion" style="display: none;" class="part">
                    <h2>Login Auth</h2>
                    <form id="loginAuthForm" class="form-container">
                        <div class="form-group">
                            <label for="authLogin">Username or email</label>
                            <input type="text" id="authLogin" placeholder="Enter username or email" required>
                        </div>
                        <div class="form-group">
                            <label for="authLoginPassword">Password</label>
                            <input type="password" id="authLoginPassword" placeholder="Enter password" required>
                        </div>
                        <button class='button_spe' type="submit">Connexion</button>
                        <p id="loginAuthFormError" class="error"></p>
                    </form>                
                </div>
                <button id="switch_forms">Connexion</button>
            </div>
        </main>
        <script src="./static/js/postData.js"></script>
        <script src="./static/js/prepaForms.js"></script>
        <script>
            const form = document.getElementById("createUserForm");

            form.addEventListener("submit", async (event) => {
                event.preventDefault();

                const formData = new FormData();
                formData.append("action", "User_Create");
                formData.append("user_name", document.getElementById("createUsername").value.trim());
                formData.append("user_mail", document.getElementById("createEmail").value.trim());
                formData.append("user_password", document.getElementById("createUserPassword").value.trim());
                formData.append("user_description_profil", document.getElementById("createUserDescription").value.trim());

                const imageInput = document.getElementById("createUserImageFile");
                if (imageInput.files.length > 0) {
                    formData.append("user_image", imageInput.files[0]);
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
                        alert("User created successfully!");
                        form.reset();
                        document.getElementById('switch_forms').click();
                    } else {
                        alert(`Error: ${jsonResponse.error}`);
                    }
                } catch (error) {
                    console.error("Error:", error);
                }
            });
            const formConfigurations = {
                
                "loginAuthForm": {
                    action: "Auth_Login",
                    fields: [
                        { id: "authLogin", key: "authLogin" },
                        { id: "authLoginPassword", key: "authPassword" }
                    ]
                }
            };

            
            document.getElementById('switch_forms').addEventListener("click", function() {
                const inscription = document.getElementById('inscription');
                const connexion = document.getElementById('connexion');
                const paragraph = document.getElementById('switch_forms');

                if (inscription.style.display === "none") {
                    inscription.style.display = "flex";
                    connexion.style.display = "none";
                    paragraph.innerHTML = 'Connexion';
                } else {
                    inscription.style.display = "none";
                    connexion.style.display = "flex";
                    paragraph.innerHTML = 'Login';
                }
            });


        </script>
<mini-footer>
    <a href="./">
        <ion-icon name="search-outline"></ion-icon>
    </a>
    
    <a href="Favoris.php">
        <ion-icon name="heart-outline"></ion-icon>
    </a>
    <a href="create-annonce.php"  class="special flex-align-center" >
        <ion-icon name="add-outline"></ion-icon>
    </a>
    <a href="Chat.php">
        <ion-icon name="chatbox-outline"></ion-icon>
    </a>
    <a href="auth.php" class="section  flex-complete">
        <ion-icon name="person-outline"></ion-icon>
    </a>
    
</mini-footer>
<footer>
    <p>&copy; 2024 ManageApp. All rights reserved.</p>
</footer>
</body>
</html>