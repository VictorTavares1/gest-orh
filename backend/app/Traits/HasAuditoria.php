<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait HasAuditoria
{
    protected function logAcao(string $acao, array $contexto = []): void
    {
        Log::info("[AUDITORIA] {$acao}", array_merge([
            'utilizador_id' => Auth::id(),
            'ip'            => request()->ip(),
            'timestamp'     => now()->toIso8601String(),
        ], $contexto));
    }
}
