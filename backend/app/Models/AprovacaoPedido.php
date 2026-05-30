<?php

namespace App\Models;

use App\Enums\PapelAprovadorEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AprovacaoPedido extends Model
{
    protected $table = 'aprovacao_pedido';
    protected $primaryKey = 'id_aprovacao';
    public $timestamps = false;

    protected $fillable = [
        'id_pedido',
        'id_aprovador',
        'papel_aprovador',
        'id_estado_pedido',
    ];

    protected $casts = [
        'papel_aprovador' => PapelAprovadorEnum::class,
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    public function aprovador(): BelongsTo
    {
        return $this->belongsTo(Utilizador::class, 'id_aprovador', 'id_utilizador');
    }

    public function estadoPedido(): BelongsTo
    {
        return $this->belongsTo(EstadoPedido::class, 'id_estado_pedido', 'id_estado_pedido');
    }
}
