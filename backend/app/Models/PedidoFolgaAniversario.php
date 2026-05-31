<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoFolgaAniversario extends Model
{
    protected $table = 'pedido_folga_aniversario';
    protected $primaryKey = 'id_pedido';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id_pedido', 'data_folga', 'horario', 'data_aniversario'];

    protected $casts = [
        'data_folga'       => 'date:Y-m-d',
        'data_aniversario' => 'date:Y-m-d',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }
}
