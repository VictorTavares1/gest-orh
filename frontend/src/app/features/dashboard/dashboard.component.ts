import { Component, inject, signal, OnInit } from '@angular/core';
import { DatePipe, TitleCasePipe } from '@angular/common';

import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';
import { MatChipsModule } from '@angular/material/chips';
import { MatProgressBarModule } from '@angular/material/progress-bar';
import { MatDividerModule } from '@angular/material/divider';
import { RouterLink } from '@angular/router';

import { DashboardService } from '../../core/services/dashboard.service';
import { AuthService } from '../../core/services/auth.service';
import {
  DashboardData,
  DashboardFuncionario,
  DashboardDiretor,
  DashboardExecutiva,
  PedidoRecente,
  PorEstado,
} from '../../core/models/dashboard.model';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [
    DatePipe,
    MatCardModule, MatIconModule, MatButtonModule,
    MatChipsModule, MatProgressBarModule, MatDividerModule,
    RouterLink,
  ],
  templateUrl: './dashboard.component.html',
  styleUrl: './dashboard.component.scss',
})
export class DashboardComponent implements OnInit {
  private dashboardService = inject(DashboardService);
  readonly auth = inject(AuthService);

  readonly loading = signal(true);
  readonly data = signal<DashboardData | null>(null);
  readonly error = signal<string | null>(null);

  ngOnInit(): void {
    this.dashboardService.resumo().subscribe({
      next: (res) => {
        this.data.set(res.data);
        this.loading.set(false);
      },
      error: () => {
        this.error.set('Não foi possível carregar o dashboard.');
        this.loading.set(false);
      },
    });
  }

  get asFuncionario(): DashboardFuncionario | null {
    const d = this.data();
    return d?.tipo === 'funcionario' ? d : null;
  }

  get asDiretor(): DashboardDiretor | null {
    const d = this.data();
    return d?.tipo === 'diretor_tecnico' ? d : null;
  }

  get asExecutiva(): DashboardExecutiva | null {
    const d = this.data();
    return d?.tipo === 'diretora_executiva' ? d : null;
  }

  estadoLabel(estado: string): string {
    const map: Record<string, string> = {
      RASCUNHO: 'Rascunho',
      PENDENTE: 'Pendente',
      EM_APROVACAO_COLEGA: 'Em aprovação (colega)',
      EM_APROVACAO_DIRETOR: 'Em aprovação (diretor)',
      EM_APROVACAO_EXECUTIVA: 'Em aprovação (executiva)',
      APROVADO: 'Aprovado',
      REJEITADO: 'Rejeitado',
      CANCELADO: 'Cancelado',
    };
    return map[estado] ?? estado;
  }

  estadoColor(estado: string): string {
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
    return map[estado] ?? 'estado-rascunho';
  }

  estadoEntries(porEstado: PorEstado): { key: string; value: number }[] {
    return Object.entries(porEstado)
      .map(([key, value]) => ({ key, value }))
      .filter((e) => e.value > 0);
  }

  trackById(_: number, item: PedidoRecente): number {
    return item.id_pedido;
  }
}
