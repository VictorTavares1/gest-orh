<?php

namespace App\Exceptions;

use App\Enums\EstadoPedidoEnum;
use RuntimeException;

class WorkflowException extends RuntimeException
{
    public static function transicaoInvalida(EstadoPedidoEnum $origem, EstadoPedidoEnum $destino): self
    {
        return new self("Transição inválida: '{$origem->label()}' → '{$destino->label()}'.");
    }

    public static function pedidoTerminal(): self
    {
        return new self('Este pedido já se encontra num estado terminal e não pode ser alterado.');
    }
}
