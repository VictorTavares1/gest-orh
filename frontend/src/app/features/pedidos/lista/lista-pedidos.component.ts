import { Component, inject, signal, OnInit, computed } from '@angular/core';
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
import { MatChipsModule } from '@angular/material/chips';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatTabsModule } from '@angular/material/tabs';

import { PedidoService } from '../../../core/services/pedido.service';
import { AuthService } from '../../../core/services/auth.service';
import { Pedido } from '../../../core/models/pedido.model';

const ESTADOS = [
  { value: 'RASCUNHO',               label: 'Rascunho' },
  { value: 'EM_APROVACAO_EXECUTIVA', label: 'Em aprovação' },
  { value: 'APROVADO',               label: 'Aprovado' },
  { value: 'REJEITADO',              label: 'Rejeitado' },
  { value: 'CANCELADO',              label: 'Cancelado' },
];

@Component({
  selector: 'app-lista-pedidos',
  standalone: true,
  imports: [
    DatePipe, RouterLink, ReactiveFormsModule,
    MatTableModule, MatPaginatorModule, MatSelectModule,
    MatFormFieldModule, MatInputModule, MatButtonModule,
    MatIconModule, MatCardModule, MatProgressBarModule,
    MatChipsModule, MatTooltipModule, MatTabsModule,
  ],
  templateUrl: './lista-pedidos.component.html',
  styleUrl: './lista-pedidos.component.scss',
})
export class ListaPedidosComponent implements OnInit {
  private pedidoService = inject(PedidoService);
  private auth = inject(AuthService);
  private fb = inject(FormBuilder);

  readonly isDiretor = this.auth.hasRole('diretor_tecnico');
  readonly estados = ESTADOS;

  // ─── Colunas ──────────────────────────────────────────────────────────────
  readonly colunasMeus    = ['tipo', 'estado', 'data_criacao', 'acoes'];
  readonly colunasSetor   = ['funcionario', 'tipo', 'estado', 'data_criacao', 'acoes'];

  // ─── Os Meus Pedidos ──────────────────────────────────────────────────────
  readonly loadingMeus = signal(false);
  readonly pedidosMeus = signal<Pedido[]>([]);
  readonly totalMeus   = signal(0);
  readonly paginaMeus  = signal(1);
  readonly porPaginaMeus = signal(15);

  filtrosMeus = this.fb.nonNullable.group({
    pesquisa: [''],
    estado:   [''],
  });

  readonly pedidosMeusFiltrados = computed(() => {
    const termo = this.filtrosMeus.getRawValue().pesquisa.toLowerCase().trim();
    if (!termo) return this.pedidosMeus();
    return this.pedidosMeus().filter(p =>
      p.tipo?.nome?.toLowerCase().includes(termo) ||
      p.estado?.label?.toLowerCase().includes(termo)
    );
  });

  // ─── Pedidos do Setor (só diretor) ────────────────────────────────────────
  readonly loadingSetor = signal(false);
  readonly pedidosSetor = signal<Pedido[]>([]);
  readonly totalSetor   = signal(0);
  readonly paginaSetor  = signal(1);
  readonly porPaginaSetor = signal(15);

  filtrosSetor = this.fb.nonNullable.group({
    pesquisa: [''],
    estado:   [''],
  });

  readonly pedidosSetorFiltrados = computed(() => {
    const termo = this.filtrosSetor.getRawValue().pesquisa.toLowerCase().trim();
    const estado = this.filtrosSetor.getRawValue().estado;
    let lista = this.pedidosSetor();
    if (estado) lista = lista.filter(p => p.estado?.nome === estado);
    if (termo)  lista = lista.filter(p =>
      p.utilizador?.nome?.toLowerCase().includes(termo) ||
      p.tipo?.nome?.toLowerCase().includes(termo) ||
      p.estado?.label?.toLowerCase().includes(termo)
    );
    return lista;
  });

  ngOnInit(): void {
    this.carregarMeus();

    this.filtrosMeus.controls.estado.valueChanges
      .pipe(debounceTime(200), distinctUntilChanged())
      .subscribe(() => { this.paginaMeus.set(1); this.carregarMeus(); });

    if (this.isDiretor()) {
      this.carregarSetor();
      this.filtrosSetor.controls.estado.valueChanges
        .pipe(debounceTime(200), distinctUntilChanged())
        .subscribe(() => { this.paginaSetor.set(1); this.carregarSetor(); });
    }
  }

  // Os meus pedidos: carrega todos do setor e filtra localmente pelo id do utilizador autenticado
  carregarMeus(): void {
    this.loadingMeus.set(true);
    const { estado } = this.filtrosMeus.getRawValue();
    const meuId = this.auth.utilizador()?.id_utilizador;

    this.pedidoService.listar({ estado: estado || undefined, per_page: 200 }).subscribe({
      next: (res) => {
        const meus = this.isDiretor()
          ? res.data.filter(p => p.utilizador?.id_utilizador === meuId)
          : res.data;
        this.pedidosMeus.set(meus);
        this.totalMeus.set(meus.length);
        this.loadingMeus.set(false);
      },
      error: () => this.loadingMeus.set(false),
    });
  }

  // Pedidos do setor: carrega tudo de uma vez para filtrar localmente
  carregarSetor(): void {
    this.loadingSetor.set(true);
    this.pedidoService.listar({ per_page: 200 }).subscribe({
      next: (res) => {
        this.pedidosSetor.set(res.data);
        this.totalSetor.set(res.meta.total);
        this.loadingSetor.set(false);
      },
      error: () => this.loadingSetor.set(false),
    });
  }

  onPaginaMeus(event: PageEvent): void {
    this.paginaMeus.set(event.pageIndex + 1);
    this.porPaginaMeus.set(event.pageSize);
    this.carregarMeus();
  }

  limparMeus(): void {
    this.filtrosMeus.reset({ pesquisa: '', estado: '' });
  }

  limparSetor(): void {
    this.filtrosSetor.reset({ pesquisa: '', estado: '' });
  }

  estadoClass(nome: string): string {
    const map: Record<string, string> = {
      RASCUNHO:               'estado-rascunho',
      PENDENTE:               'estado-pendente',
      EM_APROVACAO_COLEGA:    'estado-aprovacao',
      EM_APROVACAO_DIRETOR:   'estado-aprovacao',
      EM_APROVACAO_EXECUTIVA: 'estado-aprovacao',
      APROVADO:               'estado-aprovado',
      REJEITADO:              'estado-rejeitado',
      CANCELADO:              'estado-cancelado',
    };
    return map[nome] ?? 'estado-rascunho';
  }
}
