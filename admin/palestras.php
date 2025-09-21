<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Verificar se é admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: /login.php');
    exit;
}

$page_title = 'Gerenciar Palestras - Admin';
$message = '';
$error = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'add_lecture':
                $title = trim($_POST['title'] ?? '');
                $speaker = trim($_POST['speaker'] ?? '');
                $speaker_minibio = trim($_POST['speaker_minibio'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $duration_minutes = (int)($_POST['duration_minutes'] ?? 0);
                $embed_code = trim($_POST['embed_code'] ?? '');
                $thumbnail_url = trim($_POST['thumbnail_url'] ?? '');
                $category = trim($_POST['category'] ?? '');
                $tags = trim($_POST['tags'] ?? '');
                $is_featured = isset($_POST['is_featured']) ? 1 : 0;
                $is_live = isset($_POST['is_live']) ? 1 : 0;
                $language = trim($_POST['language'] ?? '');
                $level = trim($_POST['level'] ?? '');
                
                if (empty($title)) {
                    throw new Exception('Título é obrigatório');
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO lectures (
                        id, title, speaker, speaker_minibio, description, duration_minutes, 
                        embed_code, thumbnail_url, category, tags, is_featured, is_live, 
                        language, level, created_at, updated_at
                    ) VALUES (
                        UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
                    )
                ");
                $stmt->execute([
                    $title, $speaker, $speaker_minibio, $description, $duration_minutes,
                    $embed_code, $thumbnail_url, $category, $tags, $is_featured, $is_live,
                    $language, $level
                ]);
                $message = 'Palestra adicionada com sucesso!';
                break;
                
            case 'toggle_featured':
                $lecture_id = trim($_POST['lecture_id'] ?? '');
                if (!empty($lecture_id)) {
                    $stmt = $pdo->prepare("UPDATE lectures SET is_featured = NOT is_featured, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$lecture_id]);
                    $message = 'Status de destaque atualizado!';
                }
                break;
                
            case 'toggle_live':
                $lecture_id = trim($_POST['lecture_id'] ?? '');
                if (!empty($lecture_id)) {
                    $stmt = $pdo->prepare("UPDATE lectures SET is_live = NOT is_live, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$lecture_id]);
                    $message = 'Status ao vivo atualizado!';
                }
                break;
                
            case 'delete_lecture':
                $lecture_id = trim($_POST['lecture_id'] ?? '');
                if (!empty($lecture_id)) {
                    $stmt = $pdo->prepare("DELETE FROM lectures WHERE id = ?");
                    $stmt->execute([$lecture_id]);
                    $message = 'Palestra removida com sucesso!';
                }
                break;
        }
    } catch (Exception $e) {
        $error = 'Erro ao processar ação: ' . $e->getMessage();
    }
}

// Buscar palestras
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$level_filter = $_GET['level'] ?? '';

try {
    $where_conditions = [];
    $params = [];
    
    if ($search) {
        $where_conditions[] = "(title LIKE ? OR speaker LIKE ? OR description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($category_filter) {
        $where_conditions[] = "category = ?";
        $params[] = $category_filter;
    }
    
    if ($level_filter) {
        $where_conditions[] = "level = ?";
        $params[] = $level_filter;
    }
    
    $where_clause = '';
    if (!empty($where_conditions)) {
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
    }
    
    // Query com todos os campos da tabela
    $stmt = $pdo->prepare("
        SELECT 
            id, title, speaker, speaker_minibio, description, duration_minutes,
            embed_code, thumbnail_url, category, tags, is_featured, is_live,
            language, level, created_at, updated_at
        FROM lectures 
        $where_clause 
        ORDER BY created_at DESC
    ");
    $stmt->execute($params);
    $lectures = $stmt->fetchAll();
    
    // Buscar categorias para filtro
    $stmt = $pdo->query("SELECT DISTINCT category FROM lectures WHERE category IS NOT NULL AND category != '' ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Buscar níveis para filtro
    $stmt = $pdo->query("SELECT DISTINCT level FROM lectures WHERE level IS NOT NULL AND level != '' ORDER BY level");
    $levels = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    $lectures = [];
    $categories = [];
    $levels = [];
    $error = 'Erro ao carregar palestras: ' . $e->getMessage();
}

include __DIR__ . '/../vision/includes/head.php';
include __DIR__ . '/../vision/includes/header.php';
include __DIR__ . '/../vision/includes/sidebar.php';
?>

<div class="main-content">
    <div class="glass-hero">
        <div class="hero-content">
            <h1><i class="fas fa-video"></i> Gerenciar Palestras</h1>
            <p>Administre o conteúdo educacional completo da plataforma</p>
            <a href="index.php" class="cta-btn">
                <i class="fas fa-arrow-left"></i> Voltar ao Admin
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert-error">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Formulário Completo -->
    <div class="vision-form">
        <div class="card-header">
            <h2><i class="fas fa-plus-circle"></i> Adicionar Nova Palestra</h2>
        </div>
        
        <form method="POST">
            <input type="hidden" name="action" value="add_lecture">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="title"><i class="fas fa-heading"></i> Título *</label>
                    <input type="text" id="title" name="title" required maxlength="500">
                </div>
                
                <div class="form-group">  
                    <label for="speaker"><i class="fas fa-user"></i> Palestrante</label>
                    <input type="text" id="speaker" name="speaker" maxlength="255">
                </div>
                
                <div class="form-group">
                    <label for="category"><i class="fas fa-tag"></i> Categoria</label>
                    <input type="text" id="category" name="category" maxlength="100" 
                           placeholder="ex: Tradução, Interpretação, etc.">
                </div>
                
                <div class="form-group">
                    <label for="duration_minutes"><i class="fas fa-clock"></i> Duração (min)</label>
                    <input type="number" id="duration_minutes" name="duration_minutes" min="0" max="600" 
                           placeholder="ex: 60">
                </div>
                
                <div class="form-group">
                    <label for="level"><i class="fas fa-layer-group"></i> Nível</label>
                    <select id="level" name="level">
                        <option value="">Selecionar nível</option>
                        <option value="Iniciante">Iniciante</option>
                        <option value="Intermediário">Intermediário</option>
                        <option value="Avançado">Avançado</option>
                        <option value="Todos">Todos os níveis</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="language"><i class="fas fa-language"></i> Idioma</label>
                    <select id="language" name="language">
                        <option value="">Selecionar idioma</option>
                        <option value="pt-BR">Português (BR)</option>
                        <option value="en">Inglês</option>
                        <option value="es">Espanhol</option>
                        <option value="fr">Francês</option>
                    </select>
                </div>
                
                <div class="form-group form-group-wide">
                    <label for="speaker_minibio"><i class="fas fa-id-card"></i> Mini-bio do Palestrante</label>
                    <textarea id="speaker_minibio" name="speaker_minibio" rows="2" maxlength="500"
                              placeholder="Breve biografia do palestrante..."></textarea>
                </div>
                
                <div class="form-group form-group-wide">
                    <label for="description"><i class="fas fa-align-left"></i> Descrição</label>
                    <textarea id="description" name="description" rows="3" maxlength="2000"
                              placeholder="Descreva o conteúdo da palestra..."></textarea>
                </div>
                
                <div class="form-group form-group-wide">
                    <label for="embed_code"><i class="fas fa-code"></i> Código de Embed</label>
                    <textarea id="embed_code" name="embed_code" rows="3" maxlength="2000"
                              placeholder="Cole aqui o código de embed do vídeo..."></textarea>
                </div>
                
                <div class="form-group form-group-wide">
                    <label for="thumbnail_url"><i class="fas fa-image"></i> URL da Thumbnail</label>
                    <input type="url" id="thumbnail_url" name="thumbnail_url" maxlength="1000"
                           placeholder="https://exemplo.com/thumbnail.jpg">
                </div>
                
                <div class="form-group form-group-wide">
                    <label for="tags"><i class="fas fa-tags"></i> Tags (separadas por vírgula)</label>
                    <input type="text" id="tags" name="tags" maxlength="500"
                           placeholder="tradução, técnica, business, legal">
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_featured" value="1">
                        <i class="fas fa-star"></i> Destacar palestra
                    </label>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_live" value="1">
                        <i class="fas fa-broadcast-tower"></i> Ao vivo
                    </label>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="cta-btn">
                    <i class="fas fa-save"></i> Adicionar Palestra
                </button>
            </div>
        </form>
    </div>

    <!-- Filtros Avançados -->
    <div class="vision-form">
        <div class="card-header">
            <h2><i class="fas fa-filter"></i> Filtros Avançados</h2>
        </div>
        
        <form method="GET" class="search-form">
            <div class="search-group">
                <input type="text" name="search" placeholder="Buscar por título, palestrante ou descrição..." 
                       value="<?php echo htmlspecialchars($search); ?>" style="width: 250px;">
                
                <select name="category" style="width: 150px;">
                    <option value="">Todas as categorias</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category); ?>" 
                                <?php echo $category_filter === $category ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="level" style="width: 120px;">
                    <option value="">Todos os níveis</option>
                    <?php foreach ($levels as $level): ?>
                        <option value="<?php echo htmlspecialchars($level); ?>" 
                                <?php echo $level_filter === $level ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($level); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="cta-btn" style="padding: 10px 20px;">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                
                <a href="palestras.php" class="page-btn" style="padding: 10px 20px;">
                    <i class="fas fa-times"></i> Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Lista Completa -->
    <div class="vision-table">
        <div class="card-header">
            <h2><i class="fas fa-list"></i> Palestras Cadastradas (<?php echo count($lectures); ?>)</h2>
        </div>
        
        <?php if (empty($lectures)): ?>
            <div class="empty-state">
                <i class="fas fa-video-slash"></i>
                <h3>Nenhuma palestra encontrada</h3>
                <p>Adicione a primeira palestra ou ajuste os filtros de busca.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-video"></i> Palestra</th>
                            <th><i class="fas fa-user"></i> Palestrante</th>
                            <th><i class="fas fa-tag"></i> Categoria</th>
                            <th><i class="fas fa-clock"></i> Duração</th>
                            <th><i class="fas fa-star"></i> Status</th>
                            <th><i class="fas fa-cogs"></i> Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lectures as $lecture): ?>
                            <tr>
                                <td>
                                    <div class="project-info">
                                        <span class="text-primary" title="<?php echo htmlspecialchars($lecture['title']); ?>">
                                            <?php echo htmlspecialchars(substr($lecture['title'], 0, 60)); ?>
                                            <?php if (strlen($lecture['title']) > 60) echo '...'; ?>
                                        </span>
                                        <span class="project-client">
                                            <?php 
                                            $description = $lecture['description'] ?? '';
                                            echo htmlspecialchars(substr($description, 0, 80));
                                            if (strlen($description) > 80) echo '...';
                                            ?>
                                        </span>
                                        <?php if (!empty($lecture['level'])): ?>
                                            <span class="level-badge"><?php echo htmlspecialchars($lecture['level']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($lecture['speaker'] ?: '-'); ?>
                                    <?php if (!empty($lecture['language'])): ?>
                                        <br><small style="color: #888;"><?php echo htmlspecialchars($lecture['language']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($lecture['category'] ?: 'Geral'); ?></td>
                                <td>
                                    <?php 
                                    $duration = (int)($lecture['duration_minutes'] ?? 0);
                                    echo $duration > 0 ? $duration . ' min' : '-'; 
                                    ?>
                                </td>
                                <td>
                                    <?php $isFeatured = (bool)($lecture['is_featured'] ?? false); ?>
                                    <?php $isLive = (bool)($lecture['is_live'] ?? false); ?>
                                    
                                    <?php if ($isLive): ?>
                                        <span class="status-badge status-live">AO VIVO</span>
                                    <?php elseif ($isFeatured): ?>
                                        <span class="status-badge status-completed">DESTAQUE</span>
                                    <?php else: ?>
                                        <span class="status-badge status-cancelled">PADRÃO</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <!-- Toggle Destaque -->
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_featured">
                                            <input type="hidden" name="lecture_id" value="<?php echo htmlspecialchars($lecture['id']); ?>">
                                            <button type="submit" class="page-btn" title="Alternar Destaque">
                                                <?php $isFeatured = (bool)($lecture['is_featured'] ?? false); ?>
                                                <i class="fas fa-star<?php echo $isFeatured ? '' : '-o'; ?>"></i>
                                            </button>
                                        </form>
                                        
                                        <!-- Toggle Live -->
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_live">
                                            <input type="hidden" name="lecture_id" value="<?php echo htmlspecialchars($lecture['id']); ?>">
                                            <button type="submit" class="page-btn" title="Alternar Ao Vivo">
                                                <?php $isLive = (bool)($lecture['is_live'] ?? false); ?>
                                                <i class="fas fa-broadcast-tower" style="color: <?php echo $isLive ? '#e74c3c' : '#95a5a6'; ?>"></i>
                                            </button>
                                        </form>
                                        
                                        <!-- Ver Embed -->
                                        <?php if (!empty(trim($lecture['embed_code'] ?? ''))): ?>
                                            <button class="page-btn" onclick="showEmbedModal('<?php echo htmlspecialchars($lecture['id']); ?>')" title="Ver Embed">
                                                <i class="fas fa-code"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <!-- Deletar -->
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Tem certeza que deseja remover esta palestra?');">
                                            <input type="hidden" name="action" value="delete_lecture">
                                            <input type="hidden" name="lecture_id" value="<?php echo htmlspecialchars($lecture['id']); ?>">
                                            <button type="submit" class="page-btn" style="color: #e74c3c;" title="Remover Palestra">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Estatísticas Completas -->
    <div class="stats-grid" style="margin-top: 40px;">
        <div class="card stats-card">
            <div class="stats-content">
                <div class="stats-info">
                    <h3>Total de Palestras</h3>
                    <span class="stats-number"><?php echo count($lectures); ?></span>
                </div>
                <div class="stats-icon stats-icon-blue">
                    <i class="fas fa-video"></i>
                </div>
            </div>
        </div>
        
        <div class="card stats-card">
            <div class="stats-content">
                <div class="stats-info">
                    <h3>Em Destaque</h3>
                    <?php $featuredLectures = array_filter($lectures, function($l) { return (bool)($l['is_featured'] ?? false); }); ?>
                    <span class="stats-number"><?php echo count($featuredLectures); ?></span>
                </div>
                <div class="stats-icon stats-icon-green">
                    <i class="fas fa-star"></i>
                </div>
            </div>
        </div>
        
        <div class="card stats-card">
            <div class="stats-content">
                <div class="stats-info">
                    <h3>Ao Vivo</h3>
                    <?php $liveLectures = array_filter($lectures, function($l) { return (bool)($l['is_live'] ?? false); }); ?>
                    <span class="stats-number"><?php echo count($liveLectures); ?></span>
                </div>
                <div class="stats-icon stats-icon-red">
                    <i class="fas fa-broadcast-tower"></i>
                </div>
            </div>
        </div>
        
        <div class="card stats-card">
            <div class="stats-content">
                <div class="stats-info">
                    <h3>Duração Total</h3>
                    <?php 
                    $totalDuration = array_sum(array_map(function($l) { return (int)($l['duration_minutes'] ?? 0); }, $lectures));
                    $hours = floor($totalDuration / 60);
                    $minutes = $totalDuration % 60;
                    ?>
                    <span class="stats-number"><?php echo $hours; ?>h <?php echo $minutes; ?>m</span>
                </div>
                <div class="stats-icon stats-icon-purple">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos específicos para a página de palestras */
.form-group textarea {
    resize: vertical;
    min-height: 60px;
}

.form-group label input[type="checkbox"] {
    margin-right: 8px;
}

.project-client {
    font-size: 0.85rem;
    color: #ccc !important;
    display: block;
    margin-top: 4px;
}

.level-badge {
    background: rgba(142, 68, 173, 0.2);
    color: var(--brand-purple);
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    margin-top: 4px;
    display: inline-block;
}

.action-buttons {
    display: flex;
    gap: 6px;
    align-items: center;
    flex-wrap: wrap;
}

.action-buttons form {
    margin: 0;
}

.search-group {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.status-badge.status-live {
    background: rgba(231, 76, 60, 0.2);
    color: #e74c3c;
    animation: pulse 2s infinite;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state i {
    font-size: 4rem;
    color: #666;
    margin-bottom: 20px;
    display: block;
}

.empty-state h3 {
    color: #fff;
    margin-bottom: 12px;
    font-size: 1.4rem;
}

.empty-state p {
    color: #ccc;
    margin: 0;
}

@media (max-width: 768px) {
    .search-group {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-group input,
    .search-group select,
    .search-group button,
    .search-group a {
        width: 100% !important;
        margin-bottom: 8px;
    }
    
    .action-buttons {
        justify-content: center;
    }
}
</style>

<script>
function showEmbedModal(lectureId) {
    // Funcionalidade para mostrar modal com código embed
    alert('Funcionalidade de visualização de embed em desenvolvimento');
}
</script>

<?php include __DIR__ . '/../vision/includes/footer.php'; ?>