<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoAssiduidade extends Model
{
    protected $table = 'pedido_assiduidade';
    protected $primaryKey = 'id_pedido';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id_pedido', 'data_sem_picagem', 'horario'];

    protected $casts = [
        'data_sem_picagem' => 'date',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }
}
