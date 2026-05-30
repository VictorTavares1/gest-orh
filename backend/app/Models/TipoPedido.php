<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoPedido extends Model
{
    protected $table = 'tipo_pedido';
    protected $primaryKey = 'id_tipo_pedido';
    public $timestamps = false;

    protected $fillable = ['nome', 'ativo'];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'id_tipo_pedido', 'id_tipo_pedido');
    }

    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }
}
