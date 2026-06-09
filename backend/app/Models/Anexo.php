<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Anexo extends Model
{
    protected $table = 'anexo';
    protected $primaryKey = 'id_anexo';
    public $timestamps = false;

    protected $fillable = ['id_pedido', 'caminho', 'nome_original'];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }
}
