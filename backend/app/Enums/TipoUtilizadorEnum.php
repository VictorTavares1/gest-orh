<?php

namespace App\Enums;

enum TipoUtilizadorEnum: string
{
    case FUNCIONARIO         = 'FUNCIONARIO';
    case DIRETOR_TECNICO     = 'DIRETOR_TECNICO';
    case DIRETORA_EXECUTIVA  = 'DIRETORA_EXECUTIVA';
    case SUBSTITUTA          = 'SUBSTITUTA';

    public function label(): string
    {
        return match($this) {
            self::FUNCIONARIO        => 'Funcionário',
            self::DIRETOR_TECNICO    => 'Diretor Técnico',
            self::DIRETORA_EXECUTIVA => 'Diretora Executiva',
            self::SUBSTITUTA         => 'Substituta da Diretora',
        };
    }

    public function roleSpatie(): string
    {
        return match($this) {
            self::FUNCIONARIO        => 'funcionario',
            self::DIRETOR_TECNICO    => 'diretor_tecnico',
            self::DIRETORA_EXECUTIVA => 'diretora_executiva',
            self::SUBSTITUTA         => 'substituta',
        };
    }

    public function podeAprovarComo(): array
    {
        return match($this) {
            self::FUNCIONARIO        => [PapelAprovadorEnum::COLEGA],
            self::DIRETOR_TECNICO    => [PapelAprovadorEnum::COLEGA],
            self::DIRETORA_EXECUTIVA => PapelAprovadorEnum::cases(),
            self::SUBSTITUTA         => PapelAprovadorEnum::cases(),
        };
    }
}
