<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/dash_database.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    header('Location: /login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$page_title = 'Dashboard - Dash-T101';
$page_description = 'Painel de controle de projetos e negócios';
$user_id = $_SESSION['user_id'];

// Obter estatísticas do dashboard
$stats = getDashboardStats($user_id);
$recent_projects = getRecentProjects($user_id, 5);
$recent_invoices = getRecentInvoices($user_id, 5);

// Obter configurações do usuário
$user_settings = getUserSettings($user_id);

include __DIR__ . '/../vision/includes/head.php';
include __DIR__ . '/../vision/includes/header.php';
include __DIR__ . '/../vision/includes/sidebar.php';
?>

<div class="main-content">
    <!-- Hero Section -->
    <div class="glass-hero">
        <div class="hero-content">
            <h1><i class="fas fa-chart-line"></i> Dashboard - Dash-T101</h1>
            <p>Bem-vindo ao seu painel de controle de projetos e negócios. Gerencie suas operações de forma eficiente.</p>
        </div>
    </div>

    <!-- Estatísticas Cards -->
    <div class="stats-grid">
        <div class="video-card stats-card">
            <div class="stats-content">
                <div class="stats-info">
                    <h3>Total de Clientes</h3>
                    <span class="stats-number"><?php echo number_format($stats['total_clients']); ?></span>
                </div>
                <div class="stats-icon stats-icon-blue">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        <div class="video-card stats-card">
            <div class="stats-content">
                <div class="stats-info">
                    <h3>Projetos Ativos</h3>
                    <span class="stats-number"><?php echo number_format($stats['active_projects']); ?></span>
                    <span class="stats-subtitle">de <?php echo number_format($stats['total_projects']); ?> total</span>
                </div>
                <div class="stats-icon stats-icon-purple">
                    <i class="fas fa-project-diagram"></i>
                </div>
            </div>
        </div>

        <div class="video-card stats-card">
            <div class="stats-content">
                <div class="stats-info">
                    <h3>Receita Total</h3>
                    <span class="stats-number">
                        <?php echo formatCurrency($stats['total_revenue'], $user_settings['default_currency']); ?>
                    </span>
                </div>
                <div class="stats-icon stats-icon-green">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>

        <div class="video-card stats-card">
            <div class="stats-content">
                <div class="stats-info">
                    <h3>Faturas Pendentes</h3>
                    <span class="stats-number"><?php echo number_format($stats['pending_invoices']); ?></span>
                    <span class="stats-subtitle">
                        <?php echo formatCurrency($stats['pending_amount'], $user_settings['default_currency']); ?>
                    </span>
                </div>
                <div class="stats-icon stats-icon-red">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="video-card">
        <h2><i class="fas fa-bolt"></i> Ações Rápidas</h2>
        
        <div class="quick-actions-grid">
            <a href="projects.php" class="quick-action-card">
                <div class="quick-action-icon quick-action-icon-purple">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h3>Novo Projeto</h3>
                <p>Criar um novo projeto de tradução</p>
            </a>

            <a href="clients.php" class="quick-action-card">
                <div class="quick-action-icon quick-action-icon-blue">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h3>Novo Cliente</h3>
                <p>Adicionar um novo cliente</p>
            </a>

            <a href="invoices.php" class="quick-action-card">
                <div class="quick-action-icon quick-action-icon-green">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <h3>Nova Fatura</h3>
                <p>Gerar uma nova fatura</p>
            </a>

            <a href="../videoteca.php" class="quick-action-card">
                <div class="quick-action-icon quick-action-icon-red">
                    <i class="fas fa-video"></i>
                </div>
                <h3>Videoteca</h3>
                <p>Acessar palestras</p>
            </a>
        </div>
    </div>

    <div class="dashboard-sections">
        <!-- Projetos Recentes -->
        <div class="video-card">
            <div class="card-header">
                <h2><i class="fas fa-history"></i> Projetos Recentes</h2>
                <a href="projects.php" class="page-btn">Ver Todos</a>
            </div>
            
            <?php if (empty($recent_projects)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3>Nenhum projeto ainda</h3>
                    <p>Comece criando seu primeiro projeto</p>
                    <a href="projects.php" class="cta-btn">
                        <i class="fas fa-plus"></i> Criar Projeto
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Projeto</th>
                                <th>Status</th>
                                <th>Valor</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_projects as $project): ?>
                                <tr>
                                    <td>
                                        <div class="project-info">
                                            <span class="text-primary"><?php echo htmlspecialchars($project['project_name']); ?></span>
                                            <span class="project-client"><?php echo htmlspecialchars($project['client_name'] ?? 'Cliente não informado'); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $project['status']; ?>">
                                            <?php 
                                            $status_labels = [
                                                'pending' => 'Pendente',
                                                'in_progress' => 'Em Andamento',
                                                'completed' => 'Concluído'
                                            ];
                                            echo $status_labels[$project['status']] ?? $project['status'];
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatCurrency($project['total_amount'], $user_settings['default_currency']); ?></td>
                                    <td>
                                        <a href="projects.php?edit=<?php echo $project['id']; ?>" class="page-btn" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Faturas Recentes -->
        <div class="video-card">
            <div class="card-header">
                <h2><i class="fas fa-file-invoice-dollar"></i> Faturas Recentes</h2>
                <a href="invoices.php" class="page-btn">Ver Todas</a>
            </div>
            
            <?php if (empty($recent_invoices)): ?>
                <div class="empty-state">
                    <i class="fas fa-file-invoice"></i>
                    <h3>Nenhuma fatura ainda</h3>
                    <p>Crie sua primeira fatura</p>
                    <a href="invoices.php" class="cta-btn">
                        <i class="fas fa-plus"></i> Criar Fatura
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Cliente</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_invoices as $invoice): ?>
                                <tr>
                                    <td>
                                        <span class="text-primary">#<?php echo htmlspecialchars($invoice['invoice_number']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($invoice['client_name']); ?></td>
                                    <td><?php echo formatCurrency($invoice['total_amount'], $invoice['currency']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $invoice['status']; ?>">
                                            <?php 
                                            $status_labels = [
                                                'draft' => 'Rascunho',
                                                'sent' => 'Enviada',
                                                'paid' => 'Paga',
                                                'overdue' => 'Vencida'
                                            ];
                                            echo $status_labels[$invoice['status']] ?? $invoice['status'];
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="view_invoice.php?id=<?php echo $invoice['id']; ?>" class="page-btn" title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../vision/includes/footer.php'; ?>