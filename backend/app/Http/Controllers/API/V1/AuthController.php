<?php

namespace App\Http\Controllers\API\V1;

use App\DTOs\Auth\LoginDTO;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UtilizadorResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends BaseController
{
    public function __construct(private readonly AuthService $authService) {}

    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     summary="Autenticar utilizador",
     *     tags={"Autenticação"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@gestaorh.pt"),
     *             @OA\Property(property="password", type="string", example="Admin@2024")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login efetuado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string"),
     *                 @OA\Property(property="utilizador", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Credenciais inválidas"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(LoginDTO::fromRequest($request->validated()));

        return $this->success([
            'token'      => $result['token'],
            'utilizador' => new UtilizadorResource($result['utilizador']),
        ], 'Login efetuado com sucesso.');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     summary="Terminar sessão",
     *     tags={"Autenticação"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=204, description="Sessão terminada"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->noContent();
    }

    /**
     * @OA\Get(
     *     path="/api/v1/auth/me",
     *     summary="Perfil do utilizador autenticado",
     *     tags={"Autenticação"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dados do utilizador autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function me(Request $request): JsonResponse
    {
        $utilizador = $request->user()->load(['tipoUtilizador', 'setor.organizacao', 'roles', 'permissions']);

        return $this->success(new UtilizadorResource($utilizador));
    }
}
