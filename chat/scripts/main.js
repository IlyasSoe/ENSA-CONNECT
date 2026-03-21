document.addEventListener('DOMContentLoaded', () => {
  const msg = document.querySelector('.messages');
  const textarea = document.getElementById('message-content');
  const form = document.querySelector('.entree form');

  // Scroll en bas au chargement
  setTimeout(() => { msg.scrollTop = msg.scrollHeight; }, 0);

  // Auto-resize textarea
  textarea.addEventListener('input', () => {
    textarea.style.height = '50px';
    textarea.style.height = textarea.scrollHeight + 'px';
  });

  // Pusher — recevoir les messages
  const pusher = new Pusher('c922bfca140061b3ea91', { cluster: 'eu' });
  const channel = pusher.subscribe('chat');

  channel.bind('message', (data) => {
    ajouterMessage(data.text, 'receiver');
  });

  // Envoyer un message
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const texte = textarea.value.trim();
    if (!texte) return;

    fetch('send.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ text: texte })
    });

    ajouterMessage(texte, 'sender');
    textarea.value = '';
    textarea.style.height = '50px';
  });

  function ajouterMessage(texte, type) {
    const now = new Date();
    const date = `${now.toLocaleDateString()} ${now.getHours()}h${String(now.getMinutes()).padStart(2, '0')}`;
    const p = document.createElement('p');
    p.className = type;
    p.innerHTML = `
      <span class="content-msg">${texte}</span>
      <br>
      <span class="date">${date}</span>
    `;
    msg.appendChild(p);
    msg.scrollTop = msg.scrollHeight;
  }
});
