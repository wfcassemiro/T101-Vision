<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/dash_database.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    header('Location: /login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$page_title = 'Visualizar Fatura - Dash-T101';
$page_description = 'Detalhes completos da fatura';
$user_id = $_SESSION['user_id'];
$invoice_id = $_GET['id'] ?? null;

if (!$invoice_id) {
    header('Location: invoices.php');
    exit;
}

// Obter detalhes da fatura
try {
    $stmt = $pdo->prepare("
        SELECT i.*, 
               COALESCE(c.company_name, i.client_name) as company_name,
               COALESCE(c.contact_name, '') as contact_name,
               COALESCE(c.contact_email, '') as client_email,
               COALESCE(c.vat_number, '') as client_vat_number,
               COALESCE(c.address_line1, '') as client_address_line1,
               COALESCE(c.address_line2, '') as client_address_line2,
               COALESCE(c.address_line3, '') as client_address_line3,
               COALESCE(c.phone, '') as client_phone
        FROM dash_invoices i 
        LEFT JOIN dash_clients c ON i.client_id = c.id 
        WHERE i.id = ? AND i.user_id = ?
    ");
    $stmt->execute([$invoice_id, $user_id]);
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$invoice) {
        header('Location: invoices.php?error=Fatura não encontrada');
        exit;
    }
    
    // Obter itens da fatura
    $stmt = $pdo->prepare("SELECT * FROM dash_invoice_items WHERE invoice_id = ? ORDER BY id ASC");
    $stmt->execute([$invoice_id]);
    $invoice_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    header('Location: invoices.php?error=Erro ao carregar fatura');
    exit;
}

include __DIR__ . '/../vision/includes/head.php';
?>

<?php include __DIR__ . '/../vision/includes/header.php'; ?>

<?php include __DIR__ . '/../vision/includes/sidebar.php'; ?>

<main class="main-content">
    <!-- Breadcrumb -->
    <nav style="background: var(--glass-bg); backdrop-filter: blur(20px); border: 1px solid var(--glass-border); border-radius: 12px; padding: 15px; margin-bottom: 30px;">
        <ol style="display: flex; align-items: center; gap: 10px; font-size: 0.9rem; color: #ddd; list-style: none; margin: 0; padding: 0;">
            <li><a href="../index.php" style="color: var(--brand-purple); text-decoration: none;">
                <i class="fas fa-home"></i> Início</a></li>
            <li><i class="fas fa-chevron-right" style="font-size: 0.8rem;"></i></li>
            <li><a href="index.php" style="color: var(--brand-purple); text-decoration: none;">
                <i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><i class="fas fa-chevron-right" style="font-size: 0.8rem;"></i></li>
            <li><a href="invoices.php" style="color: var(--brand-purple); text-decoration: none;">
                <i class="fas fa-file-invoice"></i> Faturas</a></li>
            <li><i class="fas fa-chevron-right" style="font-size: 0.8rem;"></i></li>
            <li style="color: #fff; font-weight: 500;">Fatura #<?php echo htmlspecialchars($invoice['invoice_number']); ?></li>
        </ol>
    </nav>

    <!-- Header da Fatura -->
    <section class="glass-hero">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1><i class="fas fa-file-invoice" style="margin-right: 10px;"></i>Fatura #<?php echo htmlspecialchars($invoice['invoice_number']); ?></h1>
                <p>Visualize e gerencie os detalhes completos desta fatura</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <span class="tag <?php 
                    switch($invoice['status']) {
                        case 'paid': echo 'style="background: rgba(46, 204, 113, 0.25); color: #2ecc71;"'; break;
                        case 'sent': echo 'style="background: rgba(52, 152, 219, 0.25); color: #3498db;"'; break;
                        case 'draft': echo 'style="background: rgba(241, 196, 15, 0.25); color: #f1c40f;"'; break;
                        case 'overdue': echo 'style="background: rgba(231, 76, 60, 0.25); color: #e74c3c;"'; break;
                        default: echo 'style="background: rgba(149, 165, 166, 0.25); color: #95a5a6;"';
                    }
                ?>" style="font-size: 1rem; padding: 8px 16px;">
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
            </div>
        </div>
    </section>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px; margin-bottom: 40px;">
        <!-- Detalhes da Fatura -->
        <div>
            <!-- Informações do Cliente -->
            <div class="video-card" style="margin-bottom: 30px;">
                <div class="video-info">
                    <h3 style="font-size: 1.3rem; font-weight: bold; margin-bottom: 20px; color: #fff;">
                        <i class="fas fa-building" style="margin-right: 10px; color: var(--brand-purple);"></i>
                        Informações do Cliente
                    </h3>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                        <div>
                            <strong style="color: #ccc;">Empresa:</strong>
                            <p style="color: #fff; font-size: 1.1rem; font-weight: 500; margin: 5px 0;">
                                <?php echo htmlspecialchars($invoice['company_name']); ?>
                            </p>
                        </div>
                        
                        <?php if ($invoice['contact_name']): ?>
                        <div>
                            <strong style="color: #ccc;">Contato:</strong>
                            <p style="color: #fff; margin: 5px 0;">
                                <?php echo htmlspecialchars($invoice['contact_name']); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($invoice['client_email']): ?>
                        <div>
                            <strong style="color: #ccc;">Email:</strong>
                            <p style="color: var(--brand-purple); margin: 5px 0;">
                                <a href="mailto:<?php echo htmlspecialchars($invoice['client_email']); ?>" style="color: var(--brand-purple); text-decoration: none;">
                                    <?php echo htmlspecialchars($invoice['client_email']); ?>
                                </a>
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($invoice['client_vat_number']): ?>
                        <div>
                            <strong style="color: #ccc;">CNPJ/CPF:</strong>
                            <p style="color: #fff; margin: 5px 0;">
                                <?php echo htmlspecialchars($invoice['client_vat_number']); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($invoice['client_address_line1']): ?>
                        <div style="grid-column: span 2;">
                            <strong style="color: #ccc;">Endereço:</strong>
                            <div style="color: #fff; margin: 5px 0;">
                                <p><?php echo htmlspecialchars($invoice['client_address_line1']); ?></p>
                                <?php if ($invoice['client_address_line2']): ?>
                                    <p><?php echo htmlspecialchars($invoice['client_address_line2']); ?></p>
                                <?php endif; ?>
                                <?php if ($invoice['client_address_line3']): ?>
                                    <p><?php echo htmlspecialchars($invoice['client_address_line3']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Itens da Fatura -->
            <div class="vision-table">
                <div style="padding: 20px; border-bottom: 1px solid var(--glass-border);">
                    <h3 style="font-size: 1.3rem; font-weight: bold; margin: 0; color: #fff;">
                        <i class="fas fa-list" style="margin-right: 10px; color: var(--brand-purple);"></i>
                        Itens da Fatura
                    </h3>
                </div>
                
                <?php if (empty($invoice_items)): ?>
                    <div style="text-align: center; padding: 40px;">
                        <i class="fas fa-inbox" style="font-size: 3rem; color: #666; margin-bottom: 15px;"></i>
                        <h3 style="color: #fff; margin-bottom: 10px;">Nenhum item encontrado</h3>
                        <p style="color: #ccc;">Esta fatura não possui itens cadastrados.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Descrição</th>
                                <th>Qtd</th>
                                <th>Valor Unit.</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoice_items as $item): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <p style="font-weight: 600; color: #fff; margin-bottom: 5px;">
                                                <?php echo htmlspecialchars($item['description']); ?>
                                            </p>
                                            <?php if ($item['details']): ?>
                                                <p style="color: #ccc; font-size: 0.9rem;">
                                                    <?php echo htmlspecialchars($item['details']); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td style="text-align: center; color: #fff; font-weight: 500;">
                                        <?php echo number_format($item['quantity'], 2); ?>
                                    </td>
                                    <td style="text-align: right; color: #fff; font-weight: 500;">
                                        <?php echo formatCurrency($item['unit_price'], $invoice['currency']); ?>
                                    </td>
                                    <td style="text-align: right; color: var(--brand-purple); font-weight: 600;">
                                        <?php echo formatCurrency($item['total_price'], $invoice['currency']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="border-top: 2px solid var(--brand-purple);">
                                <td colspan="3" style="text-align: right; font-weight: 600; color: #fff; font-size: 1.1rem;">
                                    Total Geral:
                                </td>
                                <td style="text-align: right; font-weight: 600; color: var(--brand-purple); font-size: 1.3rem;">
                                    <?php echo formatCurrency($invoice['total_amount'], $invoice['currency']); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Sidebar - Ações e Detalhes -->
        <div>
            <!-- Ações da Fatura -->
            <div class="video-card" style="margin-bottom: 30px;">
                <div class="video-info">
                    <h3 style="font-size: 1.3rem; font-weight: bold; margin-bottom: 20px; color: #fff;">
                        <i class="fas fa-cogs" style="margin-right: 10px; color: var(--brand-purple);"></i>
                        Ações
                    </h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <a href="invoices.php?edit=<?php echo $invoice['id']; ?>" class="cta-btn" style="text-align: center;">
                            <i class="fas fa-edit" style="margin-right: 8px;"></i>Editar Fatura
                        </a>
                        
                        <?php if ($invoice['status'] !== 'paid'): ?>
                        <form method="POST" action="invoices.php" style="margin: 0;">
                            <input type="hidden" name="action" value="mark_as_paid">
                            <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
                            <button type="submit" class="cta-btn" style="width: 100%; background: #27ae60;">
                                <i class="fas fa-check-circle" style="margin-right: 8px;"></i>Marcar como Paga
                            </button>
                        </form>
                        <?php endif; ?>
                        
                        <button onclick="window.print()" class="cta-btn" style="background: #3498db;">
                            <i class="fas fa-print" style="margin-right: 8px;"></i>Imprimir Fatura
                        </button>
                        
                        <a href="invoices.php" class="cta-btn" style="background: rgba(255,255,255,0.1);">
                            <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>Voltar à Lista
                        </a>
                    </div>
                </div>
            </div>

            <!-- Detalhes da Fatura -->
            <div class="video-card">
                <div class="video-info">
                    <h3 style="font-size: 1.3rem; font-weight: bold; margin-bottom: 20px; color: #fff;">
                        <i class="fas fa-info-circle" style="margin-right: 10px; color: var(--brand-purple);"></i>
                        Detalhes da Fatura
                    </h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <div>
                            <strong style="color: #ccc; display: flex; align-items: center; margin-bottom: 5px;">
                                <i class="fas fa-hashtag" style="margin-right: 8px; color: var(--brand-purple);"></i>
                                Número:
                            </strong>
                            <span style="color: #fff; font-weight: 600;">#<?php echo htmlspecialchars($invoice['invoice_number']); ?></span>
                        </div>
                        
                        <div>
                            <strong style="color: #ccc; display: flex; align-items: center; margin-bottom: 5px;">
                                <i class="fas fa-calendar" style="margin-right: 8px; color: var(--brand-purple);"></i>
                                Data de Emissão:
                            </strong>
                            <span style="color: #fff;">
                                <?php echo date('d/m/Y', strtotime($invoice['issue_date'])); ?>
                            </span>
                        </div>
                        
                        <div>
                            <strong style="color: #ccc; display: flex; align-items: center; margin-bottom: 5px;">
                                <i class="fas fa-calendar-times" style="margin-right: 8px; color: var(--brand-purple);"></i>
                                Data de Vencimento:
                            </strong>
                            <span style="color: #fff;">
                                <?php echo date('d/m/Y', strtotime($invoice['due_date'])); ?>
                            </span>
                        </div>
                        
                        <div>
                            <strong style="color: #ccc; display: flex; align-items: center; margin-bottom: 5px;">
                                <i class="fas fa-coins" style="margin-right: 8px; color: var(--brand-purple);"></i>
                                Moeda:
                            </strong>
                            <span class="tag"><?php echo htmlspecialchars($invoice['currency']); ?></span>
                        </div>
                        
                        <?php if ($invoice['notes']): ?>
                        <div>
                            <strong style="color: #ccc; display: flex; align-items: center; margin-bottom: 5px;">
                                <i class="fas fa-sticky-note" style="margin-right: 8px; color: var(--brand-purple);"></i>
                                Observações:
                            </strong>
                            <p style="color: #ddd; font-size: 0.9rem; line-height: 1.4;">
                                <?php echo nl2br(htmlspecialchars($invoice['notes'])); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style media="print">
    .glass-header, .glass-sidebar, .main-content nav, .video-card:last-child {
        display: none !important;
    }
    
    .main-content {
        margin: 0 !important;
        padding: 20px !important;
    }
    
    body {
        background: white !important;
        color: black !important;
    }
    
    .video-card, .vision-table {
        background: white !important;
        border: 1px solid #ccc !important;
        box-shadow: none !important;
    }
</style>

<?php include __DIR__ . '/../vision/includes/footer.php'; ?>