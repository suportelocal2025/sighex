<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuperintendenteController;
use App\Http\Controllers\DiretorController;
use App\Http\Controllers\RhController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ServidorController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    Route::get('/perfil', [PerfilController::class, 'index']);
    Route::post('/perfil/atualizar', [PerfilController::class, 'update']);
    Route::post('/perfil/alterar-senha', [PerfilController::class, 'alterarSenha']);
    
    Route::get('/servidores', [ServidorController::class, 'index']);
    Route::get('/servidores/buscar', [ServidorController::class, 'buscar']);
    Route::post('/servidores/alterar-status', [ServidorController::class, 'alterarStatus']);
    Route::post('/servidores/importar-csv', [ServidorController::class, 'importarCsv']);
    Route::post('/servidores/cadastrar', [ServidorController::class, 'cadastrar']);

    Route::prefix('superintendente')->middleware('role:superintendente')->group(function () {
        Route::get('/', [SuperintendenteController::class, 'dashboard']);
        Route::get('/orcamento', [SuperintendenteController::class, 'orcamento']);
        Route::post('/orcamento', [SuperintendenteController::class, 'salvarOrcamento']);
        Route::get('/distribuicao', [SuperintendenteController::class, 'distribuicao']);
        Route::post('/distribuicao', [SuperintendenteController::class, 'salvarDistribuicao']);
        Route::get('/escalas', [SuperintendenteController::class, 'escalas']);
        Route::get('/escala/{id}', [SuperintendenteController::class, 'detalharEscala']);
        Route::get('/alertas', [SuperintendenteController::class, 'alertas']);
        Route::post('/aprovar-escala', [SuperintendenteController::class, 'aprovarEscalaExcedente']);
        Route::post('/rejeitar-escala', [SuperintendenteController::class, 'rejeitarEscalaExcedente']);
        Route::post('/enviar-alerta-email', [SuperintendenteController::class, 'enviarAlertaEmail']);
        Route::get('/relatorios', [SuperintendenteController::class, 'relatorios']);
        Route::get('/relatorio-horas', [SuperintendenteController::class, 'relatorioHoras']);
        Route::get('/relatorio-horas/exportar-excel', [SuperintendenteController::class, 'exportarRelatorioHorasExcel']);
        Route::get('/relatorio-financeiro', [SuperintendenteController::class, 'relatorioFinanceiro']);
        Route::get('/relatorio-financeiro/exportar-excel', [SuperintendenteController::class, 'exportarRelatorioFinanceiroExcel']);
        Route::get('/relatorio-orcamento', [SuperintendenteController::class, 'relatorioOrcamento']);
        Route::get('/relatorio-orcamento/exportar-excel', [SuperintendenteController::class, 'exportarRelatorioOrcamentoExcel']);
        Route::get('/relatorio-escalas', [SuperintendenteController::class, 'relatorioEscalas']);
        Route::get('/relatorio-escalas/exportar-excel', [SuperintendenteController::class, 'exportarRelatorioEscalasExcel']);
    });

    Route::prefix('diretor')->middleware('role:diretor')->group(function () {
        Route::get('/', [DiretorController::class, 'dashboard']);
        Route::get('/escala-mensal', [DiretorController::class, 'escalaMensal']);
        Route::get('/escala/imprimir-mural', [DiretorController::class, 'imprimirMural']);
        Route::get('/alertas', [DiretorController::class, 'alertas']);
        Route::post('/adicionar-servidor', [DiretorController::class, 'adicionarServidor']);
        Route::post('/remover-servidor', [DiretorController::class, 'removerServidor']);
        Route::post('/alocar-dia', [DiretorController::class, 'alocarDia']);
        Route::post('/enviar-aprovacao', [DiretorController::class, 'enviarAprovacao']);
        Route::get('/servidores', [DiretorController::class, 'servidores']);
        Route::get('/servidores-modulo-equipe', [DiretorController::class, 'servidoresModuloEquipe']);
        Route::get('/servidores-escala-anterior', [DiretorController::class, 'servidoresEscalaAnterior']);
        Route::post('/adicionar-servidor-ajax', [DiretorController::class, 'adicionarServidorAjax']);
    });

    Route::prefix('rh')->middleware('role:rh')->group(function () {
        Route::get('/', [RhController::class, 'dashboard']);
        Route::get('/escalas', [RhController::class, 'escalas']);
        Route::get('/escalas/exportar-excel', [RhController::class, 'exportarExcelFiltradas']);
        Route::get('/escala/{id}', [RhController::class, 'detalharEscala']);
        Route::get('/escala/{id}/exportar-excel', [RhController::class, 'exportarExcel']);
        Route::post('/aprovar', [RhController::class, 'aprovarEscala']);
        Route::post('/rejeitar', [RhController::class, 'rejeitarEscala']);
        Route::post('/executar', [RhController::class, 'executarEscala']);
        Route::get('/relatorios', [RhController::class, 'relatorios']);
        Route::get('/relatorio-horas', [RhController::class, 'relatorioHoras']);
        Route::get('/relatorio-horas/exportar-excel', [RhController::class, 'exportarRelatorioHorasExcel']);
        Route::get('/relatorio-financeiro', [RhController::class, 'relatorioFinanceiro']);
        Route::get('/relatorio-financeiro/exportar-excel', [RhController::class, 'exportarRelatorioFinanceiroExcel']);
        Route::get('/servidores', [RhController::class, 'servidores']);
        Route::get('/servidores/buscar', [RhController::class, 'buscarServidores']);
        Route::post('/servidores/alterar-status', [RhController::class, 'alterarStatusServidor']);
        Route::get('/solicitacoes-servidores', [RhController::class, 'solicitacoesServidores']);
        Route::post('/aprovar-solicitacao-servidor', [RhController::class, 'aprovarSolicitacaoServidor']);
        Route::post('/rejeitar-solicitacao-servidor', [RhController::class, 'rejeitarSolicitacaoServidor']);
    });

    Route::prefix('admin')->middleware('role:administrativo')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard']);
        Route::get('/unidades', [AdminController::class, 'unidades']);
        Route::get('/unidade/{id?}', [AdminController::class, 'formUnidade']);
        Route::post('/unidade', [AdminController::class, 'salvarUnidade']);
        Route::delete('/unidade/{id}', [AdminController::class, 'excluirUnidade']);
        Route::post('/unidade/{id}/inabilitar', [AdminController::class, 'excluirUnidade']);
        Route::post('/unidade/{id}/reativar', [AdminController::class, 'reativarUnidade']);
        Route::post('/modulo', [AdminController::class, 'salvarModulo']);
        Route::delete('/modulo/{id}/excluir', [AdminController::class, 'excluirModulo']);
        Route::get('/servidores', [AdminController::class, 'servidores']);
        Route::post('/servidor', [AdminController::class, 'salvarServidor']);
        Route::delete('/servidor/{id}', [AdminController::class, 'excluirServidor']);
        Route::get('/usuarios', [AdminController::class, 'usuarios']);
        Route::post('/usuario', [AdminController::class, 'salvarUsuario']);
        Route::post('/usuario/resetar-senha', [AdminController::class, 'resetarSenha']);
        Route::delete('/usuario/{id}', [AdminController::class, 'excluirUsuario']);
        Route::post('/importar-unidades', [AdminController::class, 'importarUnidades']);
        Route::post('/importar-servidores', [AdminController::class, 'importarServidores']);
        Route::get('/vinculos-modulo-equipe', [AdminController::class, 'vinculosModuloEquipe']);
        Route::post('/vinculo-modulo-equipe', [AdminController::class, 'adicionarVinculoModuloEquipe']);
        Route::delete('/vinculo-modulo-equipe/{id}', [AdminController::class, 'removerVinculoModuloEquipe']);
    });

    Route::prefix('diretor')->middleware('role:diretor')->group(function () {
        Route::post('/solicitar-inclusao-servidor', [DiretorController::class, 'solicitarInclusaoServidor']);
    });
});
