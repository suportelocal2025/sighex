<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnóstico SIGEEX</h1>";

echo "<h2>1. Versão do PHP</h2>";
echo "PHP Version: " . phpversion() . "<br>";

echo "<h2>2. Extensões PDO</h2>";
echo "PDO disponível: " . (extension_loaded('pdo') ? 'SIM' : 'NÃO') . "<br>";
echo "PDO MySQL disponível: " . (extension_loaded('pdo_mysql') ? 'SIM' : 'NÃO') . "<br>";

echo "<h2>3. Arquivos do Sistema</h2>";
$files = [
    'index.php',
    'config/database.php',
    'src/Config/Database.php',
    'src/Config/Schema.php',
    'src/Core/Router.php',
    'src/Core/Session.php',
    'src/Core/View.php',
    'src/Controllers/AuthController.php',
    'views/auth/login.php',
    'views/layouts/main.php'
];

foreach ($files as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    $color = $exists ? 'green' : 'red';
    echo "<span style='color:{$color}'>{$file}: " . ($exists ? 'OK' : 'NÃO ENCONTRADO') . "</span><br>";
}

echo "<h2>4. Configuração do Banco de Dados</h2>";
$configFile = __DIR__ . '/config/database.php';
if (file_exists($configFile)) {
    $config = require $configFile;
    echo "Host: " . $config['host'] . "<br>";
    echo "Database: " . $config['database'] . "<br>";
    echo "Username: " . $config['username'] . "<br>";
    echo "Senha: " . (strlen($config['password']) > 0 ? '***definida***' : 'VAZIA') . "<br>";
    
    echo "<h2>5. Teste de Conexão MySQL</h2>";
    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo "<span style='color:green'>Conexão MySQL: SUCESSO!</span><br>";
        
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "Tabelas encontradas: " . count($tables) . "<br>";
        if (count($tables) > 0) {
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>{$table}</li>";
            }
            echo "</ul>";
        } else {
            echo "<span style='color:orange'>Nenhuma tabela encontrada. Importe o arquivo database_mysql.sql no phpMyAdmin.</span><br>";
        }
    } catch (PDOException $e) {
        echo "<span style='color:red'>Erro de conexão: " . $e->getMessage() . "</span><br>";
    }
} else {
    echo "<span style='color:red'>Arquivo config/database.php não encontrado!</span><br>";
}

echo "<h2>6. Variáveis de Ambiente</h2>";
echo "PGHOST definido: " . (getenv('PGHOST') ? 'SIM' : 'NÃO') . "<br>";
echo "Será usado: " . (getenv('PGHOST') ? 'PostgreSQL' : 'MySQL') . "<br>";

echo "<h2>7. Diretório Atual</h2>";
echo "Diretório: " . __DIR__ . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
