<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler
{
    public function register(Exceptions $exceptions): void
    {
        $exceptions->render(function (Throwable $e, Request $request): ?JsonResponse {
            if (!$request->expectsJson()) {
                return null;
            }

            return match (true) {
                $e instanceof ValidationException     => $this->handleValidation($e),
                $e instanceof AuthenticationException => $this->handleAuthentication(),
                $e instanceof ModelNotFoundException,
                $e instanceof NotFoundHttpException   => $this->handleNotFound($e),
                $e instanceof UnauthorizedException   => $this->handleForbidden(),
                $e instanceof WorkflowException       => $this->handleWorkflow($e),
                $e instanceof PedidoException         => $this->handlePedido($e),
                $e instanceof HttpException           => $this->handleHttp($e),
                default                               => $this->handleGeneric($e),
            };
        });
    }

    private function handleValidation(ValidationException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Dados inválidos.',
            'errors'  => $e->errors(),
        ], 422);
    }

    private function handleAuthentication(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Não autenticado. Faça login para continuar.',
        ], 401);
    }

    private function handleNotFound(Throwable $e): JsonResponse
    {
        $resource = $e instanceof ModelNotFoundException
            ? class_basename($e->getModel())
            : 'Recurso';

        return response()->json([
            'success' => false,
            'message' => "{$resource} não encontrado.",
        ], 404);
    }

    private function handleForbidden(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Sem permissão para executar esta ação.',
        ], 403);
    }

    private function handleWorkflow(WorkflowException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 422);
    }

    private function handlePedido(PedidoException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 422);
    }

    private function handleHttp(HttpException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage() ?: 'Erro HTTP.',
        ], $e->getStatusCode());
    }

    private function handleGeneric(Throwable $e): JsonResponse
    {
        $debug = config('app.debug');

        return response()->json([
            'success' => false,
            'message' => $debug ? $e->getMessage() : 'Erro interno. Tente novamente.',
            'debug'   => $debug ? [
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ] : null,
        ], 500);
    }
}
