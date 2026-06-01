<?php

namespace App\Services\Pedidos;

use App\Actions\Pedido\CriarJustificacaoFaltasAction;
use App\Models\Pedido;
use App\Models\Utilizador;

class JustificacaoFaltasService
{
    public function __construct(private readonly CriarJustificacaoFaltasAction $action) {}

    public function criar(Utilizador $user, array $dados): Pedido
    {
        return $this->action->execute($user, $dados);
    }
}
