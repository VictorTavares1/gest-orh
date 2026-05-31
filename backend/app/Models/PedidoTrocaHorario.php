<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoTrocaHorario extends Model
{
    protected $table = 'pedido_troca_horario';
    protected $primaryKey = 'id_pedido';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_pedido',
        'id_colega',
        'id_setor_colega',
        'data_troca',
        'horario_antigo_ini',
        'horario_antigo_fim',
        'horario_novo_ini',
        'horario_novo_fim',
        'motivo',
    ];

    protected $casts = [
        'data_troca' => 'date:Y-m-d',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    public function colega(): BelongsTo
    {
        return $this->belongsTo(Utilizador::class, 'id_colega', 'id_utilizador');
    }

    public function setorColega(): BelongsTo
    {
        return $this->belongsTo(Setor::class, 'id_setor_colega', 'id_setor');
    }
}
