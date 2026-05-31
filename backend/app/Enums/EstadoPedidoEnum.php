<?php

namespace App\Enums;

enum EstadoPedidoEnum: string
{
    case RASCUNHO                   = 'RASCUNHO';
    case PENDENTE                   = 'PENDENTE';
    case EM_APROVACAO_COLEGA        = 'EM_APROVACAO_COLEGA';
    case EM_APROVACAO_DIRETOR       = 'EM_APROVACAO_DIRETOR';
    case EM_APROVACAO_EXECUTIVA     = 'EM_APROVACAO_EXECUTIVA';
    case APROVADO                   = 'APROVADO';
    case REJEITADO                  = 'REJEITADO';
    case CANCELADO                  = 'CANCELADO';

    public function label(): string
    {
        return match($this) {
            self::RASCUNHO               => 'Rascunho',
            self::PENDENTE               => 'Pendente',
            self::EM_APROVACAO_COLEGA    => 'Em aprovação (Colega)',
            self::EM_APROVACAO_DIRETOR   => 'Em aprovação (Diretor Técnico)',
            self::EM_APROVACAO_EXECUTIVA => 'Em aprovação (Diretora Executiva)',
            self::APROVADO               => 'Aprovado',
            self::REJEITADO              => 'Rejeitado',
            self::CANCELADO              => 'Cancelado',
        };
    }

    public function eTerminal(): bool
    {
        return in_array($this, [self::APROVADO, self::REJEITADO, self::CANCELADO]);
    }

    public function transicoesPermitidas(): array
    {
        return match($this) {
            self::RASCUNHO               => [self::PENDENTE, self::CANCELADO],
            self::PENDENTE               => [self::EM_APROVACAO_COLEGA, self::EM_APROVACAO_EXECUTIVA, self::CANCELADO, self::REJEITADO],
            self::EM_APROVACAO_COLEGA    => [self::EM_APROVACAO_EXECUTIVA, self::REJEITADO, self::CANCELADO],
            self::EM_APROVACAO_DIRETOR   => [self::EM_APROVACAO_EXECUTIVA, self::APROVADO, self::REJEITADO, self::CANCELADO],
            self::EM_APROVACAO_EXECUTIVA => [self::APROVADO, self::REJEITADO, self::CANCELADO, self::RASCUNHO],
            default                      => [],
        };
    }

    public function podeTransicionarPara(self $destino): bool
    {
        return in_array($destino, $this->transicoesPermitidas());
    }
}
