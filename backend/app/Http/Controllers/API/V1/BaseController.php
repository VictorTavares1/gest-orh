<?php

namespace App\Http\Controllers\API\V1;

use App\Traits\ApiResponseTrait;
use App\Traits\HasAuditoria;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;

/**
 * @OA\Info(
 *     title="Gestão RH API",
 *     version="1.0.0",
 *     description="API REST para sistema de Gestão de Recursos Humanos.",
 *     @OA\Contact(email="suporte@gestaorh.pt")
 * )
 * @OA\Server(url=L5_SWAGGER_CONST_HOST, description="API v1")
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Token"
 * )
 */
abstract class BaseController extends Controller
{
    use AuthorizesRequests;
    use ApiResponseTrait;
    use HasAuditoria;
}
