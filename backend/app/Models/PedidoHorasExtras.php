<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoHorasExtras extends Model
{
    protected $table = 'pedido_horas_extras';
    protected $primaryKey = 'id_pedido';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id_pedido', 'data_horas', 'numero_horas', 'motivo'];

    protected $casts = [
        'data_horas'   => 'date',
        'numero_horas' => 'decimal:2',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }
}
