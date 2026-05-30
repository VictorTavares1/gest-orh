<?php

namespace App\Models;

use App\Enums\TipoUtilizadorEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoUtilizador extends Model
{
    protected $table = 'tipo_utilizador';
    protected $primaryKey = 'id_tipo_utilizador';
    public $timestamps = false;

    protected $fillable = ['nome'];

    protected $casts = [
        'nome' => TipoUtilizadorEnum::class,
    ];

    public function utilizadores(): HasMany
    {
        return $this->hasMany(Utilizador::class, 'id_tipo_utilizador', 'id_tipo_utilizador');
    }
}
