<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Verificar acesso à videoteca
if (!function_exists('hasVideotecaAccess') || !hasVideotecaAccess()) {
    header('Location: /index.php');
    exit;
}

$page_title = "Videoteca - Translators101";
$page_description = "Explore nossa biblioteca de palestras especializadas em tradução e interpretação";

// Parâmetros de busca e filtros
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$sort_order = $_GET['sort'] ?? 'season_asc';

// Consulta principal das palestras
$sql = "SELECT * FROM lectures WHERE 1=1";
$params = [];

// Filtro de busca
if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR speaker LIKE ? OR description LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Filtro de categoria
if (!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $lectures = $stmt->fetchAll();
} catch (Exception $e) {
    $lectures = [];
}

// Buscar categorias para o dropdown
try {
    $stmt = $pdo->prepare("SELECT DISTINCT category FROM lectures WHERE category IS NOT NULL AND category != '' ORDER BY category");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $categories = ['Tradução', 'Interpretação', 'Línguas', 'Tecnologia', 'Negócios'];
}

// Função para extrair temporada e episódio
function extractSeasonEpisode($title) {
    if (preg_match('/S(\d+)E(\d+)/i', $title, $matches)) {
        return [
            'season' => (int)$matches[1],
            'episode' => (int)$matches[2],
            'has_pattern' => true
        ];
    }
    return ['season' => 0, 'episode' => 0, 'has_pattern' => false];
}

