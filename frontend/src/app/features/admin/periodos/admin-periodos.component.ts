import { Component, inject, signal, computed, OnInit } from '@angular/core';
import { DatePipe } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { MatTableModule } from '@angular/material/table';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatCardModule } from '@angular/material/card';
import { MatProgressBarModule } from '@angular/material/progress-bar';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatDialogModule, MatDialog } from '@angular/material/dialog';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';

import { AdminService, PeriodoAdmin } from '../../../core/services/admin.service';
import { ConfirmarDialogComponent } from '../../../shared/components/confirmar-dialog/confirmar-dialog.component';
import { PeriodoDialogComponent } from './periodo-dialog/periodo-dialog.component';

@Component({
  selector: 'app-admin-periodos',
  standalone: true,
  imports: [
    FormsModule, DatePipe,
    MatTableModule, MatButtonModule, MatIconModule, MatCardModule,
    MatProgressBarModule, MatTooltipModule, MatDialogModule, MatSnackBarModule,
    MatFormFieldModule, MatInputModule,
  ],
  templateUrl: './admin-periodos.component.html',
  styleUrl: './admin-periodos.component.scss',
})
export class AdminPeriodosComponent implements OnInit {
  private adminService = inject(AdminService);
  private dialog = inject(MatDialog);
  private snack = inject(MatSnackBar);

  readonly colunas = ['data_inicio', 'data_fim', 'duracao', 'acoes'];
  readonly loading = signal(false);
  readonly periodos = signal<PeriodoAdmin[]>([]);

  pesquisa = '';

  readonly periodosFiltrados = computed(() => {
    const termo = this.pesquisa.toLowerCase().trim();
    if (!termo) return this.periodos();
    return this.periodos().filter(p =>
      p.data_inicio.includes(termo) || p.data_fim.includes(termo)
    );
  });

  ngOnInit(): void {
    this.carregar();
  }

  carregar(): void {
    this.loading.set(true);
    this.adminService.listarPeriodos().subscribe({
      next: (res) => { this.periodos.set(res.data); this.loading.set(false); },
      error: () => this.loading.set(false),
    });
  }

  duracaoDias(inicio: string, fim: string): number {
    const d1 = new Date(inicio);
    const d2 = new Date(fim);
    return Math.round((d2.getTime() - d1.getTime()) / (1000 * 60 * 60 * 24));
  }

  abrirCriar(): void {
    this.dialog.open(PeriodoDialogComponent, {
      data: {},
      width: '460px',
      disableClose: true,
    }).afterClosed().subscribe((resultado) => {
      if (!resultado) return;
      if (resultado.erro) { this.snack.open(resultado.erro, 'Fechar', { duration: 5000, panelClass: 'snack-error' }); return; }
      this.snack.open('Período criado com sucesso.', 'Fechar', { duration: 3000, panelClass: 'snack-success' });
      this.carregar();
    });
  }

  abrirEditar(periodo: PeriodoAdmin): void {
    this.dialog.open(PeriodoDialogComponent, {
      data: { periodo },
      width: '460px',
      disableClose: true,
    }).afterClosed().subscribe((resultado) => {
      if (!resultado) return;
      if (resultado.erro) { this.snack.open(resultado.erro, 'Fechar', { duration: 5000, panelClass: 'snack-error' }); return; }
      this.snack.open('Período atualizado.', 'Fechar', { duration: 3000, panelClass: 'snack-success' });
      this.carregar();
    });
  }

  eliminar(periodo: PeriodoAdmin): void {
    this.dialog.open(ConfirmarDialogComponent, {
      data: {
        titulo: 'Eliminar Período',
        mensagem: `Tem a certeza que pretende eliminar o período de <strong>${periodo.data_inicio}</strong> a <strong>${periodo.data_fim}</strong>?`,
        corBotao: 'warn',
        textoBotao: 'Eliminar',
      },
      width: '400px',
    }).afterClosed().subscribe((confirmado: boolean) => {
      if (!confirmado) return;
      this.adminService.eliminarPeriodo(periodo.id_periodo).subscribe({
        next: () => {
          this.snack.open('Período eliminado.', 'Fechar', { duration: 3000 });
          this.carregar();
        },
        error: (err) => this.snack.open(err?.error?.message ?? 'Erro ao eliminar.', 'Fechar', { duration: 5000, panelClass: 'snack-error' }),
      });
    });
  }
}
