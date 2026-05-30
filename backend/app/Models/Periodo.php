<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Periodo extends Model
{
    protected $table = 'periodo';
    protected $primaryKey = 'id_periodo';
    public $timestamps = false;

    protected $fillable = ['data_inicio', 'data_fim'];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim'    => 'date',
    ];

    public function marcacoesFerias(): HasMany
    {
        return $this->hasMany(PedidoMarcacaoFerias::class, 'id_periodo', 'id_periodo');
    }

    public function alteracoesFerias(): HasMany
    {
        return $this->hasMany(PedidoAlteracaoFerias::class, 'id_periodo_atual', 'id_periodo');
    }
}
