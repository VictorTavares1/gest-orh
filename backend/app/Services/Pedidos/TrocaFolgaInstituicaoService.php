<?php

namespace App\Services\Pedidos;

use App\Actions\Pedido\CriarTrocaFolgaInstituicaoAction;
use App\Models\Pedido;
use App\Models\Utilizador;

class TrocaFolgaInstituicaoService
{
    public function __construct(private readonly CriarTrocaFolgaInstituicaoAction $action) {}

    public function criar(Utilizador $user, array $dados): Pedido
    {
        return $this->action->execute($user, $dados);
    }
}
