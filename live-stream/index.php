<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Verificar se usuário tem acesso
if (!isLoggedIn() || !hasVideotecaAccess()) {
    header("Location: /planos.php");
    exit;
}

$page_title = 'Live Stream - Translators101';
$page_description = 'Assista às palestras ao vivo da Translators101';

// Embed real do webinar da admin
$live_embed_code = '<div style="padding: 56.30% 0 0 0; position: relative"><div style="height:100%;left:0;position:absolute;top:0;width:100%"><iframe height="100%" width="100%;" src="https://webinar.mywave.video/YAbYCQ36MadzaG9s?embed" frameborder="0" allow="autoplay; fullscreen" scrolling="no"></iframe></div></div>';

// Live está ativa se temos embed code
$is_live_active = !empty(trim($live_embed_code));

// Verificar se usuário atual é admin
$current_user_is_admin = isAdmin();

include __DIR__ . '/../vision/includes/head.php';
include __DIR__ . '/../vision/includes/header.php';
include __DIR__ . '/../vision/includes/sidebar.php';
?>

<div class="main-content">
    <!-- Hero Section -->
    <div class="glass-hero">
        <div class="hero-content">
            <h1><i class="fas fa-broadcast-tower"></i> Live Stream Translators101</h1>
            <p>Participe das palestras ao vivo e interaja com outros participantes</p>
            <?php if ($is_live_active): ?>
                <div class="live-status live-active">
                    <i class="fas fa-circle pulse"></i> AO VIVO
                </div>
            <?php else: ?>
                <div class="live-status live-offline">
                    <i class="fas fa-circle"></i> OFFLINE
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="live-container">
        <!-- Player -->
        <div class="player-section">
            <div class="video-card player-card">
                <?php if ($is_live_active): ?>
                    <div class="live-player">
                        <div class="player-container">
                            <?php echo $live_embed_code; ?>
                        </div>
                        <div class="player-controls">
                            <button class="control-btn" onclick="toggleFullscreen()">
                                <i class="fas fa-expand"></i> Tela Cheia
                            </button>
                            <button class="control-btn" onclick="togglePictureInPicture()">
                                <i class="fas fa-external-link-alt"></i> PiP
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="offline-player">
                        <div class="offline-content">
                            <i class="fas fa-video-slash"></i>
                            <h3>Transmissão Offline</h3>
                            <p>No momento não há transmissões ao vivo.</p>
                            <p>Fique atento às nossas redes sociais para saber quando a próxima live começará!</p>
                            <div class="social-links">
                                <a href="#" class="social-link"><i class="fab fa-instagram"></i> Instagram</a>
                                <a href="#" class="social-link"><i class="fab fa-youtube"></i> YouTube</a>
                                <a href="#" class="social-link"><i class="fab fa-linkedin"></i> LinkedIn</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chat -->
        <div class="chat-section">
            <div class="video-card chat-card">
                <div class="chat-header">
                    <h3><i class="fas fa-comments"></i> Chat da Live</h3>
                    <div class="chat-controls">
                        <button class="control-btn small" onclick="toggleChat()" title="Minimizar Chat">
                            <i class="fas fa-minus"></i>
                        </button>
                        <?php if ($current_user_is_admin): ?>
                        <button class="control-btn small" onclick="clearChat()" title="Limpar Chat">
                            <i class="fas fa-broom"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="chat-messages" id="chatMessages">
                    <div class="system-message">
                        <i class="fas fa-info-circle"></i>
                        Bem-vindo ao chat da live! Seja respeitoso com outros participantes.
                    </div>
                    <!-- Mensagens do chat serão carregadas aqui -->
                    <div id="chatMessagesContainer"></div>
                </div>
                
                <?php if ($is_live_active): ?>
                <div class="chat-input-container">
                    <form id="chatForm" method="post" action="chat-save.php">
                        <div class="chat-input-group">
                            <input type="text" id="chatInput" name="message" placeholder="Digite sua mensagem..." maxlength="500" autocomplete="off" required>
                            <button type="submit" class="send-btn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <?php else: ?>
                <div class="chat-input-container">
                    <div class="chat-offline-message">
                        <i class="fas fa-info-circle"></i>
                        O chat estará disponível quando a transmissão estiver ao vivo.
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Overlay Preview (somente admin) -->
    <?php if ($current_user_is_admin): ?>
    <div class="video-card overlay-preview">
        <div class="card-header">
            <h2><i class="fas fa-tv"></i> Pré-visualização do Overlay</h2>
            <button type="button" class="cta-btn" onclick="clearOverlay()" style="padding: 8px 16px; font-size: 0.9rem;">
                <i class="fas fa-trash"></i> Remover da Tela
            </button>
        </div>
        <div id="overlayMessage" class="overlay-content">
            <div class="empty-state">
                <i class="fas fa-tv"></i>
                <p>Nenhuma mensagem selecionada para o overlay</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Agenda -->
    <div class="video-card schedule-card">
        <h2><i class="fas fa-calendar-alt"></i> Próximas Transmissões</h2>
        <div class="schedule-grid">
            <div class="schedule-item">
                <div class="schedule-date">
                    <div class="day">15</div>
                    <div class="month">DEZ</div>
                </div>
                <div class="schedule-info">
                    <h4>Tradução Técnica: Desafios e Soluções</h4>
                    <p><i class="fas fa-clock"></i> 19:00 - 21:00</p>
                    <p><i class="fas fa-user"></i> Prof. Maria Silva</p>
                </div>
                <div class="schedule-actions">
                    <button class="btn btn-outline">
                        <i class="fas fa-bell"></i> Lembrete
                    </button>
                </div>
            </div>
            
            <div class="schedule-item">
                <div class="schedule-date">
                    <div class="day">22</div>
                    <div class="month">DEZ</div>
                </div>
                <div class="schedule-info">
                    <h4>Workshop: Ferramentas de CAT</h4>
                    <p><i class="fas fa-clock"></i> 14:00 - 17:00</p>
                    <p><i class="fas fa-user"></i> Dr. João Santos</p>
                </div>
                <div class="schedule-actions">
                    <button class="btn btn-outline">
                        <i class="fas fa-bell"></i> Lembrete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Live Stream Specific Styles */
