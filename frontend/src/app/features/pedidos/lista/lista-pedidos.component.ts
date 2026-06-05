import { Component, inject, signal, OnInit, effect } from '@angular/core';
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

import { PedidoService } from '../../../core/services/pedido.service';
import { Pedido } from '../../../core/models/pedido.model';

const ESTADOS = [
  { value: 'RASCUNHO', label: 'Rascunho' },
  { value: 'PENDENTE', label: 'Pendente' },
  { value: 'EM_APROVACAO_COLEGA', label: 'Em aprovação (colega)' },
  { value: 'EM_APROVACAO_DIRETOR', label: 'Em aprovação (diretor)' },
  { value: 'EM_APROVACAO_EXECUTIVA', label: 'Em aprovação (executiva)' },
  { value: 'APROVADO', label: 'Aprovado' },
  { value: 'REJEITADO', label: 'Rejeitado' },
  { value: 'CANCELADO', label: 'Cancelado' },
];

@Component({
  selector: 'app-lista-pedidos',
  standalone: true,
  imports: [
    DatePipe, RouterLink, ReactiveFormsModule,
    MatTableModule, MatPaginatorModule, MatSelectModule,
    MatFormFieldModule, MatInputModule, MatButtonModule,
    MatIconModule, MatCardModule, MatProgressBarModule,
    MatChipsModule, MatTooltipModule,
  ],
  templateUrl: './lista-pedidos.component.html',
  styleUrl: './lista-pedidos.component.scss',
})
export class ListaPedidosComponent implements OnInit {
  private pedidoService = inject(PedidoService);
  private fb = inject(FormBuilder);

  readonly estados = ESTADOS;
  readonly colunas = ['tipo', 'estado', 'data_criacao', 'acoes'];

  readonly loading = signal(false);
  readonly pedidos = signal<Pedido[]>([]);
  readonly total = signal(0);
  readonly paginaAtual = signal(1);
  readonly porPagina = signal(15);

  filtros = this.fb.group({
    estado: [''],
  });

  ngOnInit(): void {
    this.carregar();

    this.filtros.valueChanges.pipe(debounceTime(300), distinctUntilChanged()).subscribe(() => {
      this.paginaAtual.set(1);
      this.carregar();
    });
  }

  carregar(): void {
    this.loading.set(true);
    const { estado } = this.filtros.getRawValue();

    this.pedidoService.listar({
      estado: estado || undefined,
      per_page: this.porPagina(),
      page: this.paginaAtual(),
    }).subscribe({
      next: (res) => {
        this.pedidos.set(res.data);
        this.total.set(res.meta.total);
        this.loading.set(false);
      },
      error: () => this.loading.set(false),
    });
  }

  onPagina(event: PageEvent): void {
    this.paginaAtual.set(event.pageIndex + 1);
    this.porPagina.set(event.pageSize);
    this.carregar();
  }

  limparFiltros(): void {
    this.filtros.reset({ estado: '' });
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
}
