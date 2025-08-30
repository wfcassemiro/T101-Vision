<?php
session_start();
require_once 'config/database.php';

// Redireciona se não estiver logado
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

// Page settings
$page_title = 'Glossários Especializados';
$page_description = 'Baixe gratuitamente nossos glossários especializados.';
$active_page = 'glossarios';
$hide_top_menu = true;

// Parâmetros de busca e filtros
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$file_type = $_GET['file_type'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 12;
$offset = ($page - 1) * $per_page;

$message = '';
$error = '';

// Construir query de busca
$where_conditions = ['is_active = 1'];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category)) {
    $where_conditions[] = "category = ?";
    $params[] = $category;
}

if (!empty($file_type)) {
    $where_conditions[] = "file_type = ?";
    $params[] = $file_type;
}

$where_sql = 'WHERE ' . implode(' AND ', $where_conditions);

try {
    // Contar total de glossários
    $count_sql = "SELECT COUNT(*) FROM glossary_files $where_sql";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_files = $stmt->fetchColumn();
    
    // Buscar glossários paginados
    $sql = "SELECT * FROM glossary_files $where_sql ORDER BY category, title ASC LIMIT $per_page OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $files = $stmt->fetchAll();
    
    // Buscar categorias para filtro
    $categories_sql = "SELECT DISTINCT category FROM glossary_files WHERE is_active = 1 ORDER BY category";
    $categories = $pdo->query($categories_sql)->fetchAll(PDO::FETCH_COLUMN);
    
    // Buscar tipos de arquivo para filtro
    $file_types_sql = "SELECT DISTINCT file_type FROM glossary_files WHERE is_active = 1 ORDER BY file_type";
    $file_types_list = $pdo->query($file_types_sql)->fetchAll(PDO::FETCH_COLUMN);
    
    $total_pages = ceil($total_files / $per_page);
    
} catch (Exception $e) {
    $error = 'Erro ao carregar glossários: ' . $e->getMessage();
    $files = [];
    $categories = [];
    $file_types_list = [];
    $total_pages = 0;
}

