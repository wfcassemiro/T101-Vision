<?php
session_start();
require_once 'config/database.php';
require_once 'config/dash_database.php';

// Page settings
$page_title = 'Projetos';
$page_description = 'Crie e gerencie seus projetos.';
$active_page = 'projects';
$hide_top_menu = true;

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_project':
                try {
                    $word_count = intval($_POST['word_count'] ?? 0);
                    $character_count = intval($_POST['character_count'] ?? 0);
                    $rate_per_word = floatval($_POST['rate_per_word'] ?? 0);
                    $rate_per_character = floatval($_POST['rate_per_character'] ?? 0);
                    $total_amount = calculateProjectTotal($word_count, $character_count, $rate_per_word, $rate_per_character);
                    
                    $stmt = $pdo->prepare("INSERT INTO dash_projects (user_id, client_id, project_name, project_description, source_language, target_language, service_type, word_count, character_count, rate_per_word, rate_per_character, total_amount, currency, status, priority, start_date, deadline, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $result = $stmt->execute([
                        $user_id,
                        $_POST['client_id'],
                        $_POST['project_name'],
                        $_POST['project_description'] ?? '',
                        $_POST['source_language'],
                        $_POST['target_language'],
                        $_POST['service_type'],
                        $word_count,
                        $character_count,
                        $rate_per_word,
                        $rate_per_character,
                        $total_amount,
                        $_POST['currency'] ?? 'BRL',
                        $_POST['status'],
                        $_POST['priority'],
                        $_POST['start_date'] ?: null,
                        $_POST['deadline'] ?: null,
                        $_POST['notes'] ?? ''
                    ]);
                    
                    if ($result) {
                        $message = 'Projeto adicionado com sucesso!';
                    } else {
                        $error = 'Erro ao adicionar projeto.';
                    }
                } catch (PDOException $e) {
                    $error = 'Erro: ' . $e->getMessage();
                }
                break;
                
            case 'edit_project':
                try {
                    $word_count = intval($_POST['word_count'] ?? 0);
                    $character_count = intval($_POST['character_count'] ?? 0);
                    $rate_per_word = floatval($_POST['rate_per_word'] ?? 0);
                    $rate_per_character = floatval($_POST['rate_per_character'] ?? 0);
                    $total_amount = calculateProjectTotal($word_count, $character_count, $rate_per_word, $rate_per_character);
                    
                    $stmt = $pdo->prepare("UPDATE dash_projects SET client_id = ?, project_name = ?, project_description = ?, source_language = ?, target_language = ?, service_type = ?, word_count = ?, character_count = ?, rate_per_word = ?, rate_per_character = ?, total_amount = ?, currency = ?, status = ?, priority = ?, start_date = ?, deadline = ?, notes = ? WHERE id = ? AND user_id = ?");
                    $result = $stmt->execute([
                        $_POST['client_id'],
                        $_POST['project_name'],
                        $_POST['project_description'] ?? '',
                        $_POST['source_language'],
                        $_POST['target_language'],
                        $_POST['service_type'],
                        $word_count,
                        $character_count,
                        $rate_per_word,
                        $rate_per_character,
                        $total_amount,
                        $_POST['currency'] ?? 'BRL',
                        $_POST['status'],
                        $_POST['priority'],
                        $_POST['start_date'] ?: null,
                        $_POST['deadline'] ?: null,
                        $_POST['notes'] ?? '',
                        $_POST['project_id'],
                        $user_id
                    ]);
                    
                    if ($result) {
                        $message = 'Projeto atualizado com sucesso!';
                    } else {
                        $error = 'Erro ao atualizar projeto.';
                    }
                } catch (PDOException $e) {
                    $error = 'Erro: ' . $e->getMessage();
                }
                break;
                
            case 'delete_project':
                try {
                    $stmt = $pdo->prepare("DELETE FROM dash_projects WHERE id = ? AND user_id = ?");
                    $result = $stmt->execute([$_POST['project_id'], $user_id]);
                    
                    if ($result) {
                        $message = 'Projeto excluído com sucesso!';
                    } else {
                        $error = 'Erro ao excluir projeto.';
                    }
                } catch (PDOException $e) {
                    $error = 'Erro: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Obter lista de clientes para o dropdown
$stmt = $pdo->prepare("SELECT id, company_name FROM dash_clients WHERE user_id = ? ORDER BY company_name ASC");
$stmt->execute([$user_id]);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obter lista de projetos
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$where_clause = "WHERE p.user_id = ?";
$params = [$user_id];

if ($search) {
    $where_clause .= " AND (p.project_name LIKE ? OR c.company_name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($status_filter) {
    $where_clause .= " AND p.status = ?";
    $params[] = $status_filter;
}

$stmt = $pdo->prepare("SELECT p.*, c.company_name, c.contact_name FROM dash_projects p LEFT JOIN dash_clients c ON p.client_id = c.id $where_clause ORDER BY p.created_at DESC");
$stmt->execute($params);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obter projeto para edição se solicitado
$edit_project = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM dash_projects WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['edit'], $user_id]);
    $edit_project = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<?php include 'includes/head.php'; ?>
