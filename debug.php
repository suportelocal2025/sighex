<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug SIGEEX</h1>";

echo "<h2>1. Testando autoload...</h2>";
try {
    spl_autoload_register(function ($class) {
        $prefix = 'App\\';
        $baseDir = __DIR__ . '/src/';
        
        if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
            return;
        }
        
        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        
        if (file_exists($file)) {
            require $file;
        } else {
            echo "<p style='color:red'>Arquivo não encontrado: {$file}</p>";
        }
    });
    echo "<p style='color:green'>Autoload OK</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

echo "<h2>2. Testando Database...</h2>";
try {
    $db = App\Config\Database::getInstance();
    echo "<p style='color:green'>Database OK - Driver: " . $db->getDriver() . "</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

echo "<h2>3. Testando Session...</h2>";
try {
    App\Core\Session::start();
    echo "<p style='color:green'>Session OK</p>";
    echo "<p>Usuário logado: " . (App\Core\Session::isLoggedIn() ? 'SIM' : 'NÃO') . "</p>";
    if (App\Core\Session::isLoggedIn()) {
        echo "<p>Papel: " . App\Core\Session::getUserPapel() . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

echo "<h2>4. Testando query do Dashboard Superintendente...</h2>";
try {
    $ano = date('Y');
    
    $orcamento = $db->fetch(
        "SELECT * FROM orcamento_global WHERE ano = :ano",
        ['ano' => $ano]
    );
    echo "<p style='color:green'>Query orcamento_global OK</p>";
    echo "<pre>" . print_r($orcamento, true) . "</pre>";
    
    $unidades = $db->fetchAll("SELECT * FROM unidades ORDER BY nome");
    echo "<p style='color:green'>Query unidades OK - Total: " . count($unidades) . "</p>";
    
    $totalDistribuido = $db->fetch("
        SELECT COALESCE(SUM(valor), 0) as total 
        FROM distribuicao_orcamento 
        WHERE ano = :ano
    ", ['ano' => $ano]);
    echo "<p style='color:green'>Query distribuicao OK</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>Erro na query: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>5. Testando View...</h2>";
try {
    $viewFile = __DIR__ . '/views/layouts/main.php';
    echo "<p>Arquivo main.php existe: " . (file_exists($viewFile) ? 'SIM' : 'NÃO') . "</p>";
    
    $dashFile = __DIR__ . '/views/superintendente/dashboard.php';
    echo "<p>Arquivo dashboard.php existe: " . (file_exists($dashFile) ? 'SIM' : 'NÃO') . "</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

echo "<h2>6. Verificando arquivos de view...</h2>";
$views = [
    'views/layouts/main.php',
    'views/auth/login.php',
    'views/superintendente/dashboard.php',
    'views/superintendente/orcamento.php',
    'views/superintendente/distribuicao.php',
    'views/diretor/dashboard.php',
    'views/rh/dashboard.php',
    'views/administrativo/dashboard.php'
];

foreach ($views as $v) {
    $exists = file_exists(__DIR__ . '/' . $v);
    $color = $exists ? 'green' : 'red';
    echo "<p style='color:{$color}'>{$v}: " . ($exists ? 'OK' : 'NÃO ENCONTRADO') . "</p>";
}