include 'includes/header.php';
?>
<body class="bg-gray-950 text-white font-inter">
<div class="flex min-h-screen">

    <?php include 'sidebar.php'; ?>

    <div class="flex-1 px-4 py-8 bg-gray-950">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-4xl font-bold mb-2">📚 Glossários Especializados</h1>
            <p class="text-gray-400 mb-8">Baixe gratuitamente nossos glossários especializados para tradutores.</p>

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

            <div class="bg-gray-800 rounded-lg p-6 mb-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-300 mb-2">Buscar</label>
                        <input type="text" name="search" id="search" 
                               value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Digite sua busca..."
                               class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg focus:border-purple-500 focus:outline-none text-white">
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-300 mb-2">Categoria</label>
                        <select name="category" id="category"
                                class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg focus:border-purple-500 focus:outline-none text-white">
                            <option value="">Todas as categorias</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" 
                                        <?php echo $category === $cat ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(ucfirst($cat)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="file_type" class="block text-sm font-medium text-gray-300 mb-2">Tipo de Arquivo</label>
                        <select name="file_type" id="file_type"
                                class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg focus:border-purple-500 focus:outline-none text-white">
                            <option value="">Todos os tipos</option>
                            <?php foreach ($file_types_list as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>" 
                                        <?php echo $file_type === $type ? 'selected' : ''; ?>>
                                    <?php echo strtoupper($type); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit" 
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-3 px-4 rounded-lg transition-colors">
                            <i class="fas fa-search mr-2"></i>Buscar
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-gray-800 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <span class="text-gray-300">
                        Mostrando <?php echo count($files); ?> de <?php echo $total_files; ?> glossários
                    </span>
                    <?php if ($search || $category || $file_type): ?>
                        <a href="glossarios.php" class="text-purple-400 hover:text-purple-300 text-sm">
                            <i class="fas fa-times mr-1"></i>Limpar filtros
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($files): ?>
                <div class="space-y-4">
                    <?php foreach ($files as $file): ?>
                        <div class="bg-gray-800 rounded-lg p-6 hover:bg-gray-750 transition-colors">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-xl font-semibold text-white mb-2">
                                        <?php echo htmlspecialchars($file['title']); ?>
                                    </h3>
                                    <div class="flex items-center gap-4 mb-3">
                                        <span class="bg-purple-600 text-xs px-2 py-1 rounded-full text-white">
                                            <?php echo htmlspecialchars(ucfirst($file['category'])); ?>
                                        </span>
                                        <span class="bg-gray-700 text-xs px-2 py-1 rounded-full text-gray-300">
                                            <?php echo strtoupper($file['file_type']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500">
                                    <?php echo date('d/m/Y', strtotime($file['created_at'])); ?>
                                </div>
                            </div>

                            <div class="mb-4">
                                <p class="text-gray-300 leading-relaxed">
                                    <?php echo nl2br(htmlspecialchars($file['description'])); ?>
                                </p>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-file-alt mr-1"></i>
                                    Tamanho: <?php echo htmlspecialchars($file['file_size']); ?>
                                </div>
                                <a href="download.php?id=<?php echo htmlspecialchars($file['id']); ?>"
                                        class="bg-purple-600 hover:bg-purple-700 text-white text-sm py-2 px-4 rounded-lg transition-colors">
                                    <i class="fas fa-download mr-2"></i>Baixar Arquivo
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="mt-8 flex justify-center">
                        <nav class="flex space-x-2">
                            <?php if ($page > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                                   class="px-3 py-2 bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php 
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);
                            
                            for ($i = $start; $i <= $end; $i++): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                                   class="px-3 py-2 rounded-lg transition-colors <?php echo $i === $page ? 'bg-purple-600 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                                   class="px-3 py-2 bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="bg-gray-800 rounded-lg p-12 text-center">
                    <i class="fas fa-book text-6xl text-gray-600 mb-4"></i>
                    <h3 class="text-xl font-semibold text-white mb-2">Nenhum glossário encontrado</h3>
                    <?php if ($search || $category || $file_type): ?>
                        <p class="text-gray-400 mb-4">Nenhum arquivo corresponde aos filtros aplicados.</p>
                        <a href="glossarios.php" class="inline-flex items-center text-purple-400 hover:text-purple-300">
                            <i class="fas fa-times mr-2"></i>Limpar filtros
                        </a>
                    <?php else: ?>
                        <p class="text-gray-400">Ainda não há glossários disponíveis.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="bg-gray-800 rounded-lg p-6 mt-8">
                <h3 class="text-xl font-semibold text-white mb-4">ℹ️ Sobre os Glossários</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-gray-300">
                    <div>
                        <h4 class="font-semibold text-purple-400 mb-2">📋 O que são</h4>
                        <p class="text-sm">
                            Nossos glossários contêm termos especializados organizados por categoria, 
                            disponíveis para download para auxiliar tradutores em diferentes áreas.
                        </p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-purple-400 mb-2">🎯 Como usar</h4>
                        <p class="text-sm">
                            Utilize os filtros para encontrar arquivos por categoria ou tipo de arquivo. 
                            Clique em "Baixar Arquivo" para salvar o glossário em seu computador.
                        </p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-purple-400 mb-2">🔍 Busca inteligente</h4>
                        <p class="text-sm">
                            Use a busca para encontrar glossários específicos ou descrições que contenham 
                            palavras-chave relacionadas ao seu projeto de tradução.
                        </p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-purple-400 mb-2">📋 Funcionalidades</h4>
                        <p class="text-sm">
                            Baixe arquivos diretamente, filtre por categorias temáticas e tipos de arquivo 
                            para encontrar rapidamente o que precisa.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>