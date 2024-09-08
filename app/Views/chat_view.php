<?= 
$this->include('header');
//$sender_id = $messages;
$user_id = session()->get('user_id');

?>

<style>
.chat-window {
    display: flex;
    flex-direction: column;
    height: 80vh;
    max-width: 600px;
    margin: 0 auto;
    border: 1px solid #ccc;
    border-radius: 10px;
    background-color: #f9f9f9;
    overflow: hidden;
}

.chat-messages {
    flex: 1;
    padding: 10px;
    overflow-y: auto;
    background: #e5ddd5;
}

.message {
    display: inline-block; /* Change from flex to inline-block */
    max-width: 80%;
    padding: 10px;
    margin: 5px 0;
    border-radius: 20px;
    line-height: 1.4;
    word-wrap: break-word;
}

.message.sent {
    background-color: #0084ff; /* Blue color for sender */
    color: white; /* White text color for better contrast */
    border-top-right-radius: 0;
    border-top-left-radius: 20px;
    float: right; /* Force alignment to the right */
    clear: both; /* Ensure the next element starts below this */
    text-align: right; /* Align text to the right */
}

.message.received {
    background-color: #ffffff; /* White color for receiver */
    border-top-left-radius: 0;
    border-top-right-radius: 20px;
    float: left; /* Force alignment to the left */
    clear: both; /* Ensure the next element starts below this */
    text-align: left; /* Align text to the left */
}

.message p {
    margin: 0;
}

.timestamp {
    font-size: 0.8em;
    color: #888;
    text-align: right;
    margin-top: 5px;
}

form {
    display: flex;
    padding: 10px;
    border-top: 1px solid #ccc;
    background-color: #f1f1f1;
}

textarea {
    flex: 1;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 20px;
    resize: none;
    outline: none;
    margin-right: 10px;
}

button {
    padding: 10px 20px;
    border: none;
    background-color: #4caf50;
    color: white;
    border-radius: 20px;
    cursor: pointer;
}

button:hover {
    background-color: #45a049;
}

.notification-icon {
    display: none;
    position: absolute;
    top: 5px;
    right: 5px;
    width: 10px;
    height: 10px;
    background-color: red;
    border-radius: 50%;
}
</style>

<div class="chat-window">
    <div class="chat-messages" id="chat-messages">
        <?php foreach ($messages as $message): ?>
            <div class="message <?= $message['sender_id'] == session()->get('user_id') ? 'sent' : 'received';?>">
                <p><?= esc($message['message']) ?></p>
                <span class="timestamp"><?= esc($message['created_at']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <form method="POST" action="<?= base_url('chat/sendMessage') ?>" onsubmit="scrollToBottom()">
        <?= csrf_field() ?>
        <input type="hidden" name="chat_room_id" value="<?= esc($chatRoomId) ?>">
        <textarea name="message" placeholder="Type a message..."></textarea>
        <button type="submit">Send</button>
    </form>
</div>

<script>
function scrollToBottom() {
    const chatMessages = document.getElementById('chat-messages');
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

document.addEventListener("DOMContentLoaded", function() {
    scrollToBottom();
});

function checkForNewMessages() {
    fetch('/chat/checkNewMessages')
        .then(response => response.json())
        .then(data => {
            if (data.newMessages > 0) {
                document.querySelector('.notification-icon').style.display = 'inline';
                scrollToBottom();
            } else {
                document.querySelector('.notification-icon').style.display = 'none';
            }
        });
}

setInterval(checkForNewMessages, 5000); // Check for new messages every 5 seconds
</script>
