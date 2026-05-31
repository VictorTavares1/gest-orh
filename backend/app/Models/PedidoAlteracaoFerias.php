<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoAlteracaoFerias extends Model
{
    protected $table = 'pedido_alteracao_ferias';
    protected $primaryKey = 'id_pedido';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id_pedido', 'id_periodo_atual', 'novo_inicio', 'novo_fim', 'numero_dias'];

    protected $casts = [
        'novo_inicio' => 'date:Y-m-d',
        'novo_fim'    => 'date:Y-m-d',
        'numero_dias' => 'integer',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    public function periodoAtual(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'id_periodo_atual', 'id_periodo');
    }
}
