<?php

namespace App\Models;

use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Utilizador extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasApiTokens, HasRoles;

    protected $table = 'utilizador';
    protected $primaryKey = 'id_utilizador';
    public $timestamps = false;

    protected string $guard_name = 'sanctum';

    protected $fillable = [
        'id_setor',
        'id_tipo_utilizador',
        'email',
        'password_hash',
        'nome',
        'ativo',
    ];

    protected $hidden = ['password_hash'];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function setor(): BelongsTo
    {
        return $this->belongsTo(Setor::class, 'id_setor', 'id_setor');
    }

    public function tipoUtilizador(): BelongsTo
    {
        return $this->belongsTo(TipoUtilizador::class, 'id_tipo_utilizador', 'id_tipo_utilizador');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'id_utilizador', 'id_utilizador');
    }

    public function aprovacoes(): HasMany
    {
        return $this->hasMany(AprovacaoPedido::class, 'id_aprovador', 'id_utilizador');
    }

    public function historicoAcoes(): HasMany
    {
        return $this->hasMany(HistoricoPedido::class, 'id_utilizador_acao', 'id_utilizador');
    }

    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }
}
