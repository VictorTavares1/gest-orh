import { Component, inject, signal, OnInit, input } from '@angular/core';
import { DatePipe } from '@angular/common';
import { Router, RouterLink } from '@angular/router';

import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatDividerModule } from '@angular/material/divider';
import { MatProgressBarModule } from '@angular/material/progress-bar';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';
import { MatTooltipModule } from '@angular/material/tooltip';

import { PedidoService } from '../../../core/services/pedido.service';
import { AuthService } from '../../../core/services/auth.service';
import { Pedido, HistoricoItem } from '../../../core/models/pedido.model';
import { AcaoDialogComponent, AcaoDialogData } from '../../aprovacoes/acao-dialog/acao-dialog.component';

const ESTADOS_TERMINAIS = ['APROVADO', 'REJEITADO', 'CANCELADO'];
const LABEL_MAP: Record<string, string> = {
  data_horas: 'Data das Horas',
  numero_horas: 'Número de Horas',
  motivo: 'Motivo',
  data_falta: 'Data da Falta',
  hora_falta: 'Hora da Falta',
  id_periodo: 'Período',
  data_inicio: 'Data de Início',
  numero_dias: 'Número de Dias',
  id_periodo_atual: 'Período Atual',
  novo_inicio: 'Nova Data de Início',
  novo_fim: 'Nova Data de Fim',
  id_colega: 'Colega',
  id_setor_colega: 'Setor do Colega',
  data_troca: 'Data da Troca',
  horario_antigo_ini: 'Horário Antigo (início)',
  horario_antigo_fim: 'Horário Antigo (fim)',
  horario_novo_ini: 'Novo Horário (início)',
  horario_novo_fim: 'Novo Horário (fim)',
  data_original: 'Data Original',
  horario_original: 'Horário Original',
  nova_data: 'Nova Data',
  data_trabalhada: 'Data Trabalhada',
  horario_trabalhado: 'Horário Trabalhado',
  data_folga: 'Data de Folga',
  horario_folga: 'Horário de Folga',
  horario: 'Horário',
  data_aniversario: 'Data de Aniversário',
  data_sem_picagem: 'Data sem Picagem',
  dias_ausencia: 'Dias de Ausência',
  nome_falecido: 'Nome do Falecido',
  grau_parentesco: 'Grau de Parentesco',
  data_formacao: 'Data da Formação',
  tema_formacao: 'Tema da Formação',
  data_ausencia: 'Data de Ausência',
  motivo_academico: 'Motivo Académico',
  data_entrada_tardia: 'Data de Entrada Tardia',
  num_horas_extras: 'Horas Extras',
  data_horas_extras: 'Data das Horas Extras',
  data_saida_antecipada: 'Data de Saída Antecipada',
};

const CAMPOS_IGNORADOS = ['id_pedido', 'created_at', 'updated_at'];

@Component({
  selector: 'app-detalhe-pedido',
  standalone: true,
  imports: [
    DatePipe, RouterLink,
    MatCardModule, MatButtonModule, MatIconModule,
    MatDividerModule, MatProgressBarModule, MatProgressSpinnerModule,
    MatDialogModule, MatTooltipModule,
  ],
  templateUrl: './detalhe-pedido.component.html',
  styleUrl: './detalhe-pedido.component.scss',
})
export class DetalhePedidoComponent implements OnInit {
  readonly id = input.required<string>();

  private pedidoService = inject(PedidoService);
  private auth = inject(AuthService);
  private router = inject(Router);
  private snackBar = inject(MatSnackBar);
  private dialog = inject(MatDialog);

  readonly isDiretora = this.auth.hasRole('diretora_executiva');

  readonly loading = signal(true);
  readonly acaoLoading = signal(false);
  readonly pedido = signal<Pedido | null>(null);
  readonly historico = signal<HistoricoItem[]>([]);

  ngOnInit(): void {
    this.carregar();
  }

  carregar(): void {
    this.loading.set(true);
    const id = Number(this.id());

    this.pedidoService.mostrar(id).subscribe({
      next: (res) => {
        this.pedido.set(res.data);
        this.loading.set(false);
        this.carregarHistorico(id);
      },
      error: () => {
        this.loading.set(false);
        this.snackBar.open('Pedido não encontrado.', 'Fechar', { duration: 3000 });
        this.router.navigate(['/pedidos']);
      },
    });
  }

  carregarHistorico(id: number): void {
    this.pedidoService.historico(id).subscribe({
      next: (res) => this.historico.set(res.data),
    });
  }

  podeSubmeter(): boolean {
    return this.pedido()?.estado?.nome === 'RASCUNHO';
  }

  podeCancelar(): boolean {
    const estado = this.pedido()?.estado?.nome;
    if (!estado || ESTADOS_TERMINAIS.includes(estado)) return false;
    if (this.isDiretora()) return false;
    return true;
  }

  submeter(): void {
    const p = this.pedido();
    if (!p) return;
    this.acaoLoading.set(true);
    this.pedidoService.submeter(p.id_pedido).subscribe({
      next: (res) => {
        this.pedido.set(res.data);
        this.acaoLoading.set(false);
        this.carregarHistorico(p.id_pedido);
        this.snackBar.open('Pedido submetido com sucesso.', 'Fechar', {
          duration: 3000,
          panelClass: 'snack-success',
        });
      },
      error: (err) => {
        this.acaoLoading.set(false);
        const msg = err.error?.message ?? 'Erro ao submeter pedido.';
        this.snackBar.open(msg, 'Fechar', { duration: 4000, panelClass: 'snack-error' });
      },
    });
  }

