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

  // WebSocket
  const conn = new WebSocket('ws://ensa-connect-production.up.railway.app');

  conn.onopen = () => {
    console.log("Connecté!");
  };

  // Recevoir un message
  conn.onmessage = (e) => {
    ajouterMessage(e.data, 'receiver');
  };

  // Envoyer un message
  form.addEventListener('submit', (e) => {
    e.preventDefault();

    const texte = textarea.value.trim();
    if (!texte) return;

    conn.send(texte);
    ajouterMessage(texte, 'sender');  // afficher localement

    // Reset textarea
    textarea.value = '';
    textarea.style.height = '50px';
  });

  function ajouterMessage(texte, type) {
    const now = new Date();
    const date = `${now.toLocaleDateString()} ${now.getHours()}h ${String(now.getMinutes()).padStart(2, '0')}`;

    const p = document.createElement('p');
    p.className = type;
    p.innerHTML = `
      <span class="content-msg">${texte}</span>
      <br>
      <span class="date">${date}</span>
    `;

    msg.appendChild(p);
    msg.scrollTop = msg.scrollHeight;  // scroll en bas
  }

});
