<?php
session_start();
require_once 'config/database.php';

$page_title = 'Planos e Preços';
$page_description = 'Escolha o plano ideal para sua carreira. Acesso a mais de 350 palestras especializadas e com direito a certificados.';

$redirect = $_GET['redirect'] ?? '';

include 'includes/header.php';
?>

<div class="min-h-screen px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-16">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">Planos e Preços</h1>
            <p class="text-xl text-gray-400 max-w-3xl mx-auto">
                Escolha o plano que melhor se adapta a suas necessidades e acelere sua 
                carreira como profissional de tradução, interpretação ou revisão.
            </p>
            
            <?php if ($redirect): ?>
                <div class="mt-6 bg-yellow-600 bg-opacity-20 border border-yellow-600 border-opacity-30 rounded-lg p-4 max-w-md mx-auto">
                    <p class="text-yellow-300">
                        <i class="fas fa-lock mr-2"></i>
                        É necessário ser assinante para acessar este conteúdo.
                    </p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Planos -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
            <?php
            $plans = [
                [
                    'name' => 'Mensal',
                    'price' => 'R$ 53',
                    'period' => '/mês',
                    'savings' => 'Muito barato',
                    'description' => 'Ideal para começar',
                    'hotmart_link' => 'https://pay.hotmart.com/V94273047M?off=i1hvrpr2&checkoutMode=10', // Substituir pelo link real da Hotmart
                    'popular' => false
                ],
                [
                    'name' => 'Trimestral',
                    'price' => 'R$ 150',
                    'period' => '/3 meses',
                    'savings' => 'Economize R$ 9',
                    'description' => 'Melhor custo-benefício',
                    'hotmart_link' => 'https://pay.hotmart.com/V94273047M?off=whfa869v&checkoutMode=10', // Substituir pelo link real da Hotmart
                    'popular' => true
                ],
                [
                    'name' => 'Semestral',
                    'price' => 'R$ 285',
                    'period' => '/6 meses',
                    'savings' => 'Economize R$ 33',
                    'description' => 'Para estudos contínuos',
                    'hotmart_link' => 'https://pay.hotmart.com/V94273047M?off=qh0m3cuy&checkoutMode=10', // Substituir pelo link real da Hotmart
                    'popular' => false
                ],
                [
                    'name' => 'Anual',
                    'price' => 'R$ 550',
                    'period' => '/ano',
                    'savings' => 'Economize R$ 86',
                    'description' => 'Máxima economia',
                    'hotmart_link' => 'https://pay.hotmart.com/V94273047M?off=cor1dwtx&checkoutMode=10', // Substituir pelo link real da Hotmart
                    'popular' => false
                ]
            ];
            
            foreach($plans as $plan):
            ?>
                <div class="bg-gray-900 rounded-lg p-6 hover:scale-105 transition-transform relative <?php echo $plan['popular'] ? 'ring-2 ring-purple-500' : ''; ?>">
                    <?php if ($plan['popular']): ?>
                        <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                            <span class="bg-purple-600 text-white px-4 py-1 rounded-full text-sm font-semibold">
                                Mais Popular
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="text-center">
                        <h3 class="text-xl font-bold mb-2"><?php echo $plan['name']; ?></h3>
                        <p class="text-gray-400 text-sm mb-4"><?php echo $plan['description']; ?></p>
                        
                        <div class="mb-6">
                            <div class="text-3xl font-bold text-purple-400"><?php echo $plan['price']; ?></div>
                            <div class="text-gray-400"><?php echo $plan['period']; ?></div>
                            <?php if ($plan['savings']): ?>
                                <div class="text-green-400 text-sm mt-2 font-semibold">
                                    <?php echo $plan['savings']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <a 
                            href="<?php echo $plan['hotmart_link']; ?>" 
                            class="block w-full bg-purple-600 hover:bg-purple-700 py-3 rounded-lg font-semibold transition-colors mb-4"
                            target="_blank"
                        >
                            Assinar
                        </a>
                    </div>
                    
                    <!-- Benefícios inclusos -->
                    <div class="text-sm text-gray-400">
                        <p class="font-semibold mb-2">Incluso no plano:</p>
                        <ul class="space-y-1">
                            <li><i class="fas fa-check text-green-400 mr-2"></i>Acesso a todas as palestras</li>
                            <li><i class="fas fa-check text-green-400 mr-2"></i>Certificados automáticos</li>
                            <li><i class="fas fa-check text-green-400 mr-2"></i>Suporte especializado</li>
                            <li><i class="fas fa-check text-green-400 mr-2"></i>Cancelamento fácil</li>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Benefícios Gerais -->
        <div class="bg-gray-900 rounded-lg p-8 mb-16">
            <h2 class="text-3xl font-bold text-center mb-8">O que você ganha com a assinatura</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="text-4xl mb-4">🎥</div>
                    <h3 class="text-xl font-semibold mb-3">Quase 400 Palestras</h3>
                    <p class="text-gray-400">
                        Acesso completo ao nosso extenso catálogo de palestras especializadas.
                    </p>
                </div>
                
                <div class="text-center">
                    <div class="text-4xl mb-4">📜</div>
                    <h3 class="text-xl font-semibold mb-3">Certificados Automáticos</h3>
                    <p class="text-gray-400">
                        Receba certificados em PDF automaticamente após assistir às palestras.
                    </p>
                </div>
                
                <div class="text-center">
                    <div class="text-4xl mb-4">🔄</div>
                    <h3 class="text-xl font-semibold mb-3">Novas palestras toda semana</h3>
                    <p class="text-gray-400">
                        Conteúdo novo toda semana para manter você sempre atualizado.
                    </p>
                </div>
                
                <div class="text-center">
                    <div class="text-4xl mb-4">📱</div>
                    <h3 class="text-xl font-semibold mb-3">Acesso Multiplataforma</h3>
                    <p class="text-gray-400">
                        Assista em qualquer dispositivo, a qualquer hora e lugar.
                    </p>
                </div>
                
                <div class="text-center">
                    <div class="text-4xl mb-4">🎯</div>
                    <h3 class="text-xl font-semibold mb-3">Conteúdo Especializado</h3>
                    <p class="text-gray-400">
                        Focado exclusivamente em tradução, interpretação e revisão.
                    </p>
                </div>
                
                <div class="text-center">
                    <div class="text-4xl mb-4">❌</div>
                    <h3 class="text-xl font-semibold mb-3">Cancelamento Fácil</h3>
                    <p class="text-gray-400">
                        Cancele sua assinatura a qualquer momento, sem complicações.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Depoimentos -->
        <div class="mb-16">
            <h2 class="text-3xl font-bold text-center mb-12">O que os assinantes dizem</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-gray-900 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center mr-4">
                            <span class="text-white font-bold">MC</span>
                        </div>
                        <div>
                            <h4 class="font-semibold">Maria Clara</h4>
                            <p class="text-gray-400 text-sm">Tradutora Juramentada</p>
                        </div>
                    </div>
                    <p class="text-gray-300">
                        "As palestras são excelentes e me ajudaram muito a aprimorar minhas técnicas 
                        de tradução. O certificado automático é um diferencial."
                    </p>
                    <div class="flex text-yellow-400 mt-4">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                
                <div class="bg-gray-900 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center mr-4">
                            <span class="text-white font-bold">JS</span>
                        </div>
                        <div>
                            <h4 class="font-semibold">João Silva</h4>
                            <p class="text-gray-400 text-sm">Intérprete Simultâneo</p>
                        </div>
                    </div>
                    <p class="text-gray-300">
                        "Conteúdo de altíssima qualidade com palestrantes renomados. 
                        Indispensável para quem quer se manter atualizado no mercado."
                    </p>
                    <div class="flex text-yellow-400 mt-4">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                
                <div class="bg-gray-900 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center mr-4">
                            <span class="text-white font-bold">AF</span>
                        </div>
                        <div>
                            <h4 class="font-semibold">Ana Fernandes</h4>
                            <p class="text-gray-400 text-sm">Revisora Técnica</p>
                        </div>
                    </div>
                    <p class="text-gray-300">
                        "A plataforma é muito fácil de usar e o preço é baixíssimo. 
                        Já assisti dezenas de palestras e todas agregaram muito conhecimento."
                    </p>
                    <div class="flex text-yellow-400 mt-4">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Call to Action Final -->
        <div class="bg-gradient-to-r from-purple-900 to-purple-600 rounded-lg p-8 text-center">
            <h2 class="text-3xl font-bold mb-4">
                Vamos acelerar sua carreira?
            </h2>
            <p class="text-xl mb-8 opacity-90">
                Junte-se a mais de 1.500 profissionais que já transformaram suas carreiras com a Translators101.
            </p>
            <a href="#planos" onclick="document.querySelector('.grid').scrollIntoView({behavior: 'smooth'})" 
               class="bg-white text-purple-600 hover:bg-gray-100 px-8 py-4 rounded-lg text-lg font-semibold transition-colors inline-block">
                Escolher Plano
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
