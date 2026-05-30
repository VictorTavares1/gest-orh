<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoMarcacaoFerias extends Model
{
    protected $table = 'pedido_marcacao_ferias';
    protected $primaryKey = 'id_pedido';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id_pedido', 'id_periodo', 'data_inicio', 'numero_dias'];

    protected $casts = [
        'data_inicio'  => 'date',
        'numero_dias'  => 'integer',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'id_periodo', 'id_periodo');
    }
}