  cancelar(): void {
    const p = this.pedido();
    if (!p) return;
    this.acaoLoading.set(true);
    this.pedidoService.cancelar(p.id_pedido).subscribe({
      next: (res) => {
        this.pedido.set(res.data);
        this.acaoLoading.set(false);
        this.carregarHistorico(p.id_pedido);
        this.snackBar.open('Pedido cancelado.', 'Fechar', { duration: 3000 });
      },
      error: (err) => {
        this.acaoLoading.set(false);
        const msg = err.error?.message ?? 'Erro ao cancelar pedido.';
        this.snackBar.open(msg, 'Fechar', { duration: 4000, panelClass: 'snack-error' });
      },
    });
  }

  podeAprovar(): boolean {
    return this.isDiretora() && this.pedido()?.estado?.nome === 'EM_APROVACAO_EXECUTIVA';
  }

  aprovar(): void {
    const p = this.pedido();
    if (!p) return;
    const data: AcaoDialogData = { acao: 'aprovar', tipoPedido: p.tipo?.nome ?? '', funcionario: p.utilizador?.nome ?? '' };
    this.dialog.open(AcaoDialogComponent, { data, width: '420px' })
      .afterClosed()
      .subscribe((confirmado: boolean) => {
        if (!confirmado) return;
        this.acaoLoading.set(true);
        this.pedidoService.aprovar(p.id_pedido).subscribe({
          next: (res) => { this.pedido.set(res.data); this.acaoLoading.set(false); this.carregarHistorico(p.id_pedido); this.snackBar.open('Pedido aprovado.', 'Fechar', { duration: 3000, panelClass: 'snack-success' }); },
          error: (err) => { this.acaoLoading.set(false); this.snackBar.open(err?.error?.message ?? 'Erro ao aprovar.', 'Fechar', { duration: 4000, panelClass: 'snack-error' }); },
        });
      });
  }

  rejeitar(): void {
    const p = this.pedido();
    if (!p) return;
    const data: AcaoDialogData = { acao: 'rejeitar', tipoPedido: p.tipo?.nome ?? '', funcionario: p.utilizador?.nome ?? '' };
    this.dialog.open(AcaoDialogComponent, { data, width: '420px' })
      .afterClosed()
      .subscribe((confirmado: boolean) => {
        if (!confirmado) return;
        this.acaoLoading.set(true);
        this.pedidoService.rejeitar(p.id_pedido).subscribe({
          next: (res) => { this.pedido.set(res.data); this.acaoLoading.set(false); this.carregarHistorico(p.id_pedido); this.snackBar.open('Pedido rejeitado.', 'Fechar', { duration: 3000, panelClass: 'snack-warn' }); },
          error: (err) => { this.acaoLoading.set(false); this.snackBar.open(err?.error?.message ?? 'Erro ao rejeitar.', 'Fechar', { duration: 4000, panelClass: 'snack-error' }); },
        });
      });
  }

  devolver(): void {
    const p = this.pedido();
    if (!p) return;
    const data: AcaoDialogData = { acao: 'devolver', tipoPedido: p.tipo?.nome ?? '', funcionario: p.utilizador?.nome ?? '', pedirComentario: true };
    this.dialog.open(AcaoDialogComponent, { data, width: '460px' })
      .afterClosed()
      .subscribe((resultado: { confirmado: boolean; comentario?: string } | undefined) => {
        if (!resultado?.confirmado) return;
        this.acaoLoading.set(true);
        this.pedidoService.devolver(p.id_pedido, resultado.comentario).subscribe({
          next: (res) => { this.pedido.set(res.data); this.acaoLoading.set(false); this.carregarHistorico(p.id_pedido); this.snackBar.open('Pedido devolvido ao funcionário.', 'Fechar', { duration: 3000, panelClass: 'snack-warn' }); },
          error: (err) => { this.acaoLoading.set(false); this.snackBar.open(err?.error?.message ?? 'Erro ao devolver.', 'Fechar', { duration: 4000, panelClass: 'snack-error' }); },
        });
      });
  }

  camposEspecializacao(): { label: string; valor: unknown }[] {
    const esp = this.pedido()?.especializacao;
    if (!esp) return [];
    return Object.entries(esp)
      .filter(([k]) => !CAMPOS_IGNORADOS.includes(k))
      .map(([k, v]) => ({ label: LABEL_MAP[k] ?? k, valor: v }));
  }

  estadoClass(nome: string): string {
    const map: Record<string, string> = {
      RASCUNHO: 'estado-rascunho',
      PENDENTE: 'estado-pendente',
      EM_APROVACAO_COLEGA: 'estado-aprovacao',
      EM_APROVACAO_DIRETOR: 'estado-aprovacao',
      EM_APROVACAO_EXECUTIVA: 'estado-aprovacao',
      APROVADO: 'estado-aprovado',
      REJEITADO: 'estado-rejeitado',
      CANCELADO: 'estado-cancelado',
    };
    return map[nome] ?? 'estado-rascunho';
  }

  formatarValor(valor: unknown): string {
    if (valor === null || valor === undefined) return '—';
    if (typeof valor === 'boolean') return valor ? 'Sim' : 'Não';
    return String(valor);
  }
}
