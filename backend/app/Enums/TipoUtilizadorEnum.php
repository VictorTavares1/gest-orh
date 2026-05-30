<?php

namespace App\Enums;

enum TipoUtilizadorEnum: string
{
    case FUNCIONARIO         = 'FUNCIONARIO';
    case DIRETOR_TECNICO     = 'DIRETOR_TECNICO';
    case DIRETORA_EXECUTIVA  = 'DIRETORA_EXECUTIVA';

    public function label(): string
    {
        return match($this) {
            self::FUNCIONARIO        => 'Funcionário',
            self::DIRETOR_TECNICO    => 'Diretor Técnico',
            self::DIRETORA_EXECUTIVA => 'Diretora Executiva',
        };
    }

    public function roleSpatie(): string
    {
        return match($this) {
            self::FUNCIONARIO        => 'funcionario',
            self::DIRETOR_TECNICO    => 'diretor_tecnico',
            self::DIRETORA_EXECUTIVA => 'diretora_executiva',
        };
    }

    public function podeAprovarComo(): array
    {
        return match($this) {
            self::FUNCIONARIO        => [PapelAprovadorEnum::COLEGA],
            self::DIRETOR_TECNICO    => [PapelAprovadorEnum::COLEGA, PapelAprovadorEnum::DIRETOR_TECNICO],
            self::DIRETORA_EXECUTIVA => PapelAprovadorEnum::cases(),
        };
    }
}
