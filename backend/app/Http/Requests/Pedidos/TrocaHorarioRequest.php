<?php

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrocaHorarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_colega'          => ['required', 'integer', Rule::exists('utilizador', 'id_utilizador'), function ($attr, $value, $fail) {
                if ((int) $value === $this->user()->id_utilizador) {
                    $fail('Não pode selecionar-se a si próprio como colega.');
                }
            }],
            'id_setor_colega'    => ['required', 'integer', Rule::exists('setor', 'id_setor')],
            'data_troca'         => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'horario_antigo_ini' => ['required', 'date_format:H:i'],
            'horario_antigo_fim' => ['required', 'date_format:H:i', function ($attr, $value, $fail) {
                $ini = $this->input('horario_antigo_ini');
                if ($ini && strtotime($value) <= strtotime($ini)) {
                    $fail('O horário de fim deve ser posterior ao horário de início.');
                }
            }],
            'horario_novo_ini'   => ['required', 'date_format:H:i'],
            'horario_novo_fim'   => ['required', 'date_format:H:i', function ($attr, $value, $fail) {
                $ini = $this->input('horario_novo_ini');
                if ($ini && strtotime($value) <= strtotime($ini)) {
                    $fail('O novo horário de fim deve ser posterior ao novo horário de início.');
                }
            }],
            'motivo'             => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_colega.required'          => 'O colega é obrigatório.',
            'id_colega.integer'           => 'O colega deve ser um identificador válido.',
            'id_colega.exists'            => 'O colega indicado não existe.',
            'id_setor_colega.required'    => 'O setor do colega é obrigatório.',
            'id_setor_colega.exists'      => 'O setor indicado não existe.',
            'data_troca.required'         => 'A data da troca é obrigatória.',
            'data_troca.date_format'      => 'A data deve estar no formato AAAA-MM-DD.',
            'data_troca.after_or_equal'   => 'A data da troca deve ser hoje ou uma data futura.',
            'horario_antigo_ini.required' => 'O horário de início (antigo) é obrigatório.',
            'horario_antigo_ini.date_format' => 'O horário deve estar no formato HH:MM.',
            'horario_antigo_fim.required' => 'O horário de fim (antigo) é obrigatório.',
            'horario_antigo_fim.date_format' => 'O horário deve estar no formato HH:MM.',
            'horario_novo_ini.required'   => 'O novo horário de início é obrigatório.',
            'horario_novo_ini.date_format' => 'O horário deve estar no formato HH:MM.',
            'horario_novo_fim.required'   => 'O novo horário de fim é obrigatório.',
            'horario_novo_fim.date_format' => 'O horário deve estar no formato HH:MM.',
            'motivo.required'             => 'O motivo é obrigatório.',
            'motivo.min'                  => 'O motivo deve ter pelo menos 10 caracteres.',
            'motivo.max'                  => 'O motivo não pode exceder 1000 caracteres.',
        ];
    }
}
