<?php

namespace App\Services;

use App\Models\Anexo;
use App\Models\Pedido;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnexoService
{
    public function upload(Pedido $pedido, UploadedFile $ficheiro): Anexo
    {
        $nomeOriginal = $ficheiro->getClientOriginalName();
        $nomeSanitizado = preg_replace('/[^a-zA-Z0-9._-]/', '_', $nomeOriginal);
        $nome     = time() . '_' . $nomeSanitizado;
        $caminho  = $ficheiro->storeAs("pedidos/{$pedido->id_pedido}", $nome, 'public');

        return Anexo::create([
            'id_pedido'    => $pedido->id_pedido,
            'caminho'      => $caminho,
            'nome_original' => $nomeOriginal,
        ]);
    }

    public function remover(Anexo $anexo): void
    {
        Storage::disk('public')->delete($anexo->caminho);
        $anexo->delete();
    }

    public function download(Anexo $anexo): StreamedResponse
    {
        abort_unless(
            Storage::disk('public')->exists($anexo->caminho),
            404,
            'Ficheiro não encontrado no servidor.'
        );

        return Storage::disk('public')->download($anexo->caminho, $anexo->nome_original ?? basename($anexo->caminho));
    }
}