// Função de ordenação
function sortLectures($lectures, $sort_order) {
    $withPattern = [];
    $withoutPattern = [];
    
    foreach ($lectures as $lecture) {
        $seasonEpisode = extractSeasonEpisode($lecture['title']);
        if ($seasonEpisode['has_pattern']) {
            $lecture['season'] = $seasonEpisode['season'];
            $lecture['episode'] = $seasonEpisode['episode'];
            $withPattern[] = $lecture;
        } else {
            $withoutPattern[] = $lecture;
        }
    }
    
    switch ($sort_order) {
        case 'season_asc':
            usort($withPattern, function($a, $b) {
                if ($a['season'] != $b['season']) {
                    return $a['season'] - $b['season'];
                }
                return $a['episode'] - $b['episode'];
            });
            usort($withoutPattern, function($a, $b) {
                return strcmp($a['title'], $b['title']);
            });
            return array_merge($withPattern, $withoutPattern);
            
        case 'season_desc':
            usort($withPattern, function($a, $b) {
                if ($a['season'] != $b['season']) {
                    return $b['season'] - $a['season'];
                }
                return $b['episode'] - $a['episode'];
            });
            usort($withoutPattern, function($a, $b) {
                return strcmp($b['title'], $a['title']);
            });
            return array_merge($withPattern, $withoutPattern);
            
        case 'alpha_asc':
            $all = array_merge($withPattern, $withoutPattern);
            usort($all, function($a, $b) {
                return strcmp($a['title'], $b['title']);
            });
            return $all;
            
        case 'alpha_desc':
            $all = array_merge($withPattern, $withoutPattern);
            usort($all, function($a, $b) {
                return strcmp($b['title'], $a['title']);
            });
            return $all;
            
        case 'date_asc':
            $all = array_merge($withPattern, $withoutPattern);
            usort($all, function($a, $b) {
                return strtotime($a['created_at']) - strtotime($b['created_at']);
            });
            return $all;
            
        case 'date_desc':
            $all = array_merge($withPattern, $withoutPattern);
            usort($all, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            return $all;
            
        default:
            return array_merge($withPattern, $withoutPattern);
    }
}

// Aplicar ordenação
$lectures = sortLectures($lectures, $sort_order);

include __DIR__ . '/vision/includes/head.php';
include __DIR__ . '/vision/includes/header.php';
include __DIR__ . '/vision/includes/sidebar.php';
?>

<div class="main-content">
    <div class="glass-hero">
        <div class="hero-content">
            <h1 class="videoteca-title">
                <i class="fas fa-video"></i>
                Videoteca
            </h1>
            <p>Explore nossa biblioteca de palestras especializadas em tradução e interpretação</p>
        </div>
    </div>

    <!-- Filtros de busca otimizados -->
    <div class="videoteca-filtros">
        <form method="GET" action="" class="filtros-form">
            <div class="filtros-grid-improved">
                <div class="search-field-expanded">
                    <label class="form-label">Buscar por palavra-chave</label>
                    <input type="text" 
                           name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Digite o título, palestrante ou descrição..."
                           class="search-input-large">
                </div>
                
                <div class="category-field">
                    <label class="form-label">Categoria</label>
                    <select name="category" class="category-select">
                        <option value="">Todas as categorias</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" 
                                    <?php echo ($category === $cat) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="sort-field">
                    <label class="form-label">Ordenar por</label>
                    <select name="sort" class="sort-select">
                        <option value="season_asc" <?php echo ($sort_order === 'season_asc') ? 'selected' : ''; ?>>Temporada (crescente)</option>
                        <option value="season_desc" <?php echo ($sort_order === 'season_desc') ? 'selected' : ''; ?>>Temporada (decrescente)</option>
                        <option value="alpha_asc" <?php echo ($sort_order === 'alpha_asc') ? 'selected' : ''; ?>>Alfabética (A-Z)</option>
                        <option value="alpha_desc" <?php echo ($sort_order === 'alpha_desc') ? 'selected' : ''; ?>>Alfabética (Z-A)</option>
                        <option value="date_asc" <?php echo ($sort_order === 'date_asc') ? 'selected' : ''; ?>>Mais antigas</option>
                        <option value="date_desc" <?php echo ($sort_order === 'date_desc') ? 'selected' : ''; ?>>Mais recentes</option>
                    </select>
                </div>
                
                <div class="search-btn-container">
                    <button type="submit" class="cta-btn search-btn">
                        <i class="fas fa-search"></i>
                        Buscar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php if (function_exists('isAdmin') && isAdmin()): ?>
    <div class="admin-section">
        <div class="video-card">
            <h3><i class="fas fa-cog"></i> Administração</h3>
            <div class="admin-actions">
                <a href="/admin/palestras.php" class="cta-btn admin-btn">
                    <i class="fas fa-plus"></i> Gerenciar palestras
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Grid de vídeos com 4 colunas -->
    <?php if (!empty($lectures)): ?>
        <div class="video-grid video-grid-four">
            <?php 
            $newestLecture = $lectures[0] ?? null;
            foreach ($lectures as $index => $lecture): 
                $isNewest = ($newestLecture && $lecture['id'] === $newestLecture['id']);
            ?>
                <div class="video-card" onclick="location.href='/palestra.php?id=<?php echo $lecture['id']; ?>'">
                    <div class="video-thumb-container">
                        <div class="video-thumb">
                            <?php if (!empty($lecture['thumbnail_url'])): ?>
                                <img src="<?php echo htmlspecialchars($lecture['thumbnail_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($lecture['title']); ?>"
                                     class="video-image">
                            <?php else: ?>
                                <div class="video-placeholder">
                                    <i class="fas fa-video placeholder-icon"></i>
                                    <span class="placeholder-text">Palestra</span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($isNewest): ?>
                                <div class="badge-new">
                                    <i class="fas fa-star"></i> NOVA
                                </div>
                            <?php endif; ?>
                            
                            <div class="video-overlay">
                                <div class="play-button">
                                    <i class="fas fa-play"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="video-info">
                        <h3><?php echo htmlspecialchars($lecture['title']); ?></h3>
                        
                        <?php if (!empty($lecture['speaker'])): ?>
                            <div class="video-speaker"><?php echo htmlspecialchars($lecture['speaker']); ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($lecture['description'])): ?>
                            <p class="video-desc"><?php echo htmlspecialchars(substr($lecture['description'], 0, 100)); ?>...</p>
                        <?php endif; ?>
                        
                        <div class="video-meta">
                            <?php if (!empty($lecture['category'])): ?>
                                <div class="video-category">
                                    <?php echo htmlspecialchars($lecture['category']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($lecture['duration_minutes'])): ?>
                                <div class="video-duration">
                                    <i class="fas fa-clock"></i>
                                    <?php echo $lecture['duration_minutes']; ?>min
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($lecture['is_featured']) && $lecture['is_featured']): ?>
                            <div class="featured-section">
                                <span class="tag featured-tag">
                                    <i class="fas fa-star"></i> Destaque
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-search"></i>
            </div>
            <h2 class="empty-title">Nenhuma palestra encontrada</h2>
            <p class="empty-description">
                Tente ajustar os filtros de busca ou escolher uma categoria diferente.
            </p>
            <a href="?search=&category=&sort=season_asc" class="cta-btn">
                <i class="fas fa-refresh"></i>
                Limpar filtros
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/vision/includes/footer.php'; ?>