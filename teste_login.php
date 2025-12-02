<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste de Login SIGEEX</h1>";

$configFile = __DIR__ . '/config/database.php';

if (!file_exists($configFile)) {
    die("<p style='color:red'>Arquivo config/database.php não encontrado!</p>");
}

$config = require $configFile;

echo "<h2>1. Conectando ao banco...</h2>";
try {
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "<p style='color:green'>Conexão OK!</p>";
} catch (PDOException $e) {
    die("<p style='color:red'>Erro de conexão: " . $e->getMessage() . "</p>");
}

echo "<h2>2. Buscando usuários...</h2>";
$usuarios = $pdo->query("SELECT id, nome, email, senha, papel, ativo FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Papel</th><th>Ativo</th><th>Hash da Senha</th></tr>";
foreach ($usuarios as $u) {
    echo "<tr>";
    echo "<td>{$u['id']}</td>";
    echo "<td>{$u['nome']}</td>";
    echo "<td>{$u['email']}</td>";
    echo "<td>{$u['papel']}</td>";
    echo "<td>{$u['ativo']}</td>";
    echo "<td style='font-size:10px'>" . substr($u['senha'], 0, 30) . "...</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>3. Testando senha 'admin123'...</h2>";

$senhaTestar = 'admin123';
$hashCorreto = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

echo "<p>Verificando se 'admin123' bate com o hash padrão: ";
if (password_verify($senhaTestar, $hashCorreto)) {
    echo "<span style='color:green'>SIM!</span></p>";
} else {
    echo "<span style='color:red'>NÃO!</span></p>";
}

foreach ($usuarios as $u) {
    echo "<p>Testando senha para {$u['email']}: ";
    if (password_verify($senhaTestar, $u['senha'])) {
        echo "<span style='color:green'>SENHA CORRETA!</span>";
    } else {
        echo "<span style='color:red'>SENHA INCORRETA</span>";
        echo " - Hash no banco: " . substr($u['senha'], 0, 20) . "...";
    }
    echo "</p>";
}

echo "<h2>4. Testando query do login...</h2>";
$email = 'super@sistema.gov.br';
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email AND ativo = 1");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "<p style='color:green'>Usuário encontrado com query 'ativo = 1'</p>";
    echo "<pre>" . print_r($user, true) . "</pre>";
} else {
    echo "<p style='color:red'>Usuário NÃO encontrado! Testando sem filtro de ativo...</p>";
    
    $stmt2 = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt2->execute(['email' => $email]);
    $user2 = $stmt2->fetch(PDO::FETCH_ASSOC);
    
    if ($user2) {
        echo "<p>Usuário existe mas ativo = {$user2['ativo']}</p>";
    } else {
        echo "<p style='color:red'>Usuário realmente não existe no banco!</p>";
    }
}
