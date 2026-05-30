<?php

namespace App\Models;

use App\Enums\EstadoPedidoEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoPedido extends Model
{
    protected $table = 'estado_pedido';
    protected $primaryKey = 'id_estado_pedido';
    public $timestamps = false;

    protected $fillable = ['nome'];

    protected $casts = [
        'nome' => EstadoPedidoEnum::class,
    ];

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'id_estado_pedido', 'id_estado_pedido');
    }
}
