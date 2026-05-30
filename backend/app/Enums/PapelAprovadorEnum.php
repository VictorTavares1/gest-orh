<?php

namespace App\Enums;

enum PapelAprovadorEnum: string
{
    case COLEGA              = 'COLEGA';
    case DIRETOR_TECNICO     = 'DIRETOR_TECNICO';
    case DIRETORA_EXECUTIVA  = 'DIRETORA_EXECUTIVA';

    public function label(): string
    {
        return match($this) {
            self::COLEGA             => 'Colega',
            self::DIRETOR_TECNICO    => 'Diretor Técnico',
            self::DIRETORA_EXECUTIVA => 'Diretora Executiva',
        };
    }

    public function ordem(): int
    {
        return match($this) {
            self::COLEGA             => 1,
            self::DIRETOR_TECNICO    => 2,
            self::DIRETORA_EXECUTIVA => 3,
        };
    }
}
