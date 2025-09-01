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
?>

<?php include __DIR__ . '/../vision/includes/header.php'; ?>

<?php include __DIR__ . '/../vision/includes/sidebar.php'; ?>

<main class="main-content">
    <!-- Hero Section -->
    <section class="glass-hero">
        <h1><i class="fas fa-chart-line" style="margin-right: 10px;"></i>Dashboard - Dash-T101</h1>
        <p>Bem-vindo ao seu painel de controle de projetos e negócios. Gerencie suas operações de forma eficiente.</p>
    </section>

    <!-- Estatísticas Cards -->
    <div class="video-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); margin-bottom: 40px;">
        <div class="video-card fade-item">
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 25px;">
                <div>
                    <p style="color: #ccc; font-size: 0.9rem; margin-bottom: 8px;">Total de Clientes</p>
                    <p style="color: #fff; font-size: 2rem; font-weight: bold;"><?php echo number_format($stats['total_clients']); ?></p>
                </div>
                <div style="background: #3498db; border-radius: 50%; padding: 15px;">
                    <i class="fas fa-users" style="color: white; font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>

        <div class="video-card fade-item">
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 25px;">
                <div>
                    <p style="color: #ccc; font-size: 0.9rem; margin-bottom: 8px;">Projetos Ativos</p>
                    <p style="color: #fff; font-size: 2rem; font-weight: bold;"><?php echo number_format($stats['active_projects']); ?></p>
                    <p style="color: #999; font-size: 0.8rem;">de <?php echo number_format($stats['total_projects']); ?> total</p>
                </div>
                <div style="background: var(--brand-purple); border-radius: 50%; padding: 15px;">
                    <i class="fas fa-project-diagram" style="color: white; font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>

        <div class="video-card fade-item">
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 25px;">
                <div>
                    <p style="color: #ccc; font-size: 0.9rem; margin-bottom: 8px;">Receita Total</p>
                    <p style="color: #fff; font-size: 2rem; font-weight: bold;">
                        <?php echo formatCurrency($stats['total_revenue'], $user_settings['default_currency']); ?>
                    </p>
                </div>
                <div style="background: #27ae60; border-radius: 50%; padding: 15px;">
                    <i class="fas fa-dollar-sign" style="color: white; font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>

        <div class="video-card fade-item">
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 25px;">
                <div>
                    <p style="color: #ccc; font-size: 0.9rem; margin-bottom: 8px;">Faturas Pendentes</p>
                    <p style="color: #fff; font-size: 2rem; font-weight: bold;"><?php echo number_format($stats['pending_invoices']); ?></p>
                    <p style="color: #999; font-size: 0.8rem;">
                        <?php echo formatCurrency($stats['pending_amount'], $user_settings['default_currency']); ?>
                    </p>
                </div>
                <div style="background: #e74c3c; border-radius: 50%; padding: 15px;">
                    <i class="fas fa-file-invoice-dollar" style="color: white; font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <section style="margin-bottom: 40px;">
        <h2 style="font-size: 1.8rem; font-weight: bold; margin-bottom: 25px; color: #fff;">
            <i class="fas fa-bolt" style="margin-right: 10px; color: var(--brand-purple);"></i>
            Ações Rápidas
        </h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <a href="/projects.php" class="video-card fade-item" style="text-decoration: none; cursor: pointer; transition: all 0.3s ease;"
               onmouseover="this.style.transform='translateY(-5px) scale(1.02)'"
               onmouseout="this.style.transform='translateY(0) scale(1)'">
                <div style="text-align: center; padding: 25px;">
                    <div style="font-size: 3rem; margin-bottom: 15px; color: var(--brand-purple);">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <h3 style="color: #fff; font-weight: 600; margin-bottom: 8px;">Novo Projeto</h3>
                    <p style="color: #ccc; font-size: 0.9rem;">Criar um novo projeto de tradução</p>
                </div>
            </a>

            <a href="clients.php" class="video-card fade-item" style="text-decoration: none; cursor: pointer; transition: all 0.3s ease;"
               onmouseover="this.style.transform='translateY(-5px) scale(1.02)'"
               onmouseout="this.style.transform='translateY(0) scale(1)'">
                <div style="text-align: center; padding: 25px;">
                    <div style="font-size: 3rem; margin-bottom: 15px; color: #3498db;">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h3 style="color: #fff; font-weight: 600; margin-bottom: 8px;">Novo Cliente</h3>
                    <p style="color: #ccc; font-size: 0.9rem;">Adicionar um novo cliente</p>
                </div>
            </a>

            <a href="invoices.php" class="video-card fade-item" style="text-decoration: none; cursor: pointer; transition: all 0.3s ease;"
               onmouseover="this.style.transform='translateY(-5px) scale(1.02)'"
               onmouseout="this.style.transform='translateY(0) scale(1)'">
                <div style="text-align: center; padding: 25px;">
                    <div style="font-size: 3rem; margin-bottom: 15px; color: #27ae60;">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h3 style="color: #fff; font-weight: 600; margin-bottom: 8px;">Nova Fatura</h3>
                    <p style="color: #ccc; font-size: 0.9rem;">Gerar uma nova fatura</p>
                </div>
            </a>

            <a href="/videoteca.php" class="video-card fade-item" style="text-decoration: none; cursor: pointer; transition: all 0.3s ease;"
               onmouseover="this.style.transform='translateY(-5px) scale(1.02)'"
               onmouseout="this.style.transform='translateY(0) scale(1)'">
                <div style="text-align: center; padding: 25px;">
                    <div style="font-size: 3rem; margin-bottom: 15px; color: #e74c3c;">
                        <i class="fas fa-video"></i>
                    </div>
                    <h3 style="color: #fff; font-weight: 600; margin-bottom: 8px;">Videoteca</h3>
                    <p style="color: #ccc; font-size: 0.9rem;">Acessar palestras</p>
                </div>
            </a>
        </div>
    </section>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
        <!-- Projetos Recentes -->
        <div>
            <h2 style="font-size: 1.8rem; font-weight: bold; margin-bottom: 25px; color: #fff;">
                <i class="fas fa-history" style="margin-right: 10px; color: var(--brand-purple);"></i>
                Projetos Recentes
            </h2>
            
            <div class="vision-table">
                <?php if (empty($recent_projects)): ?>
                    <div style="text-align: center; padding: 40px;">
                        <i class="fas fa-folder-open" style="font-size: 3rem; color: #666; margin-bottom: 15px;"></i>
                        <h3 style="color: #fff; margin-bottom: 10px;">Nenhum projeto ainda</h3>
                        <p style="color: #ccc; margin-bottom: 20px;">Comece criando seu primeiro projeto</p>
                        <a href="/projects.php" class="cta-btn" style="font-size: 0.9rem;">
                            <i class="fas fa-plus"></i> Criar Projeto
                        </a>
                    </div>
                <?php else: ?>
                    <table>
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
                                        <div>
                                            <p style="font-weight: 600; color: #fff;"><?php echo htmlspecialchars($project['project_name']); ?></p>
                                            <p style="font-size: 0.8rem; color: #ccc;"><?php echo htmlspecialchars($project['client_name'] ?? 'Cliente não informado'); ?></p>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="tag <?php 
                                            switch($project['status']) {
                                                case 'completed': echo 'style="background: rgba(46, 204, 113, 0.25); color: #2ecc71;"'; break;
                                                case 'in_progress': echo 'style="background: rgba(52, 152, 219, 0.25); color: #3498db;"'; break;
                                                case 'pending': echo 'style="background: rgba(241, 196, 15, 0.25); color: #f1c40f;"'; break;
                                                default: echo 'style="background: rgba(149, 165, 166, 0.25); color: #95a5a6;"';
                                            }
                                        ?>">
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
                                    <td style="color: #fff; font-weight: 500;">
                                        <?php echo formatCurrency($project['total_amount'], $user_settings['default_currency']); ?>
                                    </td>
                                    <td>
                                        <a href="projects.php?edit=<?php echo $project['id']; ?>" 
                                           class="cta-btn" style="font-size: 0.8rem; padding: 4px 8px;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div style="text-align: center; padding: 20px; border-top: 1px solid var(--glass-border);">
                        <a href="projects.php" class="cta-btn" style="font-size: 0.9rem;">
                            <i class="fas fa-list"></i> Ver Todos os Projetos
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Faturas Recentes -->
        <div>
            <h2 style="font-size: 1.8rem; font-weight: bold; margin-bottom: 25px; color: #fff;">
                <i class="fas fa-file-invoice-dollar" style="margin-right: 10px; color: var(--brand-purple);"></i>
                Faturas Recentes
            </h2>
            
            <div class="vision-table">
                <?php if (empty($recent_invoices)): ?>
                    <div style="text-align: center; padding: 40px;">
                        <i class="fas fa-file-invoice" style="font-size: 3rem; color: #666; margin-bottom: 15px;"></i>
                        <h3 style="color: #fff; margin-bottom: 10px;">Nenhuma fatura ainda</h3>
                        <p style="color: #ccc; margin-bottom: 20px;">Crie sua primeira fatura</p>
                        <a href="invoices.php" class="cta-btn" style="font-size: 0.9rem;">
                            <i class="fas fa-plus"></i> Criar Fatura
                        </a>
                    </div>
                <?php else: ?>
                    <table>
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
                                    <td style="font-weight: 600; color: #fff;">
                                        #<?php echo htmlspecialchars($invoice['invoice_number']); ?>
                                    </td>
                                    <td style="color: #ccc;">
                                        <?php echo htmlspecialchars($invoice['client_name']); ?>
                                    </td>
                                    <td style="color: #fff; font-weight: 500;">
                                        <?php echo formatCurrency($invoice['total_amount'], $invoice['currency']); ?>
                                    </td>
                                    <td>
                                        <span class="tag <?php 
                                            switch($invoice['status']) {
                                                case 'paid': echo 'style="background: rgba(46, 204, 113, 0.25); color: #2ecc71;"'; break;
                                                case 'sent': echo 'style="background: rgba(52, 152, 219, 0.25); color: #3498db;"'; break;
                                                case 'draft': echo 'style="background: rgba(241, 196, 15, 0.25); color: #f1c40f;"'; break;
                                                case 'overdue': echo 'style="background: rgba(231, 76, 60, 0.25); color: #e74c3c;"'; break;
                                                default: echo 'style="background: rgba(149, 165, 166, 0.25); color: #95a5a6;"';
                                            }
                                        ?>">
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
                                        <a href="view_invoice.php?id=<?php echo $invoice['id']; ?>" 
                                           class="cta-btn" style="font-size: 0.8rem; padding: 4px 8px;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div style="text-align: center; padding: 20px; border-top: 1px solid var(--glass-border);">
                        <a href="invoices.php" class="cta-btn" style="font-size: 0.9rem;">
                            <i class="fas fa-list"></i> Ver Todas as Faturas
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../vision/includes/footer.php'; ?>
                </div>
                <div class="bg-green-600 p-3 rounded-full">
                    <i class="fas fa-project-diagram text-white"></i>
                </div>
            </div>
        </div>

        <!-- CARD RECEITA TOTAL POR MOEDA CORRIGIDO -->
        <div class="bg-gray-800 rounded-lg p-6 lg:col-span-2 flex flex-col justify-center">
            <div class="flex items-center justify-between h-full">
                <div>
                    <p class="text-gray-400 text-sm mb-2">Receita Total por Moeda</p>
                </div>
                <div class="flex flex-col items-end flex-1">
                    <?php foreach ($dash_config['currencies'] as $currency_code): ?>
                        <p class="text-xl font-bold text-white text-right">
                            <?php echo formatCurrency($stats['total_revenue_' . $currency_code], $currency_code); ?>
                        </p>
                    <?php endforeach; ?>
                </div>
                <div class="bg-purple-600 p-3 rounded-full self-start ml-4">
                    <i class="fas fa-dollar-sign text-white"></i>
                </div>
            </div>
        </div>
        <!-- FIM DO CARD RECEITA TOTAL POR MOEDA -->

        <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Alertas</p>
                    <p class="text-2xl font-bold text-white"><?php echo $stats['upcoming_deadlines'] + $stats['overdue_invoices']; ?></p>
                    <p class="text-xs text-gray-500"><?php echo $stats['upcoming_deadlines']; ?> prazos + <?php echo $stats['overdue_invoices']; ?> vencidas</p>
                </div>
                <div class="bg-red-600 p-3 rounded-full">
                    <i class="fas fa-exclamation-triangle text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <a href="clients.php" class="bg-gray-800 hover:bg-gray-700 rounded-lg p-6 transition-colors">
            <div class="flex items-center">
                <div class="bg-blue-600 p-3 rounded-full mr-4">
                    <i class="fas fa-users text-white"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white">Gerenciar Clientes</h3>
                    <p class="text-gray-400 text-sm">Adicionar e editar informações de clientes</p>
                </div>
            </div>
        </a>

        <a href="projects.php" class="bg-gray-800 hover:bg-gray-700 rounded-lg p-6 transition-colors">
            <div class="flex items-center">
                <div class="bg-green-600 p-3 rounded-full mr-4">
                    <i class="fas fa-project-diagram text-white"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white">Gerenciar Projetos</h3>
                    <p class="text-gray-400 text-sm">Criar e acompanhar projetos de tradução</p>
                </div>
            </div>
        </a>

        <a href="invoices.php" class="bg-gray-800 hover:bg-gray-700 rounded-lg p-6 transition-colors">
            <div class="flex items-center">
                <div class="bg-purple-600 p-3 rounded-full mr-4">
                    <i class="fas fa-file-invoice text-white"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white">Gerenciar Faturas</h3>
                    <p class="text-gray-400 text-sm">Criar faturas e controlar pagamentos</p>
                </div>
            </div>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-white">Projetos Recentes</h2>
                <a href="projects.php" class="text-purple-400 hover:text-purple-300 text-sm">Ver todos</a>
            </div>
            
            <?php if (empty($recent_projects)): ?>
                <p class="text-gray-400 text-center py-8">Nenhum projeto encontrado</p>
            <?php else: ?>
                <div class="space-y-4">
                <?php foreach ($recent_projects as $project): ?>
                    <div class="border-l-4 border-purple-600 bg-gray-700 p-4 rounded-r-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-white"><?php echo htmlspecialchars($project['project_name']); ?></h3>
                                <p class="text-sm text-gray-400"><?php echo htmlspecialchars($project['company_name']); ?></p>
                                <p class="text-xs text-gray-500">
                                    <?php echo $dash_config['languages'][$project['source_language']] ?? $project['source_language']; ?> → <?php echo $dash_config['languages'][$project['target_language']] ?? $project['target_language']; ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="inline-block px-2 py-1 text-xs rounded-full
                                <?php echo getStatusColor($project['status'], 'project'); ?>">
                                    <?php echo getStatusLabel($project['status'], 'project'); ?>
                                </span>
                                <?php if ($project['deadline']): ?>
                                    <p class="text-xs text-gray-400 mt-1">
                                        Prazo: <?php echo date('d/m/Y', strtotime($project['deadline'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-white">Faturas Recentes</h2>
                <a href="invoices.php" class="text-purple-400 hover:text-purple-300 text-sm">Ver todas</a>
            </div>
            
            <?php if (empty($recent_invoices)): ?>
                <p class="text-gray-400 text-center py-8">Nenhuma fatura encontrada</p>
            <?php else: ?>
                <div class="space-y-4">
                <?php foreach ($recent_invoices as $invoice): ?>
                    <div class="border-l-4 border-green-600 bg-gray-700 p-4 rounded-r-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-white"><?php echo htmlspecialchars($invoice['invoice_number']); ?></h3>
                                <p class="text-sm text-gray-400"><?php echo htmlspecialchars($invoice['company_name']); ?></p>
                                <p class="text-xs text-gray-500">
                                    Emitida em <?php echo date('d/m/Y', strtotime($invoice['invoice_date'])); ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-white"><?php echo formatCurrency($invoice['total_amount'], $invoice['currency']); ?></p>
                                <span class="inline-block px-2 py-1 text-xs rounded-full
                                <?php 
                                switch($invoice['status']) {
                                    case 'paid': echo 'bg-green-600 text-white'; break;
                                    case 'sent': echo 'bg-blue-600 text-white'; break;
                                    case 'overdue': echo 'bg-red-600 text-white'; break;
                                    case 'draft': echo 'bg-gray-600 text-white'; break;
                                    default: echo 'bg-gray-600 text-white';
                                }
                                ?>">
                                    <?php 
                                    $status_labels = [
                                        'draft' => 'Rascunho',
                                        'sent' => 'Enviada',
                                        'paid' => 'Paga',
                                        'overdue' => 'Vencida',
                                        'cancelled' => 'Cancelada'
                                    ];
                                    echo $status_labels[$invoice['status']] ?? $invoice['status'];
                                    ?>
                                </span>
                                <?php if ($invoice['due_date']): ?>
                                    <p class="text-xs text-gray-400 mt-1">
                                        Vence em <?php echo date('d/m/Y', strtotime($invoice['due_date'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>