<body class="bg-gray-950 text-white font-inter">
<div class="flex min-h-screen">

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 px-4 py-8 bg-gray-950">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-4xl font-bold mb-2">Gerenciar Projetos</h1>
            <p class="text-gray-400 mb-8">Crie e acompanhe seus projetos de tradução.</p>

    <!-- Mensagens -->
    <?php if ($message): ?>
        <div class="bg-green-600 text-white p-4 rounded-lg mb-6">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="bg-red-600 text-white p-4 rounded-lg mb-6">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Formulário de Adicionar/Editar Projeto -->
    <div class="bg-gray-900 rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-white mb-4">
            <?php echo $edit_project ? 'Editar Projeto' : 'Adicionar Novo Projeto'; ?>
        </h2>
        
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <input type="hidden" name="action" value="<?php echo $edit_project ? 'edit_project' : 'add_project'; ?>">
            <?php if ($edit_project): ?>
                <input type="hidden" name="project_id" value="<?php echo $edit_project['id']; ?>">
            <?php endif; ?>
            
            <div class="lg:col-span-2">
                <label for="project_name" class="block text-sm font-medium text-gray-300 mb-2">Nome do Projeto *</label>
                <input type="text" name="project_name" id="project_name" required
                       value="<?php echo htmlspecialchars($edit_project['project_name'] ?? ''); ?>"
                       class="w-full p-3 bg-gray-800 border border-gray-700 rounded-lg focus:border-purple-500 focus:outline-none text-white">
            </div>
            
            <div>
                <label for="client_id" class="block text-sm font-medium text-gray-300 mb-2">Cliente *</label>
                <select name="client_id" id="client_id" required
                        class="w-full p-3 bg-gray-800 border border-gray-700 rounded-lg focus:border-purple-500 focus:outline-none text-white">
                    <option value="">Selecione um cliente</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?php echo $client['id']; ?>" 
                                <?php echo ($edit_project && $edit_project['client_id'] == $client['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($client['company_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="source_language" class="block text-sm font-medium text-gray-300 mb-2">Idioma de Origem *</label>
                <select name="source_language" id="source_language" required
                        class="w-full p-3 bg-gray-800 border border-gray-700 rounded-lg focus:border-purple-500 focus:outline-none text-white">
                    <option value="">Selecione</option>
                    <option value="pt-BR" <?php echo ($edit_project && $edit_project['source_language'] == 'pt-BR') ? 'selected' : ''; ?>>Português (Brasil)</option>
                    <option value="en-US" <?php echo ($edit_project && $edit_project['source_language'] == 'en-US') ? 'selected' : ''; ?>>Inglês (EUA)</option>
                    <option value="es-ES" <?php echo ($edit_project && $edit_project['source_language'] == 'es-ES') ? 'selected' : ''; ?>>Espanhol (Espanha)</option>
                    <option value="fr-FR" <?php echo ($edit_project && $edit_project['source_language'] == 'fr-FR') ? 'selected' : ''; ?>>Francês</option>
                    <option value="de-DE" <?php echo ($edit_project && $edit_project['source_language'] == 'de-DE') ? 'selected' : ''; ?>>Alemão</option>
                    <option value="it-IT" <?php echo ($edit_project && $edit_project['source_language'] == 'it-IT') ? 'selected' : ''; ?>>Italiano</option>
                </select>
            </div>
            
            <div>
                <label for="target_language" class="block text-sm font-medium text-gray-300 mb-2">Idioma de Destino *</label>
                <select name="target_language" id="target_language" required
                        class="w-full p-3 bg-gray-800 border border-gray-700 rounded-lg focus:border-purple-500 focus:outline-none text-white">
                    <option value="">Selecione</option>
                    <option value="pt-BR" <?php echo ($edit_project && $edit_project['target_language'] == 'pt-BR') ? 'selected' : ''; ?>>Português (Brasil)</option>
                    <option value="en-US" <?php echo ($edit_project && $edit_project['target_language'] == 'en-US') ? 'selected' : ''; ?>>Inglês (EUA)</option>
                    <option value="es-ES" <?php echo ($edit_project && $edit_project['target_language'] == 'es-ES') ? 'selected' : ''; ?>>Espanhol (Espanha)</option>
                    <option value="fr-FR" <?php echo ($edit_project && $edit_project['target_language'] == 'fr-FR') ? 'selected' : ''; ?>>Francês</option>
                    <option value="de-DE" <?php echo ($edit_project && $edit_project['target_language'] == 'de-DE') ? 'selected' : ''; ?>>Alemão</option>
                    <option value="it-IT" <?php echo ($edit_project && $edit_project['target_language'] == 'it-IT') ? 'selected' : ''; ?>>Italiano</option>
                </select>
            </div>
            
            <div>
                <label for="service_type" class="block text-sm font-medium text-gray-300 mb-2">Tipo de Serviço *</label>
                <select name="service_type" id="service_type" required
                        class="w-full p-3 bg-gray-800 border border-gray-700 rounded-lg focus:border-purple-500 focus:outline-none text-white">
                    <option value="translation" <?php echo ($edit_project && $edit_project['service_type'] == 'translation') ? 'selected' : ''; ?>>Tradução</option>
                    <option value="revision" <?php echo ($edit_project && $edit_project['service_type'] == 'revision') ? 'selected' : ''; ?>>Revisão</option>
                    <option value="proofreading" <?php echo ($edit_project && $edit_project['service_type'] == 'proofreading') ? 'selected' : ''; ?>>Revisão de Texto</option>
                    <option value="localization" <?php echo ($edit_project && $edit_project['service_type'] == 'localization') ? 'selected' : ''; ?>>Localização</option>
                    <option value="transcription" <?php echo ($edit_project && $edit_project['service_type'] == 'transcription') ? 'selected' : ''; ?>>Transcrição</option>
                    <option value="other" <?php echo ($edit_project && $edit_project['service_type'] == 'other') ? 'selected' : ''; ?>>Outro</option>
                </select>
            </div>
            
            <div>
                <label for="word_count" class="block text-sm font-medium text-gray-300 mb-2">Contagem de Palavras</label>
                <input type="number" name="word_count" id="word_count" min="0"
                       value="<?php echo $edit_project['word_count'] ?? ''; ?>"
                       class="w-full p-3 bg-gray-800 border border-gray-700 rounded-lg focus:border-purple-500 focus:outline-none text-white"
                       onchange="calculateTotal()">
            </div>
            
            <div>
                <label for="rate_per_word" class="block text-sm font-medium text-gray-300 mb-2">Taxa por Palavra (R$)</label>
                <input type="number" name="rate_per_word" id="rate_per_word" min="0" step="0.01"
                       value="<?php echo $edit_project['rate_per_word'] ?? ''; ?>"
                       class="w-full p-3 bg-gray-800 border border-gray-700 rounded-lg focus:border-purple-500 focus:outline-none text-white"
                       onchange="calculateTotal()">
            </div>
            
            <div>
                <label for="character_count" class="block text-sm font-medium text-gray-300 mb-2">Contagem de Caracteres</label>
                <input type="number" name="character_count" id="character_count" min="0"
                       value="<?php echo $edit_project['character_count'] ?? ''; ?>"
                       class="w-full p-3 bg-gray-800 border border-gray-700 rounded-lg focus:border-purple-500 focus:outline-none text-white"
                       onchange="calculateTotal()">
            </div>
            
            <div>
                <label for="rate_per_character" class="block text-sm font-medium text-gray-300 mb-2">Taxa por Caractere (R$)</label>
                <input type="number" name="rate_per_character" id="rate_per_character" min="0" step="0.01"
                       value="<?php echo $edit_project['rate_per_character'] ?? ''; ?>"
                       class="w-full p-3 bg-gray-800 border border-gray-700 rounded-lg focus:border-purple-500 focus:outline-none text-white"
                       onchange="calculateTotal()">
            </div>
            
            <div>
                <label for="total_amount" class="block text-sm font-medium text-gray-300 mb-2">Valor Total (R$)</label>
                <input type="text" id="total_amount" readonly
                       class="w-full p-3 bg-gray-600 border border-gray-600 rounded-lg text-white">
            </div>
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                <select name="status" id="status"
                        class="w-full p-3 bg-gray-800 border border-gray-700 rounded-lg focus:border-purple-500 focus:outline-none text-white">
                    <option value="pending" <?php echo ($edit_project && $edit_project['status'] == 'pending') ? 'selected' : ''; ?>>Pendente</option>
                    <option value="in_progress" <?php echo ($edit_project && $edit_project['status'] == 'in_progress') ? 'selected' : ''; ?>>Em Andamento</option>
                    <option value="completed" <?php echo ($edit_project && $edit_project['status'] == 'completed') ? 'selected' : ''; ?>>Concluído</option>
                    <option value="on_hold" <?php echo ($edit_project && $edit_project['status'] == 'on_hold') ? 'selected' : ''; ?>>Pausado</option>
                    <option value="cancelled" <?php echo ($edit_project && $edit_project['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>
            
            <div>
                <label for="priority" class="block text-sm font-medium text-gray-300 mb-2">Prioridade</label>
                <select name="priority" id="priority"
                        class="w-full p-3 bg-gray-800 border border-gray-700 rounded-lg focus:border-purple-500 focus:outline-none text-white">
                    <option value="low" <?php echo ($edit_project && $edit_project['priority'] == 'low') ? 'selected' : ''; ?>>Baixa</option>
                    <option value="medium" <?php echo ($edit_project && $edit_project['priority'] == 'medium') ? 'selected' : ''; ?>>Média</option>
                    <option value="high" <?php echo ($edit_project && $edit_project['priority'] == 'high') ? 'selected' : ''; ?>>Alta</option>
                    <option value="urgent" <?php echo ($edit_project && $edit_project['priority'] == 'urgent') ? 'selected' : ''; ?>>Urgente</option>
                </select>
            </div>
            
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-300 mb-2">Data de Início</label>
                <input type="date" name="start_date" id="start_date"
                       value="<?php echo $edit_project['start_date'] ?? ''; ?>"
                       class="w-full p-3 bg-gray-800 border border-gray-700 rounded-lg focus:border-purple-500 focus:outline-none text-white">
            </div>
            
            <div>
                <label for="deadline" class="block text-sm font-medium text-gray-300 mb-2">Prazo de Entrega</label>
                <input type="date" name="deadline" id="deadline"
                       value="<?php echo $edit_project['deadline'] ?? ''; ?>"
                       class="w-full p-3 bg-gray-800 border border-gray-700 rounded-lg focus:border-purple-500 focus:outline-none text-white">
            </div>
            
            <div class="lg:col-span-3">
                <label for="project_description" class="block text-sm font-medium text-gray-300 mb-2">Descrição do Projeto</label>
                <textarea name="project_description" id="project_description" rows="3"
                          class="w-full p-3 bg-gray-800 border border-gray-700 rounded-lg focus:border-purple-500 focus:outline-none text-white"><?php echo htmlspecialchars($edit_project['project_description'] ?? ''); ?></textarea>
            </div>
            
            <div class="lg:col-span-3">
                <label for="notes" class="block text-sm font-medium text-gray-300 mb-2">Observações</label>
                <textarea name="notes" id="notes" rows="3"
                          class="w-full p-3 bg-gray-800 border border-gray-700 rounded-lg focus:border-purple-500 focus:outline-none text-white"><?php echo htmlspecialchars($edit_project['notes'] ?? ''); ?></textarea>
            </div>
            
            <div class="lg:col-span-3 flex gap-4">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 px-6 py-3 rounded-lg transition-colors text-white">
                    <?php echo $edit_project ? 'Atualizar Projeto' : 'Adicionar Projeto'; ?>
                </button>
                <?php if ($edit_project): ?>
                    <a href="projects.php" class="bg-gray-600 hover:bg-gray-700 px-6 py-3 rounded-lg transition-colors text-white">
                        Cancelar
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Filtros e Lista de Projetos -->
    <div class="bg-gray-900 rounded-lg p-6">
        <div class="flex flex-col md:flex-row items-center justify-between mb-6 gap-4">
            <h2 class="text-xl font-semibold text-white">Lista de Projetos</h2>
            
            <!-- Filtros -->
            <div class="flex gap-2">
                <form method="GET" class="flex gap-2">
                    <input type="text" name="search" placeholder="Buscar projetos..."
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:border-purple-500 focus:outline-none text-white">
                    <select name="status" class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:border-purple-500 focus:outline-none text-white">
                        <option value="">Todos os status</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pendente</option>
                        <option value="in_progress" <?php echo $status_filter == 'in_progress' ? 'selected' : ''; ?>>Em Andamento</option>
                        <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Concluído</option>
                        <option value="on_hold" <?php echo $status_filter == 'on_hold' ? 'selected' : ''; ?>>Pausado</option>
                        <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                    </select>
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php if ($search || $status_filter): ?>
                        <a href="projects.php" class="bg-gray-600 hover:bg-gray-700 px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <?php if (empty($projects)): ?>
            <p class="text-gray-400 text-center py-8">
                <?php echo ($search || $status_filter) ? 'Nenhum projeto encontrado com os critérios de busca.' : 'Nenhum projeto cadastrado ainda.'; ?>
            </p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="pb-3 text-gray-300">Projeto</th>
                            <th class="pb-3 text-gray-300">Cliente</th>
                            <th class="pb-3 text-gray-300">Idiomas</th>
                            <th class="pb-3 text-gray-300">Status</th>
                            <th class="pb-3 text-gray-300">Valor</th>
                            <th class="pb-3 text-gray-300">Prazo</th>
                            <th class="pb-3 text-gray-300">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project): ?>
                            <tr class="border-b border-gray-700 hover:bg-gray-700">
                                <td class="py-4">
                                    <div>
                                        <p class="text-white font-medium"><?php echo htmlspecialchars($project['project_name']); ?></p>
                                        <p class="text-gray-400 text-sm"><?php echo ucfirst($project['service_type']); ?></p>
                                    </div>
                                </td>
                                <td class="py-4 text-gray-300"><?php echo htmlspecialchars($project['company_name']); ?></td>
                                <td class="py-4 text-gray-300 text-sm">
                                    <?php echo $project['source_language']; ?> → <?php echo $project['target_language']; ?>
                                </td>
                                <td class="py-4">
                                    <span class="inline-block px-2 py-1 text-xs rounded-full
                                        <?php 
                                        switch($project['status']) {
                                            case 'completed': echo 'bg-green-600 text-white'; break;
                                            case 'in_progress': echo 'bg-blue-600 text-white'; break;
                                            case 'pending': echo 'bg-yellow-600 text-white'; break;
                                            case 'on_hold': echo 'bg-orange-600 text-white'; break;
                                            case 'cancelled': echo 'bg-red-600 text-white'; break;
                                            default: echo 'bg-gray-600 text-white';
                                        }
                                        ?>">
                                        <?php 
                                        $status_labels = [
                                            'pending' => 'Pendente',
                                            'in_progress' => 'Em Andamento',
                                            'completed' => 'Concluído',
                                            'cancelled' => 'Cancelado',
                                            'on_hold' => 'Pausado'
                                        ];
                                        echo $status_labels[$project['status']] ?? $project['status'];
                                        ?>
                                    </span>
                                </td>
                                <td class="py-4 text-gray-300">R$ <?php echo number_format($project['total_amount'], 2, ',', '.'); ?></td>
                                <td class="py-4 text-gray-300">
                                    <?php if ($project['deadline']): ?>
                                        <?php echo date('d/m/Y', strtotime($project['deadline'])); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="py-4">
                                    <div class="flex gap-2">
                                        <a href="?edit=<?php echo $project['id']; ?>" 
                                           class="bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded text-sm transition-colors">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este projeto?')">
                                            <input type="hidden" name="action" value="delete_project">
                                            <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                            <button type="submit" class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm transition-colors">
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
</div>
</div>
</div>
<script>
function calculateTotal() {
    const wordCount = parseFloat(document.getElementById('word_count').value) || 0;
    const ratePerWord = parseFloat(document.getElementById('rate_per_word').value) || 0;
    const characterCount = parseFloat(document.getElementById('character_count').value) || 0;
    const ratePerCharacter = parseFloat(document.getElementById('rate_per_character').value) || 0;
    
    const wordTotal = wordCount * ratePerWord;
    const charTotal = characterCount * ratePerCharacter;
    const total = wordTotal + charTotal;
    
    document.getElementById('total_amount').value = 'R$ ' + total.toFixed(2).replace('.', ',');
}

// Calcular total ao carregar a página se estiver editando
document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
});
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
