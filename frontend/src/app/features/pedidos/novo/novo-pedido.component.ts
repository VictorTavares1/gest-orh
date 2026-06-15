import { Component, inject, signal, OnInit, computed } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { FormBuilder, ReactiveFormsModule, Validators, AbstractControl } from '@angular/forms';
import { forkJoin } from 'rxjs';

import { MatStepperModule } from '@angular/material/stepper';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { MatTooltipModule } from '@angular/material/tooltip';

import { PedidoService } from '../../../core/services/pedido.service';
import { ReferenciaService, TipoPedido, Periodo, Utilizador, Setor } from '../../../core/services/referencia.service';

// ─── Definição dos campos por tipo ─────────────────────────────────────────

type FieldType = 'date' | 'time' | 'text' | 'textarea' | 'number' | 'select-periodo' | 'select-colega' | 'select-setor';

interface CampoConfig {
  key: string;
  label: string;
  type: FieldType;
  required?: boolean;
}

const CAMPOS_POR_TIPO: Record<string, CampoConfig[]> = {
  'horas-extras': [
    { key: 'data_horas',   label: 'Data das Horas',    type: 'date',   required: true },
    { key: 'numero_horas', label: 'Número de Horas',   type: 'number', required: true },
    { key: 'motivo',       label: 'Motivo',             type: 'text',   required: true },
  ],
  'justificacao-faltas': [
    { key: 'data_falta', label: 'Data da Falta', type: 'date', required: true },
    { key: 'hora_falta', label: 'Hora da Falta', type: 'time', required: true },
    { key: 'motivo',     label: 'Motivo',         type: 'text', required: true },
  ],
  'marcacao-ferias': [
    { key: 'id_periodo',  label: 'Período',        type: 'select-periodo', required: true },
    { key: 'data_inicio', label: 'Data de Início', type: 'date',           required: true },
    { key: 'numero_dias', label: 'Número de Dias', type: 'number',         required: true },
  ],
  'alteracao-ferias': [
    { key: 'id_periodo_atual', label: 'Período Atual',      type: 'select-periodo', required: true },
    { key: 'novo_inicio',      label: 'Nova Data de Início', type: 'date',           required: true },
    { key: 'novo_fim',         label: 'Nova Data de Fim',    type: 'date',           required: true },
    { key: 'numero_dias',      label: 'Número de Dias',      type: 'number',         required: true },
  ],
  'troca-horario': [
    { key: 'id_colega',          label: 'Colega',               type: 'select-colega', required: true },
    { key: 'id_setor_colega',    label: 'Setor do Colega',      type: 'select-setor',  required: true },
    { key: 'data_troca',         label: 'Data da Troca',        type: 'date',          required: true },
    { key: 'horario_antigo_ini', label: 'Horário Antigo (ini)', type: 'time',          required: true },
    { key: 'horario_antigo_fim', label: 'Horário Antigo (fim)', type: 'time',          required: true },
    { key: 'horario_novo_ini',   label: 'Novo Horário (ini)',   type: 'time',          required: true },
    { key: 'horario_novo_fim',   label: 'Novo Horário (fim)',   type: 'time',          required: true },
    { key: 'motivo',             label: 'Motivo',               type: 'text',          required: true },
  ],
  'troca-folga-instituicao': [
    { key: 'data_original',    label: 'Data Original',   type: 'date', required: true },
    { key: 'horario_original', label: 'Horário Original', type: 'time', required: true },
    { key: 'nova_data',        label: 'Nova Data',        type: 'date', required: true },
    { key: 'motivo',           label: 'Motivo',           type: 'text', required: true },
  ],
  'interrupcao-atividade': [
    { key: 'data_trabalhada',    label: 'Data Trabalhada',     type: 'date', required: true },
    { key: 'horario_trabalhado', label: 'Horário Trabalhado',  type: 'time', required: true },
    { key: 'data_folga',         label: 'Data de Folga',       type: 'date', required: true },
    { key: 'horario_folga',      label: 'Horário de Folga',    type: 'time', required: true },
  ],
  'folga-aniversario': [
    { key: 'data_folga',       label: 'Data de Folga',     type: 'date', required: true },
    { key: 'horario',          label: 'Horário',           type: 'time', required: true },
    { key: 'data_aniversario', label: 'Data de Aniversário', type: 'date', required: true },
  ],
  'assiduidade': [
    { key: 'data_sem_picagem', label: 'Data sem Picagem', type: 'date', required: true },
    { key: 'horario',          label: 'Horário',          type: 'time', required: true },
  ],
  'licenca-nojo': [
    { key: 'dias_ausencia',   label: 'Dias de Ausência',  type: 'number', required: true },
    { key: 'nome_falecido',   label: 'Nome do Falecido',  type: 'text',   required: true },
    { key: 'grau_parentesco', label: 'Grau de Parentesco', type: 'text',  required: true },
  ],
  'formacao': [
    { key: 'data_formacao', label: 'Data da Formação', type: 'date', required: true },
    { key: 'tema_formacao', label: 'Tema da Formação', type: 'text', required: true },
  ],
  'motivos-academicos': [
    { key: 'data_ausencia',    label: 'Data de Ausência',  type: 'date', required: true },
    { key: 'motivo_academico', label: 'Motivo Académico',  type: 'text', required: true },
  ],
  'comp-entrada-tardia': [
    { key: 'data_entrada_tardia', label: 'Data de Entrada Tardia', type: 'date',   required: true },
    { key: 'horario',             label: 'Horário',                 type: 'time',   required: true },
    { key: 'num_horas_extras',    label: 'Horas Extras',           type: 'number', required: true },
    { key: 'data_horas_extras',   label: 'Data das Horas Extras',  type: 'date',   required: true },
    { key: 'motivo',              label: 'Motivo',                  type: 'text',   required: true },
  ],
  'comp-saida-antecipada': [
    { key: 'data_saida_antecipada', label: 'Data de Saída Antecipada', type: 'date',   required: true },
    { key: 'horario',               label: 'Horário',                   type: 'time',   required: true },
    { key: 'num_horas_extras',      label: 'Horas Extras',             type: 'number', required: true },
    { key: 'data_horas_extras',     label: 'Data das Horas Extras',    type: 'date',   required: true },
    { key: 'motivo',                label: 'Motivo',                    type: 'text',   required: true },
  ],
  'outros': [
    { key: 'descricao', label: 'Descrição', type: 'textarea', required: true },
  ],
};

