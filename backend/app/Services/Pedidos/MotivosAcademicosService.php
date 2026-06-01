<?php

namespace App\Services\Pedidos;

use App\Actions\Pedido\CriarMotivosAcademicosAction;
use App\Models\Pedido;
use App\Models\Utilizador;

class MotivosAcademicosService
{
    public function __construct(private readonly CriarMotivosAcademicosAction $action) {}

    public function criar(Utilizador $user, array $dados): Pedido
    {
        return $this->action->execute($user, $dados);
    }
}
