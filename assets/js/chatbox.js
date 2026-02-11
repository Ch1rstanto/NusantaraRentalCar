function toggleChat() {
    const window = document.getElementById('chat-window');
    window.style.display = (window.style.display === 'none' || window.style.display === '') ? 'flex' : 'none';
}

async function handleSend() {
    const input = document.getElementById('chat-input');
    const content = document.getElementById('chat-content');
    const message = input.value.trim();

    if (!message) return;

    // Tampilkan pesan user
    content.innerHTML += `<div style="background: #007bff; color: white; padding: 10px; border-radius: 10px; align-self: flex-end; max-width: 80%;">${message}</div>`;
    input.value = '';
    content.scrollTop = content.scrollHeight;

    // Kirim ke backend
    try {
        const response = await fetch('api/chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: message })
        });
        const data = await response.json();

        // Tampilkan jawaban AI
        content.innerHTML += `<div style="background: #e9ecef; padding: 10px; border-radius: 10px; align-self: flex-start; max-width: 80%;">${data.response}</div>`;
        content.scrollTop = content.scrollHeight;
    } catch (error) {
        console.error("Gagal mengirim pesan:", error);
    }
}
