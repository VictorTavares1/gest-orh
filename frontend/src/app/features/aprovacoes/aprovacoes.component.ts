import { Component, inject, signal, computed, OnInit } from '@angular/core';
import { DatePipe } from '@angular/common';
import { RouterLink } from '@angular/router';
import { FormBuilder, ReactiveFormsModule } from '@angular/forms';
import { debounceTime, distinctUntilChanged } from 'rxjs';

import { MatTableModule } from '@angular/material/table';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatSelectModule } from '@angular/material/select';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatCardModule } from '@angular/material/card';
import { MatProgressBarModule } from '@angular/material/progress-bar';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatDialogModule, MatDialog } from '@angular/material/dialog';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatChipsModule } from '@angular/material/chips';

import { PedidoService } from '../../core/services/pedido.service';
import { AuthService } from '../../core/services/auth.service';
import { Pedido } from '../../core/models/pedido.model';
import { AcaoDialogComponent, AcaoDialogData } from './acao-dialog/acao-dialog.component';

const TIPOS_PEDIDO = [
  { value: '', label: 'Todos os tipos' },
  { value: 'Horas Extras', label: 'Horas Extras' },
  { value: 'Justificação de Faltas', label: 'Justificação de Faltas' },
  { value: 'Marcação de Férias', label: 'Marcação de Férias' },
  { value: 'Alteração de Férias', label: 'Alteração de Férias' },
  { value: 'Troca de Horário', label: 'Troca de Horário' },
  { value: 'Troca de Folga com Instituição', label: 'Troca de Folga c/ Instituição' },
  { value: 'Interrupção de Atividade', label: 'Interrupção de Atividade' },
  { value: 'Folga de Aniversário', label: 'Folga de Aniversário' },
  { value: 'Assiduidade', label: 'Assiduidade' },
  { value: 'Licença de Nojo', label: 'Licença de Nojo' },
  { value: 'Formação', label: 'Formação' },
  { value: 'Motivos Académicos', label: 'Motivos Académicos' },
  { value: 'Compensação de Entrada Tardia', label: 'Comp. Entrada Tardia' },
  { value: 'Compensação de Saída Antecipada', label: 'Comp. Saída Antecipada' },
];

@Component({
  selector: 'app-aprovacoes',
  standalone: true,
  imports: [
    DatePipe, RouterLink, ReactiveFormsModule,
    MatTableModule, MatPaginatorModule, MatSelectModule,
    MatFormFieldModule, MatInputModule, MatButtonModule,
    MatIconModule, MatCardModule, MatProgressBarModule,
    MatTooltipModule, MatDialogModule, MatSnackBarModule, MatChipsModule,
  ],
  templateUrl: './aprovacoes.component.html',
  styleUrl: './aprovacoes.component.scss',
})
export class AprovacoesComponent implements OnInit {
  private pedidoService = inject(PedidoService);
  private auth = inject(AuthService);
  private dialog = inject(MatDialog);
  private snack = inject(MatSnackBar);
  private fb = inject(FormBuilder);

  readonly isDiretora = this.auth.hasRole('diretora_executiva');
  readonly isSubstituta = this.auth.hasRole('substituta');
  private get estadoFiltro(): string {
    return (this.isDiretora() || this.isSubstituta()) ? 'EM_APROVACAO_EXECUTIVA' : 'EM_APROVACAO_COLEGA';
  }

  readonly colunas = ['funcionario', 'tipo', 'estado', 'data_criacao', 'acoes'];
  readonly tiposPedido = TIPOS_PEDIDO;

  readonly loading = signal(false);
  readonly processando = signal<number | null>(null);
  readonly pedidos = signal<Pedido[]>([]);
  readonly total = signal(0);
  readonly pagina = signal(1);
  readonly porPagina = signal(15);

  filtros = this.fb.nonNullable.group({
    pesquisa: [''],
    tipo: [''],
  });

  readonly pedidosFiltrados = computed(() => {
    const termo = this.filtros.getRawValue().pesquisa.toLowerCase().trim();
    const tipo = this.filtros.getRawValue().tipo;
    let lista = this.pedidos();
    if (tipo) lista = lista.filter(p => p.tipo?.nome === tipo);
    if (termo) lista = lista.filter(p =>
      p.utilizador?.nome?.toLowerCase().includes(termo) ||
      p.tipo?.nome?.toLowerCase().includes(termo)
    );
    return lista;
  });

