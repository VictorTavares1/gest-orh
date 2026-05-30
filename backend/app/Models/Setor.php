<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Setor extends Model
{
    protected $table = 'setor';
    protected $primaryKey = 'id_setor';
    public $timestamps = false;

    protected $fillable = ['id_organizacao', 'nome'];

    public function organizacao(): BelongsTo
    {
        return $this->belongsTo(Organizacao::class, 'id_organizacao', 'id_organizacao');
    }

    public function utilizadores(): HasMany
    {
        return $this->hasMany(Utilizador::class, 'id_setor', 'id_setor');
    }
}
