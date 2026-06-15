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
  PENDENTE:               'Em aprovação',
  EM_APROVACAO_COLEGA:    'Em aprovação',
  EM_APROVACAO_DIRETOR:   'Em aprovação',
  EM_APROVACAO_EXECUTIVA: 'Em aprovação',
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
  readonly exportLoading = signal(false);
  readonly pedidos = signal<Pedido[]>([]);
  readonly dataGeracao = signal<Date | null>(null);

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
        mapa[id] = { nome: p.utilizador?.nome ?? '—', setor: p.utilizador?.setor?.nome ?? '—', total: 0};
      }
      mapa[id].total++;
    }
    return Object.values(mapa).sort((a, b) => b.total - a.total).slice(0, 10);
  });

  readonly porSetor = computed((): LinhaSetor[] => {
    const mapa: Record<string, number> = {};
    for (const p of this.pedidos()) {
      const nome = p.utilizador?.setor?.nome ?? 'Sem setor';
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

  readonly maxTipo = computed(() =>
    Math.max(1, ...this.porTipo().map(t => t.total))
  );

  readonly maxFuncionario = computed(() =>
    Math.max(1, ...this.porFuncionario().map(f => f.total))
  );

  readonly maxSetor = computed(() =>
    Math.max(1, ...this.porSetor().map(s => s.total))
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

  async exportarPDF(): Promise<void> {
    this.exportLoading.set(true);
    try {
      const { jsPDF } = await import('jspdf');
      const autoTable = (await import('jspdf-autotable')).default;

      const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
      const largura = doc.internal.pageSize.getWidth();
      const agora = this.dataGeracao() ?? new Date();
      const dataStr = agora.toLocaleDateString('pt-PT') + ' ' + agora.toLocaleTimeString('pt-PT', { hour: '2-digit', minute: '2-digit' });

      // Cabeçalho
      doc.setFillColor(79, 70, 229);
      doc.rect(0, 0, largura, 28, 'F');
      doc.setTextColor(255, 255, 255);
      doc.setFontSize(18);
      doc.setFont('helvetica', 'bold');
      doc.text('Gestão RH — Relatório Mensal', 14, 12);
      doc.setFontSize(11);
      doc.setFont('helvetica', 'normal');
      doc.text(`Período: ${this.tituloMes}`, 14, 21);
      doc.text(`Gerado em: ${dataStr}`, largura - 14, 21, { align: 'right' });

      let y = 36;

      // Resumo
      doc.setTextColor(17, 24, 39);
      doc.setFontSize(13);
      doc.setFont('helvetica', 'bold');
      doc.text('Resumo Geral', 14, y);
      y += 6;

      autoTable(doc, {
        startY: y,
        head: [['Estado', 'Total']],
        body: this.porEstado().map(e => [e.label, String(e.total)]),
        foot: [['Total de pedidos', String(this.totalPedidos())]],
        styles: { fontSize: 10, cellPadding: 3 },
        headStyles: { fillColor: [99, 102, 241] },
        footStyles: { fillColor: [238, 242, 255], textColor: [55, 48, 163], fontStyle: 'bold' },
      });

      y = (doc as any).lastAutoTable.finalY + 10;

      // Por Tipo
      doc.setFontSize(13);
      doc.setFont('helvetica', 'bold');
      doc.setTextColor(17, 24, 39);
      doc.text('Pedidos por Tipo', 14, y);
      y += 6;

      autoTable(doc, {
        startY: y,
        head: [['Tipo', 'Total', '%']],
        body: this.porTipo().map(t => [t.nome, String(t.total), `${t.percentagem}%`]),
        styles: { fontSize: 10, cellPadding: 3 },
        headStyles: { fillColor: [99, 102, 241] },
      });

      y = (doc as any).lastAutoTable.finalY + 10;

      // Por Setor
      if (y > 240) { doc.addPage(); y = 20; }
      doc.setFontSize(13);
      doc.setFont('helvetica', 'bold');
      doc.setTextColor(17, 24, 39);
      doc.text('Pedidos por Setor', 14, y);
      y += 6;

      autoTable(doc, {
        startY: y,
        head: [['Setor', 'Total', '%']],
        body: this.porSetor().map(s => [s.nome, String(s.total), `${s.percentagem}%`]),
        styles: { fontSize: 10, cellPadding: 3 },
        headStyles: { fillColor: [16, 185, 129] },
      });

      y = (doc as any).lastAutoTable.finalY + 10;

      // Top Funcionários
      if (y > 220) { doc.addPage(); y = 20; }
      doc.setFontSize(13);
      doc.setFont('helvetica', 'bold');
      doc.setTextColor(17, 24, 39);
      doc.text('Top 10 Funcionários', 14, y);
      y += 6;

      autoTable(doc, {
        startY: y,
        head: [['#', 'Funcionário', 'Setor', 'Pedidos']],
        body: this.porFuncionario().map((f, i) => [String(i + 1), f.nome, f.setor, String(f.total)]),
        styles: { fontSize: 10, cellPadding: 3 },
        headStyles: { fillColor: [99, 102, 241] },
      });

      // Rodapé em todas as páginas
      const totalPages = (doc as any).internal.getNumberOfPages();
      for (let i = 1; i <= totalPages; i++) {
        doc.setPage(i);
        doc.setFontSize(8);
        doc.setTextColor(156, 163, 175);
        doc.text(
          `Gestão RH · Centro Paroquial · Pág. ${i} / ${totalPages}`,
          largura / 2, doc.internal.pageSize.getHeight() - 8,
          { align: 'center' }
        );
      }

      doc.save(`relatorio_${this.anoSelecionado}_${String(this.mesSelecionado).padStart(2, '0')}.pdf`);
    } finally {
      this.exportLoading.set(false);
    }
  }
}
