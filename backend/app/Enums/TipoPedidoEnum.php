<?php

namespace App\Enums;

enum TipoPedidoEnum: string
{
    case HORAS_EXTRAS            = 'horas_extras';
    case JUSTIFICACAO_FALTAS     = 'justificacao_faltas';
    case MARCACAO_FERIAS         = 'marcacao_ferias';
    case ALTERACAO_FERIAS        = 'alteracao_ferias';
    case TROCA_HORARIO           = 'troca_horario';
    case TROCA_FOLGA_INSTITUICAO = 'troca_folga_instituicao';
    case INTERRUPCAO_ATIVIDADE   = 'interrupcao_atividade';
    case FOLGA_ANIVERSARIO       = 'folga_aniversario';
    case ASSIDUIDADE             = 'assiduidade';
    case LICENCA_NOJO            = 'licenca_nojo';
    case FORMACAO                = 'formacao';
    case MOTIVOS_ACADEMICOS      = 'motivos_academicos';
    case COMP_ENTRADA_TARDIA     = 'comp_entrada_tardia';
    case COMP_SAIDA_ANTECIPADA   = 'comp_saida_antecipada';

    public function label(): string
    {
        return match($this) {
            self::HORAS_EXTRAS            => 'Horas Extras',
            self::JUSTIFICACAO_FALTAS     => 'Justificação de Faltas',
            self::MARCACAO_FERIAS         => 'Marcação de Férias',
            self::ALTERACAO_FERIAS        => 'Alteração de Férias',
            self::TROCA_HORARIO           => 'Troca de Horário',
            self::TROCA_FOLGA_INSTITUICAO => 'Troca de Folga com Instituição',
            self::INTERRUPCAO_ATIVIDADE   => 'Interrupção de Atividade',
            self::FOLGA_ANIVERSARIO       => 'Folga de Aniversário',
            self::ASSIDUIDADE             => 'Assiduidade',
            self::LICENCA_NOJO            => 'Licença de Nojo',
            self::FORMACAO                => 'Formação',
            self::MOTIVOS_ACADEMICOS      => 'Motivos Académicos',
            self::COMP_ENTRADA_TARDIA     => 'Compensação de Entrada Tardia',
            self::COMP_SAIDA_ANTECIPADA   => 'Compensação de Saída Antecipada',
        };
    }

    public static function fromLabel(string $label): self
    {
        foreach (self::cases() as $case) {
            if ($case->label() === $label) {
                return $case;
            }
        }
        throw new \ValueError("'{$label}' não é um label válido de TipoPedidoEnum.");
    }

    public function papelDeAprovacao(): array
    {
        return match($this) {
            self::TROCA_HORARIO => [
                PapelAprovadorEnum::COLEGA,
                PapelAprovadorEnum::DIRETORA_EXECUTIVA,
            ],
            default => [PapelAprovadorEnum::DIRETORA_EXECUTIVA],
        };
    }

    public function requerColega(): bool
    {
        return in_array(PapelAprovadorEnum::COLEGA, $this->papelDeAprovacao());
    }
}