// Ícones por tipo
const ICONE_POR_TIPO: Record<string, string> = {
  'horas-extras':            'more_time',
  'justificacao-faltas':     'event_busy',
  'marcacao-ferias':         'beach_access',
  'alteracao-ferias':        'edit_calendar',
  'troca-horario':           'swap_horiz',
  'troca-folga-instituicao': 'swap_calls',
  'interrupcao-atividade':   'pause_circle',
  'folga-aniversario':       'cake',
  'assiduidade':             'fingerprint',
  'licenca-nojo':            'sentiment_very_dissatisfied',
  'formacao':                'school',
  'motivos-academicos':      'menu_book',
  'comp-entrada-tardia':     'login',
  'comp-saida-antecipada':   'logout',
  'outros':                  'description',
};

@Component({
  selector: 'app-novo-pedido',
  standalone: true,
  imports: [
    RouterLink, ReactiveFormsModule,
    MatStepperModule, MatCardModule, MatButtonModule, MatIconModule,
    MatFormFieldModule, MatInputModule, MatSelectModule,
    MatDatepickerModule,
    MatProgressSpinnerModule, MatTooltipModule,
  ],
  templateUrl: './novo-pedido.component.html',
  styleUrl: './novo-pedido.component.scss',
})
export class NovoPedidoComponent implements OnInit {
  private fb = inject(FormBuilder);
  private pedidoService = inject(PedidoService);
  private referenciaService = inject(ReferenciaService);
  private snackBar = inject(MatSnackBar);
  private router = inject(Router);

  readonly loadingRef = signal(true);
  readonly submitting = signal(false);

  // Dados de referência
  readonly tiposPedido = signal<TipoPedido[]>([]);
  readonly periodos = signal<Periodo[]>([]);
  readonly utilizadores = signal<Utilizador[]>([]);
  readonly setores = signal<Setor[]>([]);

