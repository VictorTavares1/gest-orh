<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoCompSaidaAntecipada extends Model
{
    protected $table = 'pedido_comp_saida_antecipada';
    protected $primaryKey = 'id_pedido';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_pedido',
        'data_saida_antecipada',
        'horario',
        'num_horas_extras',
        'data_horas_extras',
        'motivo',
    ];

    protected $casts = [
        'data_saida_antecipada' => 'date:Y-m-d',
        'data_horas_extras'     => 'date:Y-m-d',
        'num_horas_extras'      => 'decimal:2',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }
}
