<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoMotivosAcademicos extends Model
{
    protected $table = 'pedido_motivos_academicos';
    protected $primaryKey = 'id_pedido';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id_pedido', 'data_ausencia', 'motivo_academico'];

    protected $casts = [
        'data_ausencia' => 'date',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }
}
