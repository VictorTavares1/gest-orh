<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoCompEntradaTardia extends Model
{
    protected $table = 'pedido_comp_entrada_tardia';
    protected $primaryKey = 'id_pedido';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_pedido',
        'data_entrada_tardia',
        'horario',
        'num_horas_extras',
        'data_horas_extras',
        'motivo',
    ];

    protected $casts = [
        'data_entrada_tardia' => 'date',
        'data_horas_extras'   => 'date',
        'num_horas_extras'    => 'decimal:2',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }
}
