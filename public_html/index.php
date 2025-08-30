<?php

// Inclui o arquivo de funções de autenticação e conexão com o banco de dados
require_once __DIR__ . 
'/config/database.php';

// Inclui o cabeçalho do site Translators101
include __DIR__ . 
'/includes/header.php';

// URL da Landing Page da Hotmart
$hotmart_lp_url = 'https://hotm.art/t101';

// Tenta obter o conteúdo da página da Hotmart
$hotmart_content = @file_get_contents($hotmart_lp_url );

if ($hotmart_content === FALSE) {
    // Se não conseguir carregar, exibe uma mensagem de erro ou um conteúdo alternativo
    echo '<div class="container mx-auto p-8 text-center text-red-500">';
    echo '<h1>Erro ao carregar a página da Hotmart.</h1>';
    echo '<p>Por favor, tente novamente mais tarde ou entre em contato com o suporte.</p>';
    echo '</div>';
} else {
    // Exibe o conteúdo da página da Hotmart
    echo $hotmart_content;
}

// Inclui o rodapé do site Translators101
include __DIR__ . 
'/includes/footer.php';

?>
