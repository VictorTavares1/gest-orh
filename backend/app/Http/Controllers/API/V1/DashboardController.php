<?php

namespace App\Http\Controllers\API\V1;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends BaseController
{
    public function __construct(private readonly DashboardService $dashboardService) {}

    public function resumo(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->loadMissing(['setor', 'roles', 'permissions']);

        $dados = $this->dashboardService->resumo($user);

        return $this->success($dados);
    }
}
