<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricoPedido extends Model
{
    protected $table = 'historico_pedido';
    protected $primaryKey = 'id_historico';
    public $timestamps = false;

    protected $fillable = [
        'id_pedido',
        'id_utilizador_acao',
        'id_estado_anterior',
        'id_estado_novo',
        'data_alteracao',
        'comentario',
    ];

    protected $casts = [
        'data_alteracao' => 'datetime',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    public function utilizadorAcao(): BelongsTo
    {
        return $this->belongsTo(Utilizador::class, 'id_utilizador_acao', 'id_utilizador');
    }

    public function estadoAnterior(): BelongsTo
    {
        return $this->belongsTo(EstadoPedido::class, 'id_estado_anterior', 'id_estado_pedido');
    }

    public function estadoNovo(): BelongsTo
    {
        return $this->belongsTo(EstadoPedido::class, 'id_estado_novo', 'id_estado_pedido');
    }
}
