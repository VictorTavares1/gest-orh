<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoJustificacaoFaltas extends Model
{
    protected $table = 'pedido_justificacao_faltas';
    protected $primaryKey = 'id_pedido';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id_pedido', 'data_falta', 'hora_falta', 'motivo'];

    protected $casts = [
        'data_falta' => 'date:Y-m-d',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }
}