  readonly totalPendentes = computed(() =>
    this.pedidos().filter(p => p.estado?.nome === this.estadoFiltro).length
  );

  ngOnInit(): void {
    this.carregar();

    this.filtros.controls.tipo.valueChanges
      .pipe(debounceTime(200), distinctUntilChanged())
      .subscribe(() => { this.pagina.set(1); this.carregar(); });
  }

  carregar(): void {
    this.loading.set(true);
    this.pedidoService.listar({ estado: this.estadoFiltro, per_page: 200 }).subscribe({
      next: (res) => {
        this.pedidos.set(res.data);
        this.total.set(res.meta.total);
        this.loading.set(false);
      },
      error: () => this.loading.set(false),
    });
  }

  onPagina(event: PageEvent): void {
    this.pagina.set(event.pageIndex + 1);
    this.porPagina.set(event.pageSize);
  }

  limpar(): void {
    this.filtros.reset({ pesquisa: '', tipo: '' });
  }

  aprovar(pedido: Pedido): void {
    const data: AcaoDialogData = {
      acao: 'aprovar',
      tipoPedido: pedido.tipo?.nome ?? '',
      funcionario: pedido.utilizador?.nome ?? '',
    };
    this.dialog.open(AcaoDialogComponent, { data, width: '420px' })
      .afterClosed()
      .subscribe((confirmado: boolean) => {
        if (!confirmado) return;
        this.processando.set(pedido.id_pedido);
        this.pedidoService.aprovar(pedido.id_pedido).subscribe({
          next: () => {
            this.snack.open('Pedido aprovado com sucesso.', 'Fechar', { duration: 4000, panelClass: 'snack-success' });
            this.processando.set(null);
            this.carregar();
          },
          error: (err) => {
            this.snack.open(err?.error?.message ?? 'Erro ao aprovar pedido.', 'Fechar', { duration: 5000, panelClass: 'snack-error' });
            this.processando.set(null);
          },
        });
      });
  }

  rejeitar(pedido: Pedido): void {
    const data: AcaoDialogData = {
      acao: 'rejeitar',
      tipoPedido: pedido.tipo?.nome ?? '',
      funcionario: pedido.utilizador?.nome ?? '',
    };
    this.dialog.open(AcaoDialogComponent, { data, width: '420px' })
      .afterClosed()
      .subscribe((confirmado: boolean) => {
        if (!confirmado) return;
        this.processando.set(pedido.id_pedido);
        this.pedidoService.rejeitar(pedido.id_pedido).subscribe({
          next: () => {
            this.snack.open('Pedido rejeitado.', 'Fechar', { duration: 4000, panelClass: 'snack-warn' });
            this.processando.set(null);
            this.carregar();
          },
          error: (err) => {
            this.snack.open(err?.error?.message ?? 'Erro ao rejeitar pedido.', 'Fechar', { duration: 5000, panelClass: 'snack-error' });
            this.processando.set(null);
          },
        });
      });
  }

  devolver(pedido: Pedido): void {
    const data: AcaoDialogData = {
      acao: 'devolver',
      tipoPedido: pedido.tipo?.nome ?? '',
      funcionario: pedido.utilizador?.nome ?? '',
      pedirComentario: true,
    };
    this.dialog.open(AcaoDialogComponent, { data, width: '460px' })
      .afterClosed()
      .subscribe((resultado: { confirmado: boolean; comentario?: string } | undefined) => {
        if (!resultado?.confirmado) return;
        this.processando.set(pedido.id_pedido);
        this.pedidoService.devolver(pedido.id_pedido, resultado.comentario).subscribe({
          next: () => {
            this.snack.open('Pedido devolvido ao funcionário.', 'Fechar', { duration: 4000, panelClass: 'snack-warn' });
            this.processando.set(null);
            this.carregar();
          },
          error: (err) => {
            this.snack.open(err?.error?.message ?? 'Erro ao devolver pedido.', 'Fechar', { duration: 5000, panelClass: 'snack-error' });
            this.processando.set(null);
          },
        });
      });
  }

  estadoClass(nome: string): string {
    const map: Record<string, string> = {
      EM_APROVACAO_EXECUTIVA: 'estado-aprovacao',
    };
    return map[nome] ?? 'estado-aprovacao';
  }
}
