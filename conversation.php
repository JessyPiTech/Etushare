<?php 
require_once "./securiter/session.php";
require_once "./component/head.php";

$target_user_id = $_GET['user_id'] ?? null;
if (!$target_user_id) die("Invalid user ID");
?>

<style>
.chat-container {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.messages-container {
    height: 400px;
    overflow-y: auto;
    padding: 20px;
    background: #f5f5f5;
    border-radius: 8px;
    margin-bottom: 20px;
}

.message {
    margin: 10px 0;
    padding: 10px;
    border-radius: 8px;
    max-width: 70%;
}

.message.sent {
    background: #007bff;
    color: white;
    margin-left: auto;
}

.message.received {
    background: #e9ecef;
    color: black;
    margin-right: auto;
}

.message-form {
    display: flex;
    gap: 10px;
}

.message-input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.send-button {
    padding: 10px 20px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.send-button:hover {
    background: #0056b3;
}

.message-metadata {
    font-size: 0.8em;
    color: #666;
    margin-bottom: 5px;
}
</style>

<main>
    <div class="chat-container">
        <div id="messages-container" class="messages-container"></div>
        <form id="message-form" class="message-form">
            <input type="text" id="message-input" class="message-input" placeholder="Type your message..." required>
            <button type="submit" class="send-button">Send</button>
        </form>
    </div>
</main>

<script src="./static/js/postData.js"></script>
<script>
   const current_user_id = <?php echo $_SESSION['user_id']; ?>;
    const target_user_id = <?php echo $target_user_id; ?>;

    async function loadData() {
        await checkFriendship();
        loadMessages();
        setInterval(loadMessages, 5000);
    }

    async function checkFriendship() {
        try {
            const requestData = {
                action: "Friend_Check",
                user_id: target_user_id,
                current_user_id: current_user_id
            };
            
            const data = await postData(apiUrl, requestData);
            const {friendStatus } = data;
            console.log(friendStatus)
            if (friendStatus === 'not_friends') {
                window.location.href = `user-detail.php?user_id=${target_user_id}`;
            }
        } catch (error) {
            console.error('Error checking friendship:', error);
            window.location.href = `user-detail.php?user_id=${target_user_id}`;
        }
    }

    async function loadMessages() {
        try {
            const requestData = {
                action: "Message_Get",
                user_id_1: current_user_id,
                user_id_2: target_user_id
            };
            
            const data = await postData(apiUrl, requestData);
            if (data.success) {
                displayMessages(data.messages);
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }
    function displayMessages(messages) {
        const container = document.getElementById('messages-container');
        container.innerHTML = '';
        
        messages.forEach(message => {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('message');
            messageDiv.classList.add(message.user_id == current_user_id ? 'sent' : 'received');
            
            const metadata = document.createElement('div');
            metadata.classList.add('message-metadata');
            metadata.textContent = `${message.user_name} - ${new Date(message.user_friend_message_time).toLocaleString()}`;
            
            const messageText = document.createElement('div');
            messageText.textContent = message.user_friend_message_text;
            
            messageDiv.appendChild(metadata);
            messageDiv.appendChild(messageText);
            container.appendChild(messageDiv);
        });
        
        container.scrollTop = container.scrollHeight;
    }

    document.getElementById('message-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const input = document.getElementById('message-input');
        const messageText = input.value.trim();
        
        if (!messageText) return;
        
        try {
            const requestData = {
                action: "Message_Send",
                user_id: current_user_id,
                target_user_id: target_user_id,
                message_text: messageText
            };
            
            const data = await postData(apiUrl, requestData);
            if (data.success) {
                input.value = '';
                await loadMessages();
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    });
    loadData()
</script>

<?php require_once "./component/foot.php"; ?>