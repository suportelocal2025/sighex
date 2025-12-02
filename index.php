<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

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
    }
});

use App\Config\Database;
use App\Config\Schema;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;
use App\Core\Middleware;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\SuperintendenteController;
use App\Controllers\DiretorController;
use App\Controllers\RhController;
use App\Controllers\AdminController;

Session::start();

try {
    $schema = new Schema();
    $schema->createTables();
    $schema->seedDefaultData();
} catch (Exception $e) {
    // Tables already exist
}

$router = new Router();

$router->get('/login', [AuthController::class, 'showLogin'], [Middleware::guest()]);
$router->post('/login', [AuthController::class, 'login'], [Middleware::guest()]);
$router->get('/logout', [AuthController::class, 'logout']);

$router->get('/', [DashboardController::class, 'index'], [Middleware::auth()]);

$router->get('/superintendente/orcamento', [SuperintendenteController::class, 'orcamento'], [Middleware::superintendente()]);
$router->post('/superintendente/orcamento/salvar', [SuperintendenteController::class, 'salvarOrcamento'], [Middleware::superintendente()]);
$router->get('/superintendente/distribuicao', [SuperintendenteController::class, 'distribuicao'], [Middleware::superintendente()]);
$router->post('/superintendente/distribuicao/salvar', [SuperintendenteController::class, 'salvarDistribuicao'], [Middleware::superintendente()]);
$router->get('/superintendente/distribuicao/historico', [SuperintendenteController::class, 'historicoDistribuicao'], [Middleware::superintendente()]);
$router->get('/superintendente/relatorios', [SuperintendenteController::class, 'relatorios'], [Middleware::superintendente()]);

$router->get('/diretor/escala-mensal', [DiretorController::class, 'escalaMensal'], [Middleware::diretor()]);
$router->post('/diretor/escala/salvar-alocacao', [DiretorController::class, 'salvarAlocacao'], [Middleware::diretor()]);
$router->post('/diretor/escala/remover-alocacao', [DiretorController::class, 'removerAlocacao'], [Middleware::diretor()]);
$router->get('/diretor/escala/verificar-alocacao', [DiretorController::class, 'verificarAlocacao'], [Middleware::diretor()]);
$router->get('/diretor/enviar-escala', [DiretorController::class, 'enviarEscala'], [Middleware::diretor()]);
$router->post('/diretor/escala/confirmar-envio', [DiretorController::class, 'confirmarEnvioEscala'], [Middleware::diretor()]);
$router->get('/diretor/servidores', [DiretorController::class, 'servidores'], [Middleware::diretor()]);
$router->get('/diretor/escala/reabrir', [DiretorController::class, 'reabrirEscala'], [Middleware::diretor()]);
$router->get('/diretor/escala/servidores-disponiveis', [DiretorController::class, 'listarServidoresDisponiveis'], [Middleware::diretor()]);
$router->post('/diretor/escala/adicionar-servidor-equipe', [DiretorController::class, 'adicionarServidorEquipe'], [Middleware::diretor()]);
$router->post('/diretor/escala/remover-servidor-equipe', [DiretorController::class, 'removerServidorEquipe'], [Middleware::diretor()]);
$router->get('/diretor/escala/servidores-equipe', [DiretorController::class, 'listarServidoresEquipe'], [Middleware::diretor()]);
$router->post('/diretor/escala/atualizar-lider', [DiretorController::class, 'atualizarLiderEquipe'], [Middleware::diretor()]);
$router->get('/diretor/escala/imprimir-mural', [DiretorController::class, 'imprimirMural'], [Middleware::diretor()]);

$router->get('/rh/escalas', [RhController::class, 'escalas'], [Middleware::rh()]);
$router->get('/rh/escalas/{id}', [RhController::class, 'detalharEscala'], [Middleware::rh()]);
$router->get('/rh/escalas/{id}/exportar-excel', [RhController::class, 'exportarEscalaExcel'], [Middleware::rh()]);
$router->post('/rh/escalas/aprovar', [RhController::class, 'aprovarEscala'], [Middleware::rh()]);
$router->post('/rh/escalas/rejeitar', [RhController::class, 'rejeitarEscala'], [Middleware::rh()]);
$router->post('/rh/escalas/executar', [RhController::class, 'executarEscala'], [Middleware::rh()]);
$router->get('/rh/relatorios', [RhController::class, 'relatorios'], [Middleware::rh()]);
$router->get('/rh/relatorios/gerar', [RhController::class, 'gerarRelatorio'], [Middleware::rh()]);

$router->get('/admin/unidades', [AdminController::class, 'unidades'], [Middleware::administrativo()]);
$router->get('/admin/unidades/nova', [AdminController::class, 'novaUnidade'], [Middleware::administrativo()]);
$router->post('/admin/unidades/salvar', [AdminController::class, 'salvarUnidade'], [Middleware::administrativo()]);
$router->get('/admin/unidades/{id}/editar', [AdminController::class, 'editarUnidade'], [Middleware::administrativo()]);
$router->get('/admin/unidades/{id}/excluir', [AdminController::class, 'excluirUnidade'], [Middleware::administrativo()]);
$router->post('/admin/unidades/importar', [AdminController::class, 'importarUnidades'], [Middleware::administrativo()]);
$router->post('/admin/modulos/adicionar', [AdminController::class, 'adicionarModulo'], [Middleware::administrativo()]);
$router->post('/admin/modulos/remover', [AdminController::class, 'removerModulo'], [Middleware::administrativo()]);
$router->get('/admin/servidores', [AdminController::class, 'servidores'], [Middleware::administrativo()]);
$router->post('/admin/servidores/salvar', [AdminController::class, 'salvarServidor'], [Middleware::administrativo()]);
$router->post('/admin/servidores/excluir', [AdminController::class, 'excluirServidor'], [Middleware::administrativo()]);
$router->post('/admin/servidores/importar', [AdminController::class, 'importarServidores'], [Middleware::administrativo()]);

$router->dispatch();