.live-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
    margin-bottom: 40px;
}

.player-section {
    min-height: 0;
}

.player-card {
    height: 100%;
}

.live-player {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.player-container {
    flex: 1;
    min-height: 400px;
    background: #000;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
}

.player-container iframe {
    width: 100%;
    height: 100%;
    border: none;
}

.player-controls {
    display: flex;
    gap: 12px;
    padding: 16px;
    background: rgba(0,0,0,0.1);
    border-top: 1px solid var(--glass-border);
}

.control-btn {
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    color: var(--text-light);
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.control-btn:hover {
    background: var(--brand-purple);
    color: #fff;
    transform: translateY(-2px);
}

.control-btn.small {
    padding: 6px 10px;
    font-size: 0.8rem;
}

.chat-section {
    min-height: 0;
}

.chat-card {
    height: 100%;
    display: flex;
    flex-direction: column;
    min-height: 500px;
}

.chat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    border-bottom: 1px solid var(--glass-border);
    background: rgba(142, 68, 173, 0.1);
}

.chat-header h3 {
    margin: 0;
    color: #fff;
    font-size: 1.1rem;
}

.chat-controls {
    display: flex;
    gap: 8px;
}

.chat-messages {
    flex: 1;
    padding: 16px;
    overflow-y: auto;
    max-height: 350px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.chat-message {
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--glass-border);
    border-radius: 12px;
    padding: 12px;
    backdrop-filter: blur(10px);
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}

.username {
    color: var(--brand-purple);
    font-weight: 600;
    font-size: 0.9rem;
}

.timestamp {
    color: #999;
    font-size: 0.8rem;
}

.message-content {
    color: var(--text-light);
    font-size: 0.9rem;
    line-height: 1.4;
}

.message-actions {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid var(--glass-border);
}

.btn-overlay {
    background: rgba(52, 152, 219, 0.2);
    border: 1px solid rgba(52, 152, 219, 0.3);
    color: #3498db;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-overlay:hover {
    background: rgba(52, 152, 219, 0.3);
    transform: translateY(-1px);
}

.system-message {
    background: rgba(46, 204, 113, 0.2);
    border: 1px solid rgba(46, 204, 113, 0.3);
    color: #2ecc71;
    padding: 12px;
    border-radius: 8px;
    text-align: center;
    font-size: 0.9rem;
}

.chat-offline-message {
    background: rgba(149, 165, 166, 0.2);
    border: 1px solid rgba(149, 165, 166, 0.3);
    color: #95a5a6;
    padding: 12px;
    border-radius: 8px;
    text-align: center;
    font-size: 0.9rem;
}

.chat-input-container {
    padding: 16px;
    border-top: 1px solid var(--glass-border);
    background: rgba(0,0,0,0.1);
}

.chat-input-group {
    display: flex;
    gap: 12px;
    align-items: center;
}

.chat-input-group input {
    flex: 1;
    padding: 12px;
    border-radius: 20px;
    border: 1px solid var(--glass-border);
    background: rgba(255,255,255,0.06);
    color: var(--text-light);
    font-size: 0.9rem;
}

.chat-input-group input:focus {
    outline: none;
    border-color: var(--brand-purple);
    box-shadow: 0 0 0 2px rgba(142, 68, 173, 0.2);
}

.send-btn {
    background: var(--brand-purple);
    border: none;
    color: #fff;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.send-btn:hover {
    background: var(--brand-purple-dark);
    transform: scale(1.1);
}

.live-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    margin-top: 20px;
}

