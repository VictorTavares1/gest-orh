<?php

namespace App\Services\Pedidos;

use App\Actions\Pedido\CriarLicencaNojoAction;
use App\Models\Pedido;
use App\Models\Utilizador;

class LicencaNojoService
{
    public function __construct(private readonly CriarLicencaNojoAction $action) {}

    public function criar(Utilizador $user, array $dados): Pedido
    {
        return $this->action->execute($user, $dados);
    }
}
