<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoTrocaFolgaInstituicao extends Model
{
    protected $table = 'pedido_troca_folga_instituicao';
    protected $primaryKey = 'id_pedido';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id_pedido', 'data_original', 'horario_original', 'nova_data', 'motivo'];

    protected $casts = [
        'data_original' => 'date:Y-m-d',
        'nova_data'     => 'date:Y-m-d',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }
}
