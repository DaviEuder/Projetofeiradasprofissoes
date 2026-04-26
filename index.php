<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placar de Líderes - Desafio do Basquete</title>
    <!-- Carrega Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Configuração da Fonte Inter e cores para o Tailwind -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        'primary': '#1D4ED8', // Cor primária (azul)
                        'gold': '#FFD700',
                        'silver': '#C0C0C0',
                        'bronze': '#CD7F32',
                        'dark-bg': '#1F2937',
                    }
                }
            }
        }
    </script>
    <style>
       
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0F172A;
        }
        
        .podium-shine {
           
            animation: shine 3s infinite alternate; 
        }
        @keyframes shine {
            0% { box-shadow: 0 0 8px rgba(255, 215, 0, 0.4); }
            50% { box-shadow: 0 0 20px rgba(255, 215, 0, 0.9); }
            100% { box-shadow: 0 0 8px rgba(255, 215, 0, 0.4); }
        }
    </style>
</head>
<body class="text-white min-h-screen p-4 sm:p-8">

<?php

// ----------------------------------------------------
// PHP - FUNÇÕES DE CONEXÃO E LEITURA
// ----------------------------------------------------

function connectDB() {
    // A variável DATABASE_URL é fornecida pelo ambiente
    $databaseUrl = getenv('DATABASE_URL');
    if (empty($databaseUrl)) {
        // Retorna um erro amigável se a variável de ambiente não estiver definida
        throw new Exception("DATABASE_URL não está definida. O Placar não pode se conectar ao banco de dados.");
    }
    
    $url = parse_url($databaseUrl);
    $host = $url['host'];
    $port = $url['port'] ?? 5432;
    $user = $url['user'];
    $password = $url['pass'];
    $dbname = ltrim($url['path'], '/');

    // Cria a string DSN para PostgreSQL (ou outro banco compatível)
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    
    // Conexão PDO
    $db = new PDO($dsn, $user, $password); 
    
    // Configura o PDO para lançar exceções em caso de erro
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
}