.live-active {
    background: rgba(46, 204, 113, 0.2);
    border: 1px solid rgba(46, 204, 113, 0.3);
    color: #2ecc71;
}

.live-offline {
    background: rgba(149, 165, 166, 0.2);
    border: 1px solid rgba(149, 165, 166, 0.3);
    color: #95a5a6;
}

.pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.offline-player {
    height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #2c2c2e, #1c1c1e);
    border-radius: 12px;
}

.offline-content {
    text-align: center;
    padding: 40px;
}

.offline-content i {
    font-size: 4rem;
    color: #666;
    margin-bottom: 20px;
}

.offline-content h3 {
    color: #fff;
    margin-bottom: 16px;
    font-size: 1.5rem;
}

.offline-content p {
    color: #ccc;
    margin-bottom: 12px;
    line-height: 1.5;
}

.social-links {
    display: flex;
    gap: 16px;
    justify-content: center;
    margin-top: 24px;
}

.social-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: 20px;
    color: var(--text-light);
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.social-link:hover {
    background: var(--brand-purple);
    color: #fff;
    transform: translateY(-2px);
}

.overlay-preview {
    margin-top: 24px;
}

.overlay-content {
    min-height: 100px;
    background: rgba(0,0,0,0.3);
    border-radius: 8px;
    padding: 20px;
    border: 2px dashed var(--glass-border);
}

.schedule-grid {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-top: 20px;
}

.schedule-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--glass-border);
    border-radius: 12px;
    transition: all 0.3s ease;
}

.schedule-item:hover {
    background: rgba(255,255,255,0.08);
    transform: translateY(-2px);
}

.schedule-date {
    text-align: center;
    min-width: 60px;
}

.schedule-date .day {
    font-size: 2rem;
    font-weight: bold;
    color: var(--brand-purple);
    line-height: 1;
}

.schedule-date .month {
    font-size: 0.8rem;
    color: #ccc;
    text-transform: uppercase;
}

.schedule-info {
    flex: 1;
}

.schedule-info h4 {
    color: #fff;
    margin-bottom: 8px;
    font-size: 1.1rem;
}

.schedule-info p {
    color: #ccc;
    margin: 4px 0;
    font-size: 0.9rem;
}

.schedule-actions {
    min-width: 120px;
}

.btn-outline {
    background: transparent;
    border: 1px solid var(--brand-purple);
    color: var(--brand-purple);
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-outline:hover {
    background: var(--brand-purple);
    color: #fff;
}

/* Responsive */
@media (max-width: 1024px) {
    .live-container {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .chat-card {
        min-height: 400px;
    }
}

@media (max-width: 768px) {
    .schedule-item {
        flex-direction: column;
        text-align: center;
        gap: 12px;
    }
    
    .schedule-actions {
        min-width: auto;
    }
    
    .chat-controls {
        display: none;
    }
}
</style>

<!-- Passar informação do admin status para JavaScript -->
<script>
// Configurações globais passadas do PHP
const CURRENT_USER_IS_ADMIN = <?php echo $current_user_is_admin ? 'true' : 'false'; ?>;
const CURRENT_USER_NAME = '<?php echo addslashes($_SESSION['user_name'] ?? $_SESSION['nome'] ?? 'Você'); ?>';

// Array para armazenar mensagens localmente
let chatMessages = [];

// Chat AJAX functionality
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chatForm');
    
    if (chatForm) {
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const input = document.getElementById('chatInput');
            const msg = input.value.trim();
            if (!msg) return;

            // Desabilitar input durante envio
            input.disabled = true;
            const sendBtn = chatForm.querySelector('.send-btn');
            const originalContent = sendBtn.innerHTML;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            // Simular envio da mensagem
            setTimeout(() => {
                // Adicionar mensagem ao chat local
                const newMessage = {
                    id: Date.now(),
                    user_name: CURRENT_USER_NAME,
                    message: msg,
                    created_at: new Date().toISOString(),
                    isOwn: true,
                    isAdmin: CURRENT_USER_IS_ADMIN // Importante: marcar se usuário é admin
                };
                
                chatMessages.push(newMessage);
                addMessageToChat(newMessage);
                
                // Limpar input
                input.value = '';
                input.disabled = false;
                sendBtn.innerHTML = originalContent;
                
                // Auto-scroll
                scrollChatToBottom();
            }, 500);
        });
    }
    
    // Simular algumas mensagens iniciais
    setTimeout(() => {
        addInitialMessages();
    }, 1000);
});

