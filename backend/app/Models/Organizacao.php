<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organizacao extends Model
{
    protected $table = 'organizacao';
    protected $primaryKey = 'id_organizacao';
    public $timestamps = false;

    protected $fillable = ['nome', 'ativo'];

    protected $casts = [
        'ativo'     => 'boolean',
        'criado_em' => 'datetime',
    ];

    public function setores(): HasMany
    {
        return $this->hasMany(Setor::class, 'id_organizacao', 'id_organizacao');
    }

    public function scopeAtiva($query)
    {
        return $query->where('ativo', true);
    }
}
