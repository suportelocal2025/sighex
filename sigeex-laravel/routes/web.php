<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuperintendenteController;
use App\Http\Controllers\DiretorController;
use App\Http\Controllers\RhController;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::prefix('superintendente')->middleware('role:superintendente')->group(function () {
        Route::get('/', [SuperintendenteController::class, 'dashboard']);
        Route::get('/orcamento', [SuperintendenteController::class, 'orcamento']);
        Route::post('/orcamento', [SuperintendenteController::class, 'salvarOrcamento']);
        Route::get('/distribuicao', [SuperintendenteController::class, 'distribuicao']);
        Route::post('/distribuicao', [SuperintendenteController::class, 'salvarDistribuicao']);
        Route::get('/relatorios', [SuperintendenteController::class, 'relatorios']);
    });

    Route::prefix('diretor')->middleware('role:diretor')->group(function () {
        Route::get('/', [DiretorController::class, 'dashboard']);
        Route::get('/escala-mensal', [DiretorController::class, 'escalaMensal']);
        Route::post('/adicionar-servidor', [DiretorController::class, 'adicionarServidor']);
        Route::post('/remover-servidor', [DiretorController::class, 'removerServidor']);
        Route::post('/alocar-dia', [DiretorController::class, 'alocarDia']);
        Route::post('/enviar-aprovacao', [DiretorController::class, 'enviarAprovacao']);
        Route::get('/servidores', [DiretorController::class, 'servidores']);
    });

    Route::prefix('rh')->middleware('role:rh')->group(function () {
        Route::get('/', [RhController::class, 'dashboard']);
        Route::get('/escalas', [RhController::class, 'escalas']);
        Route::get('/escala/{id}', [RhController::class, 'detalharEscala']);
        Route::post('/aprovar', [RhController::class, 'aprovarEscala']);
        Route::post('/rejeitar', [RhController::class, 'rejeitarEscala']);
        Route::post('/executar', [RhController::class, 'executarEscala']);
        Route::get('/relatorios', [RhController::class, 'relatorios']);
    });

    Route::prefix('admin')->middleware('role:administrativo')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard']);
        Route::get('/unidades', [AdminController::class, 'unidades']);
        Route::get('/unidade/{id?}', [AdminController::class, 'formUnidade']);
        Route::post('/unidade', [AdminController::class, 'salvarUnidade']);
        Route::delete('/unidade/{id}', [AdminController::class, 'excluirUnidade']);
        Route::get('/servidores', [AdminController::class, 'servidores']);
        Route::post('/servidor', [AdminController::class, 'salvarServidor']);
        Route::delete('/servidor/{id}', [AdminController::class, 'excluirServidor']);
        Route::get('/usuarios', [AdminController::class, 'usuarios']);
        Route::post('/usuario', [AdminController::class, 'salvarUsuario']);
        Route::post('/usuario/resetar-senha', [AdminController::class, 'resetarSenha']);
        Route::delete('/usuario/{id}', [AdminController::class, 'excluirUsuario']);
    });
});
