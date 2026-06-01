<?php

namespace App\Services\Pedidos;

use App\Actions\Pedido\CriarMarcacaoFeriasAction;
use App\Models\Pedido;
use App\Models\Utilizador;

class MarcacaoFeriasService
{
    public function __construct(private readonly CriarMarcacaoFeriasAction $action) {}

    public function criar(Utilizador $user, array $dados): Pedido
    {
        return $this->action->execute($user, $dados);
    }
}
