<?php

namespace App\Models;

use App\Enums\EstadoPedidoEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pedido extends Model
{
    protected $table = 'pedido';
    protected $primaryKey = 'id_pedido';
    public $timestamps = false;

    protected $fillable = [
        'id_utilizador',
        'id_tipo_pedido',
        'id_estado_pedido',
        'data_criacao',
    ];

    protected $casts = [
        'data_criacao' => 'datetime',
    ];

    public function utilizador(): BelongsTo
    {
        return $this->belongsTo(Utilizador::class, 'id_utilizador', 'id_utilizador');
    }

    public function tipoPedido(): BelongsTo
    {
        return $this->belongsTo(TipoPedido::class, 'id_tipo_pedido', 'id_tipo_pedido');
    }

    public function estadoPedido(): BelongsTo
    {
        return $this->belongsTo(EstadoPedido::class, 'id_estado_pedido', 'id_estado_pedido');
    }

    public function anexos(): HasMany
    {
        return $this->hasMany(Anexo::class, 'id_pedido', 'id_pedido');
    }

    public function aprovacoes(): HasMany
    {
        return $this->hasMany(AprovacaoPedido::class, 'id_pedido', 'id_pedido');
    }

    public function historico(): HasMany
    {
        return $this->hasMany(HistoricoPedido::class, 'id_pedido', 'id_pedido')
            ->orderBy('data_alteracao');
    }

    // Especializações
    public function horasExtras(): HasOne
    {
        return $this->hasOne(PedidoHorasExtras::class, 'id_pedido', 'id_pedido');
    }

    public function justificacaoFaltas(): HasOne
    {
        return $this->hasOne(PedidoJustificacaoFaltas::class, 'id_pedido', 'id_pedido');
    }

    public function marcacaoFerias(): HasOne
    {
        return $this->hasOne(PedidoMarcacaoFerias::class, 'id_pedido', 'id_pedido');
    }

    public function alteracaoFerias(): HasOne
    {
        return $this->hasOne(PedidoAlteracaoFerias::class, 'id_pedido', 'id_pedido');
    }

    public function trocaHorario(): HasOne
    {
        return $this->hasOne(PedidoTrocaHorario::class, 'id_pedido', 'id_pedido');
    }

    public function trocaFolgaInstituicao(): HasOne
    {
        return $this->hasOne(PedidoTrocaFolgaInstituicao::class, 'id_pedido', 'id_pedido');
    }

    public function interrupcaoAtividade(): HasOne
    {
        return $this->hasOne(PedidoInterrupcaoAtividade::class, 'id_pedido', 'id_pedido');
    }

    public function folgaAniversario(): HasOne
    {
        return $this->hasOne(PedidoFolgaAniversario::class, 'id_pedido', 'id_pedido');
    }

    public function assiduidade(): HasOne
    {
        return $this->hasOne(PedidoAssiduidade::class, 'id_pedido', 'id_pedido');
    }

    public function licencaNojo(): HasOne
    {
        return $this->hasOne(PedidoLicencaNojo::class, 'id_pedido', 'id_pedido');
    }

    public function formacao(): HasOne
    {
        return $this->hasOne(PedidoFormacao::class, 'id_pedido', 'id_pedido');
    }

    public function motivosAcademicos(): HasOne
    {
        return $this->hasOne(PedidoMotivosAcademicos::class, 'id_pedido', 'id_pedido');
    }

    public function compEntradaTardia(): HasOne
    {
        return $this->hasOne(PedidoCompEntradaTardia::class, 'id_pedido', 'id_pedido');
    }

    public function compSaidaAntecipada(): HasOne
    {
        return $this->hasOne(PedidoCompSaidaAntecipada::class, 'id_pedido', 'id_pedido');
    }

    public function scopeDoUtilizador($query, int $utilizadorId)
    {
        return $query->where('id_utilizador', $utilizadorId);
    }

    public function scopePorEstado($query, EstadoPedidoEnum $estado)
    {
        return $query->whereHas('estadoPedido', fn($q) => $q->where('nome', $estado->value));
    }

    public function scopePorTipo($query, int $tipoPedidoId)
    {
        return $query->where('id_tipo_pedido', $tipoPedidoId);
    }
}
