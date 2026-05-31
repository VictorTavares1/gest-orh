<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoInterrupcaoAtividade extends Model
{
    protected $table = 'pedido_interrupcao_atividade';
    protected $primaryKey = 'id_pedido';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_pedido',
        'data_folga',
        'horario_folga',
        'data_trabalhada',
        'horario_trabalhado',
    ];

    protected $casts = [
        'data_folga'      => 'date:Y-m-d',
        'data_trabalhada' => 'date:Y-m-d',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }
}
