import { Component, inject, signal, computed, OnInit } from '@angular/core';
import { DatePipe } from '@angular/common';

import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatSelectModule } from '@angular/material/select';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatProgressBarModule } from '@angular/material/progress-bar';
import { MatDividerModule } from '@angular/material/divider';
import { MatTooltipModule } from '@angular/material/tooltip';
import { FormsModule } from '@angular/forms';

import { PedidoService } from '../../../core/services/pedido.service';
import { Pedido } from '../../../core/models/pedido.model';

const MESES = [
  'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro',
];

const ESTADO_LABEL: Record<string, string> = {
  RASCUNHO:               'Rascunho',
  PENDENTE:               'Pendente',
  EM_APROVACAO_COLEGA:    'Em aprovação (colega)',
  EM_APROVACAO_DIRETOR:   'Em aprovação (diretor)',
  EM_APROVACAO_EXECUTIVA: 'Em aprovação (executiva)',
  APROVADO:               'Aprovado',
  REJEITADO:              'Rejeitado',
  CANCELADO:              'Cancelado',
};

interface LinhaEstado {
  label: string;
  nome: string;
  total: number;
  cor: string;
}

interface LinhaTipo {
  nome: string;
  total: number;
  percentagem: number;
}

interface LinhaFuncionario {
  nome: string;
  setor: string;
  total: number;
}

interface LinhaSetor {
  nome: string;
  total: number;
  percentagem: number;
}

@Component({
  selector: 'app-admin-relatorio',
  standalone: true,
  imports: [
    DatePipe, FormsModule,
    MatCardModule, MatButtonModule, MatIconModule, MatSelectModule,
    MatFormFieldModule, MatProgressBarModule, MatDividerModule, MatTooltipModule,
  ],
  templateUrl: './admin-relatorio.component.html',
  styleUrl: './admin-relatorio.component.scss',
})
export class AdminRelatorioComponent implements OnInit {
  private pedidoService = inject(PedidoService);

  readonly meses = MESES;
  readonly loading = signal(false);
  readonly pedidos = signal<Pedido[]>([]);
  readonly dataGeracao = signal<Date | null>(null);

  // Mês anterior por defeito (comportamento automático do 1º dia do mês)
  mesSelecionado: number;
  anoSelecionado: number;

  readonly anos: number[];

  constructor() {
    const hoje = new Date();
    const mesAnterior = new Date(hoje.getFullYear(), hoje.getMonth() - 1, 1);
    this.mesSelecionado = mesAnterior.getMonth() + 1;
    this.anoSelecionado = mesAnterior.getFullYear();

    const anoAtual = hoje.getFullYear();
    this.anos = Array.from({ length: 3 }, (_, i) => anoAtual - i);
  }

  // ─── Métricas computadas ──────────────────────────────────────────────────

  readonly totalPedidos = computed(() => this.pedidos().length);

  readonly porEstado = computed((): LinhaEstado[] => {
    const mapa: Record<string, number> = {};
    for (const p of this.pedidos()) {
      const nome = p.estado?.nome ?? 'DESCONHECIDO';
      mapa[nome] = (mapa[nome] ?? 0) + 1;
    }
    const cores: Record<string, string> = {
      RASCUNHO:               '#9ca3af',
      PENDENTE:               '#f59e0b',
      EM_APROVACAO_COLEGA:    '#3b82f6',
      EM_APROVACAO_DIRETOR:   '#6366f1',
      EM_APROVACAO_EXECUTIVA: '#8b5cf6',
      APROVADO:               '#16a34a',
      REJEITADO:              '#dc2626',
      CANCELADO:              '#6b7280',
    };
    return Object.entries(mapa)
      .map(([nome, total]) => ({
        nome,
        label: ESTADO_LABEL[nome] ?? nome,
        total,
        cor: cores[nome] ?? '#9ca3af',
      }))
      .sort((a, b) => b.total - a.total);
  });

  readonly porTipo = computed((): LinhaTipo[] => {
    const mapa: Record<string, number> = {};
    for (const p of this.pedidos()) {
      const nome = p.tipo?.nome ?? 'Desconhecido';
      mapa[nome] = (mapa[nome] ?? 0) + 1;
    }
    const total = this.totalPedidos();
    return Object.entries(mapa)
      .map(([nome, count]) => ({
        nome,
        total: count,
        percentagem: total > 0 ? Math.round((count / total) * 100) : 0,
      }))
      .sort((a, b) => b.total - a.total);
  });

  readonly porFuncionario = computed((): LinhaFuncionario[] => {
    const mapa: Record<string, { nome: string; setor: string; total: number }> = {};
    for (const p of this.pedidos()) {
      const id = String(p.utilizador?.id ?? 'x');
      if (!mapa[id]) {
        mapa[id] = { nome: p.utilizador?.nome ?? '—', setor: '—', total: 0 };
      }
      mapa[id].total++;
    }
    return Object.values(mapa).sort((a, b) => b.total - a.total).slice(0, 10);
  });

  readonly maxTipo = computed(() =>
    Math.max(1, ...this.porTipo().map(t => t.total))
  );

  readonly maxFuncionario = computed(() =>
    Math.max(1, ...this.porFuncionario().map(f => f.total))
  );

  get tituloMes(): string {
    return `${MESES[this.mesSelecionado - 1]} ${this.anoSelecionado}`;
  }

  // ─── Lifecycle ────────────────────────────────────────────────────────────

  ngOnInit(): void {
    this.carregar();
  }

  carregar(): void {
    this.loading.set(true);
    this.pedidos.set([]);
    this.pedidoService.listar({
      mes: this.mesSelecionado,
      ano: this.anoSelecionado,
      per_page: 9999,
    }).subscribe({
      next: (res) => {
        this.pedidos.set(res.data);
        this.dataGeracao.set(new Date());
        this.loading.set(false);
      },
      error: () => this.loading.set(false),
    });
  }

  exportarPDF(): void {
    window.print();
  }
}
