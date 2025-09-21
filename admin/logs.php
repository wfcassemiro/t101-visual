<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Verificar se é admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: /login.php');
    exit;
}

$page_title = 'Logs do Sistema - Admin';
$message = '';
$error = '';

// Parâmetros de filtro
$log_type = $_GET['type'] ?? '';
$date_filter = $_GET['date'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Buscar logs
try {
    $where_conditions = [];
    $params = [];
    
    if ($log_type) {
        $where_conditions[] = "log_type = ?";
        $params[] = $log_type;
    }
    
    if ($date_filter) {
        $where_conditions[] = "DATE(created_at) = ?";
        $params[] = $date_filter;
    }
    
    $where_clause = '';
    if (!empty($where_conditions)) {
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
    }
    
    // Buscar logs com paginação
    $stmt = $pdo->prepare("
        SELECT * FROM system_logs 
        $where_clause 
        ORDER BY created_at DESC 
        LIMIT $per_page OFFSET $offset
    ");
    $stmt->execute($params);
    $logs = $stmt->fetchAll();
    
    // Total para paginação
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM system_logs $where_clause");
    $stmt->execute($params);
    $total_logs = $stmt->fetchColumn();
    $total_pages = ceil($total_logs / $per_page);
    
    // Buscar tipos de log disponíveis
    $stmt = $pdo->query("SELECT DISTINCT log_type FROM system_logs ORDER BY log_type");
    $log_types = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Estatísticas
    $stmt = $pdo->query("SELECT COUNT(*) FROM system_logs WHERE DATE(created_at) = CURDATE()");
    $today_logs = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM system_logs WHERE log_level = 'ERROR'");
    $error_logs = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM system_logs WHERE log_level = 'WARNING'");
    $warning_logs = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    $logs = [];
    $log_types = [];
    $total_logs = 0;
    $total_pages = 0;
    $today_logs = 0;
    $error_logs = 0;
    $warning_logs = 0;
    $error = 'Erro ao carregar logs: ' . $e->getMessage();
}

include __DIR__ . '/../vision/includes/head.php';
include __DIR__ . '/../vision/includes/header.php';
include __DIR__ . '/../vision/includes/sidebar.php';
?>

<div class="main-content">
    <div class="glass-hero">
        <div class="hero-content">
            <h1><i class="fas fa-list-alt"></i> Logs do Sistema</h1>
            <p>Monitoramento e auditoria do sistema</p>
            <a href="index.php" class="cta-btn">
                <i class="fas fa-arrow-left"></i> Voltar ao Admin
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Estatísticas dos Logs -->
    <div class="stats-grid">
        <div class="video-card stats-card">
            <div class="stats-content">
                <div class="stats-info">
                    <h3>Logs Hoje</h3>
                    <span class="stats-number"><?php echo number_format($today_logs); ?></span>
                </div>
                <div class="stats-icon stats-icon-blue">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
        </div>

        <div class="video-card stats-card">
            <div class="stats-content">
                <div class="stats-info">
                    <h3>Total de Logs</h3>
                    <span class="stats-number"><?php echo number_format($total_logs); ?></span>
                </div>
                <div class="stats-icon stats-icon-purple">
                    <i class="fas fa-list"></i>
                </div>
            </div>
        </div>

        <div class="video-card stats-card">
            <div class="stats-content">
                <div class="stats-info">
                    <h3>Erros</h3>
                    <span class="stats-number"><?php echo number_format($error_logs); ?></span>
                </div>
                <div class="stats-icon stats-icon-red">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
            </div>
        </div>

        <div class="video-card stats-card">
            <div class="stats-content">
                <div class="stats-info">
                    <h3>Avisos</h3>
                    <span class="stats-number"><?php echo number_format($warning_logs); ?></span>
                </div>
                <div class="stats-icon stats-icon-red">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="video-card">
        <h2><i class="fas fa-filter"></i> Filtros</h2>
        
        <form method="GET" class="vision-form">
            <div class="form-grid">
                <div class="form-group">
                    <label for="type">
                        <i class="fas fa-tags"></i> Tipo de Log
                    </label>
                    <select id="type" name="type">
                        <option value="">Todos os tipos</option>
                        <?php foreach ($log_types as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>" 
                                    <?php echo $log_type == $type ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date">
                        <i class="fas fa-calendar"></i> Data
                    </label>
                    <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
                </div>

                <div class="form-group">
                    <label>&nbsp;</label>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="cta-btn">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <a href="logs.php" class="page-btn">
                            <i class="fas fa-times"></i> Limpar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Lista de Logs -->
    <div class="video-card">
        <div class="card-header">
            <h2><i class="fas fa-list"></i> Logs do Sistema (<?php echo number_format($total_logs); ?>)</h2>
        </div>

        <?php if (empty($logs)): ?>
            <div class="alert-warning">
                <i class="fas fa-info-circle"></i>
                <?php echo ($log_type || $date_filter) ? 'Nenhum log encontrado com os critérios de busca.' : 'Nenhum log registrado ainda.'; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-calendar"></i> Data/Hora</th>
                            <th><i class="fas fa-flag"></i> Nível</th>
                            <th><i class="fas fa-tags"></i> Tipo</th>
                            <th><i class="fas fa-user"></i> Usuário</th>
                            <th><i class="fas fa-info-circle"></i> Mensagem</th>
                            <th><i class="fas fa-eye"></i> Detalhes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php 
                                        switch($log['log_level']) {
                                            case 'ERROR': echo 'cancelled'; break;
                                            case 'WARNING': echo 'pending'; break;
                                            case 'INFO': echo 'completed'; break;
                                            default: echo 'in_progress';
                                        }
                                    ?>">
                                        <?php echo htmlspecialchars($log['log_level']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($log['log_type']); ?></td>
                                <td><?php echo htmlspecialchars($log['user_id'] ?? 'Sistema'); ?></td>
                                <td>
                                    <span class="text-primary">
                                        <?php echo htmlspecialchars(substr($log['message'], 0, 80)) . (strlen($log['message']) > 80 ? '...' : ''); ?>
                                    </span>
                                </td>
                                <td>
                                    <button onclick="showLogDetails(<?php echo htmlspecialchars(json_encode($log)); ?>)" 
                                            class="page-btn" title="Ver Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <?php if ($total_pages > 1): ?>
                <div style="text-align: center; margin-top: 30px;">
                    <?php
                    $base_url = "logs.php?";
                    if ($log_type) $base_url .= "type=" . urlencode($log_type) . "&";
                    if ($date_filter) $base_url .= "date=" . urlencode($date_filter) . "&";
                    ?>
                    
                    <?php if ($page > 1): ?>
                        <a href="<?php echo $base_url; ?>page=1" class="page-btn">« Primeira</a>
                        <a href="<?php echo $base_url; ?>page=<?php echo $page - 1; ?>" class="page-btn">‹ Anterior</a>
                    <?php endif; ?>
                    
                    <?php
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <?php if ($i == $page): ?>
                            <span class="cta-btn" style="margin: 0 5px;"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="<?php echo $base_url; ?>page=<?php echo $i; ?>" 
                               class="page-btn" style="margin: 0 5px;"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="<?php echo $base_url; ?>page=<?php echo $page + 1; ?>" class="page-btn">Próxima ›</a>
                        <a href="<?php echo $base_url; ?>page=<?php echo $total_pages; ?>" class="page-btn">Última »</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Informações sobre Logs -->
    <div class="video-card">
        <h2><i class="fas fa-info-circle"></i> Sobre os Logs</h2>
        
        <div class="dashboard-sections">
            <div>
                <h3><i class="fas fa-layer-group"></i> <strong>Níveis de Log</strong></h3>
                <ul style="color: #ddd;">
                    <li><strong>ERROR:</strong> Erros críticos do sistema</li>
                    <li><strong>WARNING:</strong> Avisos importantes</li>
                    <li><strong>INFO:</strong> Informações gerais</li>
                    <li><strong>DEBUG:</strong> Informações de depuração</li>
                </ul>
                
                <h3><i class="fas fa-clock"></i> <strong>Retenção</strong></h3>
                <p>Os logs são mantidos por 90 dias e depois automaticamente removidos.</p>
            </div>
            
            <div>
                <h3><i class="fas fa-tags"></i> <strong>Tipos de Log</strong></h3>
                <ul style="color: #ddd;">
                    <li><strong>AUTH:</strong> Autenticação e autorização</li>
                    <li><strong>USER:</strong> Ações de usuários</li>
                    <li><strong>SYSTEM:</strong> Eventos do sistema</li>
                    <li><strong>ERROR:</strong> Erros e exceções</li>
                    <li><strong>WEBHOOK:</strong> Integrações externas</li>
                </ul>
                
                <h3><i class="fas fa-download"></i> <strong>Exportar</strong></h3>
                <div style="margin-top: 10px;">
                    <button onclick="exportLogs()" class="page-btn">
                        <i class="fas fa-download"></i> Exportar CSV
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalhes do log -->
<div id="logModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-info-circle"></i> Detalhes do Log</h3>
            <button type="button" onclick="closeLogModal()" class="close-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div id="logDetails">
            <!-- Conteúdo será preenchido via JavaScript -->
        </div>
    </div>
</div>

<script>
function showLogDetails(log) {
    const modal = document.getElementById('logModal');
    const details = document.getElementById('logDetails');
    
    details.innerHTML = `
        <div class="form-group">
            <label><strong>Data/Hora:</strong></label>
            <p>${new Date(log.created_at).toLocaleString('pt-BR')}</p>
        </div>
        <div class="form-group">
            <label><strong>Nível:</strong></label>
            <p><span class="status-badge">${log.log_level}</span></p>
        </div>
        <div class="form-group">
            <label><strong>Tipo:</strong></label>
            <p>${log.log_type}</p>
        </div>
        <div class="form-group">
            <label><strong>Usuário ID:</strong></label>
            <p>${log.user_id || 'Sistema'}</p>
        </div>
        <div class="form-group">
            <label><strong>Mensagem:</strong></label>
            <p>${log.message}</p>
        </div>
        <div class="form-group">
            <label><strong>Dados Adicionais:</strong></label>
            <textarea readonly rows="8" style="width: 100%; font-family: monospace; font-size: 0.8rem;">${log.additional_data || 'Nenhum dado adicional'}</textarea>
        </div>
        <div class="form-group">
            <label><strong>IP Address:</strong></label>
            <p>${log.ip_address || 'N/A'}</p>
        </div>
        <div class="form-group">
            <label><strong>User Agent:</strong></label>
            <p style="font-size: 0.8rem; word-break: break-all;">${log.user_agent || 'N/A'}</p>
        </div>
    `;
    
    modal.style.display = 'flex';
}

function closeLogModal() {
    document.getElementById('logModal').style.display = 'none';
}

function exportLogs() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.location.href = 'logs.php?' + params.toString();
}

// Fechar modal clicando fora
document.getElementById('logModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLogModal();
    }
});
</script>

<?php include __DIR__ . '/../vision/includes/footer.php'; ?>