<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/src/';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;
    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require $file;
});

use App\Config\Database;
use App\Core\Session;

Session::start();

$db = Database::getInstance();
$ano = date('Y');

echo "<h1>Debug Dashboard Superintendente</h1>";

echo "<h2>1. Query unidadesStats...</h2>";
try {
    $unidadesStats = $db->fetchAll("
        SELECT 
            u.id, u.nome,
            COALESCE(d.valor, 0) as orcamento_distribuido,
            COALESCE(SUM(CASE WHEN e.status = 'executada' THEN e.valor_executado ELSE 0 END), 0) as gasto_total,
            COALESCE(SUM(CASE WHEN e.status IN ('aprovada', 'executada') THEN e.total_horas ELSE 0 END), 0) as horas_total
        FROM unidades u
        LEFT JOIN distribuicao_orcamento d ON u.id = d.unidade_id AND d.ano = :ano
        LEFT JOIN escalas e ON u.id = e.unidade_id AND e.ano = :ano
        GROUP BY u.id, u.nome, d.valor
        ORDER BY u.nome
    ", ['ano' => $ano]);
    echo "<p style='color:green'>Query OK - Total: " . count($unidadesStats) . "</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

echo "<h2>2. Testando include da View...</h2>";
try {
    $titulo = 'Teste';
    $orcamento = ['valor_total' => 0, 'percentual_reserva' => 10];
    $reservaTecnica = 0;
    $valorDisponivel = 0;
    $totalDistribuido = 0;
    $totalGasto = 0;
    $totalUnidades = 0;
    $unidadesStats = [];
    $periodo = 'ano';
    
    ob_start();
    include __DIR__ . '/views/superintendente/dashboard.php';
    $content = ob_get_clean();
    echo "<p style='color:green'>View dashboard.php OK - Tamanho: " . strlen($content) . " bytes</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>Erro na view: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<p style='color:red'>Erro fatal na view: " . $e->getMessage() . "</p>";
    echo "<p>Arquivo: " . $e->getFile() . " Linha: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>3. Testando View::layout...</h2>";
try {
    ob_start();
    App\Core\View::layout('main', 'superintendente/dashboard', [
        'titulo' => 'Dashboard do Superintendente',
        'ano' => $ano,
        'periodo' => 'ano',
        'orcamento' => ['valor_total' => 0, 'percentual_reserva' => 10],
        'reservaTecnica' => 0,
        'valorDisponivel' => 0,
        'totalDistribuido' => 0,
        'totalGasto' => 0,
        'totalUnidades' => 0,
        'unidadesStats' => []
    ]);
    $output = ob_get_clean();
    echo "<p style='color:green'>View::layout OK - Tamanho: " . strlen($output) . " bytes</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>Erro em View::layout: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<p style='color:red'>Erro fatal em View::layout: " . $e->getMessage() . "</p>";
    echo "<p>Arquivo: " . $e->getFile() . " Linha: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