  // Tipo selecionado
  readonly tipoSelecionado = signal<TipoPedido | null>(null);

  readonly camposDoTipo = computed(() => {
    const tipo = this.tipoSelecionado();
    if (!tipo) return [];
    return CAMPOS_POR_TIPO[tipo.slug] ?? [];
  });

  // Formulário de campos (passo 2)
  camposForm = this.fb.nonNullable.group({});

  ngOnInit(): void {
    forkJoin({
      tipos:       this.referenciaService.tiposPedido(),
      periodos:    this.referenciaService.periodos(),
      utilizadores: this.referenciaService.utilizadores(),
      setores:     this.referenciaService.setores(),
    }).subscribe({
      next: (res) => {
        this.tiposPedido.set(res.tipos.data);
        this.periodos.set(res.periodos.data);
        this.utilizadores.set(res.utilizadores.data);
        this.setores.set(res.setores.data);
        this.loadingRef.set(false);
      },
      error: () => {
        this.loadingRef.set(false);
        this.snackBar.open('Erro ao carregar dados. Tente novamente.', 'Fechar', { duration: 4000 });
      },
    });
  }

  selecionarTipo(tipo: TipoPedido): void {
    this.tipoSelecionado.set(tipo);
    this.construirFormulario();
  }

  private construirFormulario(): void {
    const campos = this.camposDoTipo();
    const group: Record<string, AbstractControl> = {};
    for (const campo of campos) {
      const validators = campo.required ? [Validators.required] : [];
      if (campo.type === 'number') validators.push(Validators.min(0));
      group[campo.key] = this.fb.nonNullable.control('', validators);
    }
    this.camposForm = this.fb.nonNullable.group(group);
  }

  tipoIcon(slug: string): string {
    return ICONE_POR_TIPO[slug] ?? 'description';
  }

  camposConfig(): CampoConfig[] {
    return this.camposDoTipo();
  }

  isDate(campo: CampoConfig): boolean { return campo.type === 'date'; }
  isTime(campo: CampoConfig): boolean { return campo.type === 'time'; }
  isNumber(campo: CampoConfig): boolean { return campo.type === 'number'; }
  isText(campo: CampoConfig): boolean { return campo.type === 'text'; }
  isSelectPeriodo(campo: CampoConfig): boolean { return campo.type === 'select-periodo'; }
  isTextarea(campo: CampoConfig): boolean { return campo.type === 'textarea'; }
  isSelectColega(campo: CampoConfig): boolean { return campo.type === 'select-colega'; }
  isSelectSetor(campo: CampoConfig): boolean { return campo.type === 'select-setor'; }

  getControl(key: string) {
    return this.camposForm.get(key);
  }

  submeter(): void {
    if (this.camposForm.invalid) {
      this.camposForm.markAllAsTouched();
      return;
    }
    const tipo = this.tipoSelecionado();
    if (!tipo) return;

    this.submitting.set(true);

    // Converter datas Date → string ISO (yyyy-MM-dd)
    const raw = this.camposForm.getRawValue() as Record<string, unknown>;
    const dados: Record<string, unknown> = {};
    const campos = this.camposDoTipo();
    for (const campo of campos) {
      const val = raw[campo.key];
      if (campo.type === 'date' && val instanceof Date) {
        dados[campo.key] = val.toISOString().split('T')[0];
      } else {
        dados[campo.key] = val;
      }
    }

    this.pedidoService.criar(tipo.slug, dados).subscribe({
      next: (res) => {
        this.submitting.set(false);
        this.snackBar.open('Pedido criado com sucesso!', 'Fechar', {
          duration: 3000,
          panelClass: 'snack-success',
        });
        this.router.navigate(['/pedidos', res.data.id_pedido]);
      },
      error: (err) => {
        this.submitting.set(false);
        const msg = err.error?.message ?? 'Erro ao criar pedido.';
        this.snackBar.open(msg, 'Fechar', { duration: 4000, panelClass: 'snack-error' });
      },
    });
  }
}
