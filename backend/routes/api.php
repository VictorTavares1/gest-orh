<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Gestão RH v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->name('v1.')->group(function () {

    // Saúde / ping
    Route::get('/ping', fn () => response()->json([
        'success'   => true,
        'message'   => 'API Gestão RH v1 operacional.',
        'timestamp' => now()->toIso8601String(),
    ]))->name('ping');

    // ─── Autenticação (pública) ─────────────────────────────────────────
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/login', [\App\Http\Controllers\API\V1\AuthController::class, 'login'])
            ->name('login');
    });

    // ─── Rotas protegidas ───────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('auth')->name('auth.')->group(function () {
            Route::post('/logout', [\App\Http\Controllers\API\V1\AuthController::class, 'logout'])->name('logout');
            Route::get('/me', [\App\Http\Controllers\API\V1\AuthController::class, 'me'])->name('me');
        });

        // Referência
        Route::get('/tipos-pedido', [\App\Http\Controllers\API\V1\TipoPedidoController::class, 'index'])->name('tipos-pedido.index');
        Route::get('/estados-pedido', [\App\Http\Controllers\API\V1\EstadoPedidoController::class, 'index'])->name('estados-pedido.index');
        Route::get('/periodos', [\App\Http\Controllers\API\V1\PeriodoController::class, 'index'])->name('periodos.index');

        // Administração
        Route::middleware('role:diretora_executiva')->group(function () {
            Route::apiResource('/organizacoes', \App\Http\Controllers\API\V1\OrganizacaoController::class);
            Route::apiResource('/setores', \App\Http\Controllers\API\V1\SetorController::class);
            Route::apiResource('/utilizadores', \App\Http\Controllers\API\V1\UtilizadorController::class);
            Route::patch('/utilizadores/{utilizador}/ativar', [\App\Http\Controllers\API\V1\UtilizadorController::class, 'ativar'])->name('utilizadores.ativar');
            Route::patch('/utilizadores/{utilizador}/desativar', [\App\Http\Controllers\API\V1\UtilizadorController::class, 'desativar'])->name('utilizadores.desativar');
            Route::apiResource('/periodos', \App\Http\Controllers\API\V1\PeriodoController::class)->except(['index']);
        });

        // Pedidos
        Route::prefix('pedidos')->name('pedidos.')->group(function () {
            Route::get('/', [\App\Http\Controllers\API\V1\PedidoController::class, 'index'])->name('index');
            Route::get('/{pedido}', [\App\Http\Controllers\API\V1\PedidoController::class, 'show'])->name('show');
            Route::delete('/{pedido}', [\App\Http\Controllers\API\V1\PedidoController::class, 'destroy'])->name('destroy');
            Route::get('/{pedido}/historico', [\App\Http\Controllers\API\V1\PedidoController::class, 'historico'])->name('historico');
            Route::get('/{pedido}/anexos', [\App\Http\Controllers\API\V1\AnexoController::class, 'index'])->name('anexos.index');
            Route::post('/{pedido}/anexos', [\App\Http\Controllers\API\V1\AnexoController::class, 'store'])->name('anexos.store');
            Route::post('/{pedido}/submeter', [\App\Http\Controllers\API\V1\AprovacaoController::class, 'submeter'])->name('submeter');
            Route::post('/{pedido}/aprovar', [\App\Http\Controllers\API\V1\AprovacaoController::class, 'aprovar'])->name('aprovar');
            Route::post('/{pedido}/rejeitar', [\App\Http\Controllers\API\V1\AprovacaoController::class, 'rejeitar'])->name('rejeitar');

            // Criação por tipo
            Route::post('/horas-extras', [\App\Http\Controllers\API\V1\Pedidos\HorasExtrasController::class, 'store'])->name('horas-extras.store');
            Route::post('/justificacao-faltas', [\App\Http\Controllers\API\V1\Pedidos\JustificacaoFaltasController::class, 'store'])->name('justificacao-faltas.store');
            Route::post('/marcacao-ferias', [\App\Http\Controllers\API\V1\Pedidos\MarcacaoFeriasController::class, 'store'])->name('marcacao-ferias.store');
            Route::post('/alteracao-ferias', [\App\Http\Controllers\API\V1\Pedidos\AlteracaoFeriasController::class, 'store'])->name('alteracao-ferias.store');
            Route::post('/troca-horario', [\App\Http\Controllers\API\V1\Pedidos\TrocaHorarioController::class, 'store'])->name('troca-horario.store');
            Route::post('/troca-folga-instituicao', [\App\Http\Controllers\API\V1\Pedidos\TrocaFolgaInstituicaoController::class, 'store'])->name('troca-folga-instituicao.store');
            Route::post('/interrupcao-atividade', [\App\Http\Controllers\API\V1\Pedidos\InterrupcaoAtividadeController::class, 'store'])->name('interrupcao-atividade.store');
            Route::post('/folga-aniversario', [\App\Http\Controllers\API\V1\Pedidos\FolgaAniversarioController::class, 'store'])->name('folga-aniversario.store');
            Route::post('/assiduidade', [\App\Http\Controllers\API\V1\Pedidos\AssiduidadeController::class, 'store'])->name('assiduidade.store');
            Route::post('/licenca-nojo', [\App\Http\Controllers\API\V1\Pedidos\LicencaNojoController::class, 'store'])->name('licenca-nojo.store');
            Route::post('/formacao', [\App\Http\Controllers\API\V1\Pedidos\FormacaoController::class, 'store'])->name('formacao.store');
            Route::post('/motivos-academicos', [\App\Http\Controllers\API\V1\Pedidos\MotivosAcademicosController::class, 'store'])->name('motivos-academicos.store');
            Route::post('/comp-entrada-tardia', [\App\Http\Controllers\API\V1\Pedidos\CompEntradaTardiaController::class, 'store'])->name('comp-entrada-tardia.store');
            Route::post('/comp-saida-antecipada', [\App\Http\Controllers\API\V1\Pedidos\CompSaidaAntecipadaController::class, 'store'])->name('comp-saida-antecipada.store');
        });

        Route::delete('/anexos/{anexo}', [\App\Http\Controllers\API\V1\AnexoController::class, 'destroy'])->name('anexos.destroy');
        Route::get('/anexos/{anexo}/download', [\App\Http\Controllers\API\V1\AnexoController::class, 'download'])->name('anexos.download');
        Route::get('/dashboard/resumo', [\App\Http\Controllers\API\V1\DashboardController::class, 'resumo'])->name('dashboard.resumo');
    });
});
