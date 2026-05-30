<?php

namespace App\Exceptions;

use RuntimeException;

class PedidoException extends RuntimeException
{
    public static function naoPodeSerAlterado(): self
    {
        return new self('Este pedido não pode ser alterado no estado atual.');
    }

    public static function naoPodeSerCancelado(): self
    {
        return new self('Este pedido não pode ser cancelado no estado atual.');
    }
}