function addInitialMessages() {
    const initialMessages = [
        {
            id: 1,
            user_name: 'Sistema',
            message: 'Chat da live iniciado!',
            created_at: new Date(Date.now() - 300000).toISOString(),
            isSystem: true
        },
        {
            id: 2,
            user_name: 'Admin',
            message: 'Bem-vindos à transmissão ao vivo! Em breve começaremos.',
            created_at: new Date(Date.now() - 120000).toISOString(),
            isAdmin: true
        }
    ];
    
    initialMessages.forEach(msg => {
        chatMessages.push(msg);
        addMessageToChat(msg);
    });
    
    scrollChatToBottom();
}

function addMessageToChat(msg) {
    const container = document.getElementById('chatMessagesContainer');
    const time = new Date(msg.created_at).toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
    
    const messageElement = document.createElement('div');
    messageElement.className = 'chat-message';
    
    let overlayButton = '';
    // Mostrar botão de overlay se:
    // 1. Usuario atual é admin E
    // 2. A mensagem não é do sistema E  
    // 3. (A mensagem é de admin OU é própria do usuário que é admin)
    if (CURRENT_USER_IS_ADMIN && !msg.isSystem) {
        overlayButton = `
            <div class="message-actions">
                <button class="btn-overlay" data-id="${msg.id}">
                    <i class="fas fa-tv"></i> Exibir na Tela
                </button>
            </div>
        `;
    }
    
    messageElement.innerHTML = `
        <div class="message-header">
            <strong class="username">${escapeHtml(msg.user_name)}</strong>
            <small class="timestamp">[${time}]</small>
        </div>
        <div class="message-content">
            ${escapeHtml(msg.message)}
        </div>
        ${overlayButton}
    `;
    
    container.appendChild(messageElement);
    
    // Adicionar eventos para botões de overlay se houver
    if (overlayButton) {
        addOverlayButtonEvents();
    }
}

function addOverlayButtonEvents() {
    document.querySelectorAll('.btn-overlay:not([data-bound])').forEach(btn => {
        btn.setAttribute('data-bound', 'true');
        btn.addEventListener('click', function() {
            const messageText = this.closest('.chat-message').querySelector('.message-content').textContent;
            const username = this.closest('.chat-message').querySelector('.username').textContent;
            
            // Atualizar preview do overlay
            const overlayContent = document.getElementById('overlayMessage');
            if (overlayContent) {
                overlayContent.innerHTML = `
                    <div class="overlay-message" style="background: rgba(142, 68, 173, 0.2); border: 1px solid rgba(142, 68, 173, 0.3); padding: 16px; border-radius: 8px; color: #fff;">
                        <strong style="color: var(--brand-purple);">${username}:</strong> ${messageText}
                    </div>
                `;
            }
            
            // Feedback visual
            this.innerHTML = '<i class="fas fa-check"></i> Enviado!';
            this.style.background = 'rgba(46, 204, 113, 0.3)';
            this.style.color = '#2ecc71';
            
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-tv"></i> Exibir na Tela';
                this.style.background = 'rgba(52, 152, 219, 0.2)';
                this.style.color = '#3498db';
            }, 2000);
        });
    });
}

function scrollChatToBottom() {
    const chatContainer = document.getElementById('chatMessages');
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

function escapeHtml(unsafe) {
    return unsafe
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
}

// Outras funções do chat
function toggleChat() {
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages.style.display === 'none') {
        chatMessages.style.display = 'flex';
    } else {
        chatMessages.style.display = 'none';
    }
}

function clearChat() {
    if (confirm('Limpar todas as mensagens do chat?')) {
        chatMessages = [];
        document.getElementById('chatMessagesContainer').innerHTML = '';
    }
}

// Função sem confirmação para remover overlay
function clearOverlay() {
    const overlayContent = document.getElementById('overlayMessage');
    if (overlayContent) {
        overlayContent.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-tv"></i>
                <p>Nenhuma mensagem selecionada para o overlay</p>
            </div>
        `;
    }
}

function toggleFullscreen() {
    const player = document.querySelector('.player-container iframe');
    if (player && player.requestFullscreen) {
        player.requestFullscreen();
    } else {
        alert('Seu navegador não suporta tela cheia ou não há player ativo.');
    }
}

function togglePictureInPicture() {
    alert('Funcionalidade Picture-in-Picture em desenvolvimento. Será implementada em breve!');
}
</script>

<?php include __DIR__ . '/../vision/includes/footer.php'; ?>