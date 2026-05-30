<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoLicencaNojo extends Model
{
    protected $table = 'pedido_licenca_nojo';
    protected $primaryKey = 'id_pedido';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id_pedido', 'dias_ausencia', 'nome_falecido', 'grau_parentesco'];

    protected $casts = [
        'dias_ausencia' => 'integer',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }
}
