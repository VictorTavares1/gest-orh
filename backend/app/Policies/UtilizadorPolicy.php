<?php

namespace App\Policies;

use App\Models\Utilizador;

class UtilizadorPolicy
{
    public function viewAny(Utilizador $user): bool
    {
        return $user->can('utilizadores.ver');
    }

    public function view(Utilizador $user, Utilizador $utilizador): bool
    {
        return $user->id_utilizador === $utilizador->id_utilizador
            || $user->can('utilizadores.ver');
    }

    public function create(Utilizador $user): bool
    {
        return $user->can('utilizadores.gerir');
    }

    public function update(Utilizador $user, Utilizador $utilizador): bool
    {
        return $user->id_utilizador === $utilizador->id_utilizador
            || $user->can('utilizadores.gerir');
    }

    public function delete(Utilizador $user, Utilizador $utilizador): bool
    {
        return $user->can('utilizadores.gerir')
            && $user->id_utilizador !== $utilizador->id_utilizador;
    }

    public function ativar(Utilizador $user, Utilizador $utilizador): bool
    {
        return $user->can('utilizadores.gerir');
    }

    public function desativar(Utilizador $user, Utilizador $utilizador): bool
    {
        return $user->can('utilizadores.gerir')
            && $user->id_utilizador !== $utilizador->id_utilizador;
    }
}