function getScoreboard($conn) {
    // Seleciona os 15 melhores scores, ordenando por pontos (DESC) e desempate por data
    $stmt = $conn->prepare("
        SELECT nome_jogador, pontos 
        FROM registros_partida 
        ORDER BY pontos DESC, data_registro DESC 
        LIMIT 15
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$conn = null;
$error_message = null;
$scoreboard = [];

try {
    $conn = connectDB();
    $scoreboard = getScoreboard($conn);
    
    // O código agora APENAS LÊ os dados.

} catch (Exception $e) { 
    // Captura qualquer erro de conexão ou de SQL
    $error_message = "Erro no DB: " . htmlspecialchars($e->getMessage());
}

?>

<!-- Título Principal -->
<div class="max-w-4xl mx-auto text-center mb-10">
    <h1 class="text-5xl font-extrabold text-white mb-2">Placar de Líderes</h1>
    <p class="text-xl text-indigo-400">Desafio de Basquete Arduino</p>
    <?php if ($error_message): ?>
        <!-- Exibe erro de conexão em destaque -->
        <p class="text-red-500 mt-4 p-3 bg-red-900/50 rounded-xl border border-red-700 font-medium"><?php echo $error_message; ?></p>
    <?php else: ?>
        <p class="text-green-400 mt-4 font-medium">Status: Conexão com Banco de Dados OK.</p>
    <?php endif; ?>
</div>

<!-- Container do Placar -->
<div class="max-w-3xl mx-auto bg-gray-800 rounded-2xl shadow-2xl p-4 sm:p-8 border border-gray-700">
    
    <?php if (empty($scoreboard)): ?>
        <div class="text-center py-10 text-gray-400">
            <!-- Ícone de Pódio (substituído por SVG simples) -->
            <svg class="w-12 h-12 mx-auto mb-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0h3m-3 0h-3m3 0V7a2 2 0 012-2h2a2 2 0 012 2v12a2 2 0 01-2 2h-2a2 2 0 01-2-2zm0 0h6"></path></svg>
            <p class="text-xl font-bold">Nenhum registro encontrado.</p>
            <p>Seja o primeiro a marcar a sua cesta!</p>
        </div>
    <?php else: ?>
        
        <?php 
            // Separa os 3 primeiros para o pódio e o restante para a lista
            $top3 = array_slice($scoreboard, 0, 3);
            $remainingScores = array_slice($scoreboard, 3);
        ?>

        <!-- Pódio (Top 3) -->
        <div class="grid grid-cols-3 gap-4 mb-8 text-center items-end">
            
            <!-- 2º Lugar -->
            <?php if (isset($top3[1])): ?>
            <div class="order-1 flex flex-col justify-end transform hover:scale-105 transition duration-300 ease-in-out">
                <div class="bg-silver/80 text-gray-900 p-3 sm:p-5 rounded-t-xl shadow-xl border-t-4 border-l-4 border-r-4 border-silver">
                    <p class="font-bold text-lg sm:text-2xl"><?php echo htmlspecialchars($top3[1]['pontos']); ?></p>
                    <p class="text-sm sm:text-base truncate"><?php echo htmlspecialchars($top3[1]['nome_jogador']); ?></p>
                </div>
                <div class="bg-gray-700/80 h-16 sm:h-24 rounded-b-xl flex items-center justify-center font-black text-4xl border-b-4 border-silver">🥈</div>
            </div>
            <?php endif; ?>

            <!-- 1º Lugar -->
            <?php if (isset($top3[0])): ?>
            <div class="order-0 flex flex-col justify-end transform hover:scale-105 transition duration-300 ease-in-out">
                <div class="bg-gold text-gray-900 p-4 sm:p-6 rounded-t-xl shadow-2xl podium-shine border-t-8 border-l-4 border-r-4 border-yellow-400">
                    <p class="font-black text-2xl sm:text-4xl leading-none"><?php echo htmlspecialchars($top3[0]['pontos']); ?></p>
                    <p class="text-base sm:text-xl truncate mt-1 font-semibold"><?php echo htmlspecialchars($top3[0]['nome_jogador']); ?></p>
                </div>
                <div class="bg-gray-700/80 h-24 sm:h-36 rounded-b-xl flex items-center justify-center font-black text-5xl border-b-8 border-gold">🥇</div>
            </div>
            <?php endif; ?>

            <!-- 3º Lugar -->
            <?php if (isset($top3[2])): ?>
            <div class="order-2 flex flex-col justify-end transform hover:scale-105 transition duration-300 ease-in-out">
                <div class="bg-bronze/80 text-gray-900 p-3 sm:p-5 rounded-t-xl shadow-xl border-t-4 border-l-4 border-r-4 border-bronze">
                    <p class="font-bold text-lg sm:text-2xl"><?php echo htmlspecialchars($top3[2]['pontos']); ?></p>
                    <p class="text-sm sm:text-base truncate"><?php echo htmlspecialchars($top3[2]['nome_jogador']); ?></p>
                </div>
                <div class="bg-gray-700/80 h-12 sm:h-16 rounded-b-xl flex items-center justify-center font-black text-3xl border-b-4 border-bronze">🥉</div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Tabela dos demais (4º em diante) -->
        <?php if (!empty($remainingScores)): ?>
        <h3 class="text-xl font-extrabold mt-10 mb-4 border-b border-gray-700 pb-2 text-indigo-300">Outros Melhores</h3>
        <ul class="space-y-2">
            <?php $rank = 4; ?>
            <?php foreach ($remainingScores as $row): ?>
                <li class="flex justify-between items-center bg-gray-700/50 p-4 rounded-xl hover:bg-gray-700/80 transition duration-150 border-l-4 border-indigo-500/50">
                    <!-- Classificação -->
                    <span class="text-gray-400 font-bold w-1/12 text-lg"><?php echo $rank++; ?>.</span>
                    <!-- Nome do Jogador -->
                    <span class="truncate w-6/12 font-medium text-white"><?php echo htmlspecialchars($row['nome_jogador']); ?></span>
                    <!-- Pontuação -->
                    <span class="text-xl font-extrabold w-4/12 text-right text-indigo-400"><?php echo htmlspecialchars($row['pontos']); ?> pts</span>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

    <?php endif; ?>

</div>

</body>
</html>
