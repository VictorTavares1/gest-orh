import { Component, inject, signal, computed, OnInit } from '@angular/core';
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
import { MatSelectModule } from '@angular/material/select';
import { MatChipsModule } from '@angular/material/chips';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';

import { AdminService, UtilizadorAdmin } from '../../../core/services/admin.service';
import { UtilizadorDialogComponent, UtilizadorDialogData } from './utilizador-dialog/utilizador-dialog.component';

@Component({
  selector: 'app-admin-utilizadores',
  standalone: true,
  imports: [
    FormsModule,
    MatTableModule, MatButtonModule, MatIconModule, MatCardModule,
    MatProgressBarModule, MatTooltipModule, MatDialogModule, MatSnackBarModule,
    MatFormFieldModule, MatInputModule, MatSelectModule, MatChipsModule, MatSlideToggleModule,
  ],
  templateUrl: './admin-utilizadores.component.html',
  styleUrl: './admin-utilizadores.component.scss',
})
export class AdminUtilizadoresComponent implements OnInit {
  private adminService = inject(AdminService);
  private dialog = inject(MatDialog);
  private snack = inject(MatSnackBar);

  readonly colunas = ['avatar', 'nome', 'setor', 'tipo', 'estado', 'acoes'];
  readonly tipoLabel = this.adminService.tipoLabel;

  readonly loading = signal(false);
  readonly processando = signal<number | null>(null);
  readonly utilizadores = signal<UtilizadorAdmin[]>([]);

  pesquisa = '';
  filtroTipo = '';
  filtroAtivo = '';

  readonly utilizadoresFiltrados = computed(() => {
    let lista = this.utilizadores();
    const termo = this.pesquisa.toLowerCase().trim();
    if (termo) lista = lista.filter(u =>
      u.nome.toLowerCase().includes(termo) ||
      u.email.toLowerCase().includes(termo) ||
      u.setor?.nome?.toLowerCase().includes(termo)
    );
    if (this.filtroTipo) lista = lista.filter(u => u.tipo_utilizador?.nome === this.filtroTipo);
    if (this.filtroAtivo !== '') lista = lista.filter(u => String(u.ativo) === this.filtroAtivo);
    return lista;
  });

  readonly tiposUtilizador = ['FUNCIONARIO', 'DIRETOR_TECNICO', 'DIRETORA_EXECUTIVA'];

  ngOnInit(): void {
    this.carregar();
  }

  carregar(): void {
    this.loading.set(true);
    this.adminService.listarUtilizadores().subscribe({
      next: (res) => { this.utilizadores.set(res.data); this.loading.set(false); },
      error: () => this.loading.set(false),
    });
  }

  abrirCriar(): void {
    const data: UtilizadorDialogData = {};
    this.dialog.open(UtilizadorDialogComponent, { data, width: '560px', disableClose: true })
      .afterClosed()
      .subscribe((resultado) => {
        if (!resultado) return;
        if (resultado.erro) {
          this.snack.open(resultado.erro, 'Fechar', { duration: 5000, panelClass: 'snack-error' });
          return;
        }
        this.snack.open('Utilizador criado com sucesso.', 'Fechar', { duration: 4000, panelClass: 'snack-success' });
        this.carregar();
      });
  }

  abrirEditar(utilizador: UtilizadorAdmin): void {
    const data: UtilizadorDialogData = { utilizador };
    this.dialog.open(UtilizadorDialogComponent, { data, width: '560px', disableClose: true })
      .afterClosed()
      .subscribe((resultado) => {
        if (!resultado) return;
        if (resultado.erro) {
          this.snack.open(resultado.erro, 'Fechar', { duration: 5000, panelClass: 'snack-error' });
          return;
        }
        this.snack.open('Utilizador atualizado.', 'Fechar', { duration: 4000, panelClass: 'snack-success' });
        this.carregar();
      });
  }

  toggleAtivo(utilizador: UtilizadorAdmin): void {
    this.processando.set(utilizador.id_utilizador);
    const acao = utilizador.ativo
      ? this.adminService.desativarUtilizador(utilizador.id_utilizador)
      : this.adminService.ativarUtilizador(utilizador.id_utilizador);

    acao.subscribe({
      next: () => {
        const msg = utilizador.ativo ? 'Conta desativada.' : 'Conta ativada.';
        this.snack.open(msg, 'Fechar', { duration: 3000 });
        this.processando.set(null);
        this.carregar();
      },
      error: (err) => {
        this.snack.open(err?.error?.message ?? 'Erro ao alterar estado.', 'Fechar', { duration: 5000, panelClass: 'snack-error' });
        this.processando.set(null);
      },
    });
  }

  iniciais(nome: string): string {
    return nome.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase();
  }

  limparFiltros(): void {
    this.pesquisa = '';
    this.filtroTipo = '';
    this.filtroAtivo = '';
  }
}
