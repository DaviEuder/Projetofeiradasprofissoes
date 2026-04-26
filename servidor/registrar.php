<?php
header('Content-Type: text/plain'); // Retorna texto puro

// ----------------------------------------------------
// // FUNÇÃO DE CONEXÃO ROBUSTA
// ----------------------------------------------------

/**
 * Conecta ao banco de dados PostgreSQL usando a variável de ambiente DATABASE_URL.
 * Usa parse_url() para desmembrar a string de conexão do Render.
 * @return PDO A instância da conexão PDO.
 * @throws Exception Se a variável DATABASE_URL não estiver definida ou a conexão falhar.
 */
function connectDB() {
    $databaseUrl = getenv('DATABASE_URL');

    if (empty($databaseUrl)) {
        throw new Exception("DATABASE_URL não está definida.");
    }

    // 1. Desmembra a URL de conexão fornecida pelo Render.
    $url = parse_url($databaseUrl);

    // 2. Extrai cada componente
    $host = $url['host'];
    $port = $url['port'] ?? 5432;
    $user = $url['user'];
    $password = $url['pass'];
    $dbname = ltrim($url['path'], '/');

    // 3. Constrói o DSN no formato exato que o PDO espera
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

    // 4. Tenta conectar
    $db = new PDO($dsn, $user, $password); // Passa user e password explicitamente
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    return $db;
}

// ----------------------------------------------------
// // INÍCIO DA APLICAÇÃO
// ----------------------------------------------------

try {
    // Tenta a conexão com o banco usando a função robusta
    $conn = connectDB();

    // Sanitiza e coleta os dados dos campos 'nome' e 'pontos'
    $nome = trim($_POST['nome'] ?? '');
    // Garante que 'pontos' seja um inteiro, se nao for enviado ou for invalido, sera 0
    $pontos = (int)($_POST['pontos'] ?? 0); 

    if ($nome === '' || $pontos < 0) {
        http_response_code(400); 
        echo "Erro: Dados invalidos. Nome nao pode ser vazio ou pontos negativos.";
        exit;
    }

    // Insere na tabela 'registros_partida'
    $stmt = $conn->prepare("
        INSERT INTO registros_partida (nome_jogador, pontos)
        VALUES (:nome, :pontos)
    ");

    $stmt->bindParam(":nome", $nome);
    $stmt->bindParam(":pontos", $pontos, PDO::PARAM_INT);
    $stmt->execute();

    http_response_code(200);
    echo "Registro inserido com sucesso!";

} catch (Exception $e) { // Captura Exception, que inclui PDOException
    // Se ocorrer um erro no banco ou na conexão
    error_log("Erro no servidor: " . $e->getMessage());
    http_response_code(500); 
    echo "Erro no servidor ao processar o banco de dados. Detalhe: " . $e->getMessage();
}
