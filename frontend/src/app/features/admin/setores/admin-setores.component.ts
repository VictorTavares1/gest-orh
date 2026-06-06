import { Component, inject, signal, computed, OnInit } from '@angular/core';
import { DatePipe } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';

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
import { forkJoin } from 'rxjs';

import { AdminService, SetorAdmin, OrganizacaoAdmin } from '../../../core/services/admin.service';
import { ConfirmarDialogComponent } from '../../../shared/components/confirmar-dialog/confirmar-dialog.component';
import { SetorDialogComponent } from './setor-dialog/setor-dialog.component';

@Component({
  selector: 'app-admin-setores',
  standalone: true,
  imports: [
    FormsModule, ReactiveFormsModule, DatePipe,
    MatTableModule, MatButtonModule, MatIconModule, MatCardModule,
    MatProgressBarModule, MatTooltipModule, MatDialogModule, MatSnackBarModule,
    MatFormFieldModule, MatInputModule, MatSelectModule,
  ],
  templateUrl: './admin-setores.component.html',
  styleUrl: './admin-setores.component.scss',
})
export class AdminSetoresComponent implements OnInit {
  private adminService = inject(AdminService);
  private dialog = inject(MatDialog);
  private snack = inject(MatSnackBar);

  readonly colunas = ['nome', 'organizacao', 'acoes'];
  readonly loading = signal(false);
  readonly setores = signal<SetorAdmin[]>([]);
  readonly organizacoes = signal<OrganizacaoAdmin[]>([]);

  pesquisa = '';

  readonly setoresFiltrados = computed(() => {
    const termo = this.pesquisa.toLowerCase().trim();
    if (!termo) return this.setores();
    return this.setores().filter(s =>
      s.nome.toLowerCase().includes(termo) ||
      s.organizacao?.nome.toLowerCase().includes(termo)
    );
  });

  ngOnInit(): void {
    this.carregar();
  }

  carregar(): void {
    this.loading.set(true);
    forkJoin([
      this.adminService.listarSetores(),
      this.adminService.listarOrganizacoes(),
    ]).subscribe({
      next: ([setoresRes, orgsRes]) => {
        this.setores.set(setoresRes.data);
        this.organizacoes.set(orgsRes.data);
        this.loading.set(false);
      },
      error: () => this.loading.set(false),
    });
  }

  abrirCriar(): void {
    this.dialog.open(SetorDialogComponent, {
      data: { organizacoes: this.organizacoes() },
      width: '460px',
      disableClose: true,
    }).afterClosed().subscribe((resultado) => {
      if (!resultado) return;
      if (resultado.erro) { this.snack.open(resultado.erro, 'Fechar', { duration: 5000, panelClass: 'snack-error' }); return; }
      this.snack.open('Setor criado com sucesso.', 'Fechar', { duration: 3000, panelClass: 'snack-success' });
      this.carregar();
    });
  }

  abrirEditar(setor: SetorAdmin): void {
    this.dialog.open(SetorDialogComponent, {
      data: { setor, organizacoes: this.organizacoes() },
      width: '460px',
      disableClose: true,
    }).afterClosed().subscribe((resultado) => {
      if (!resultado) return;
      if (resultado.erro) { this.snack.open(resultado.erro, 'Fechar', { duration: 5000, panelClass: 'snack-error' }); return; }
      this.snack.open('Setor atualizado.', 'Fechar', { duration: 3000, panelClass: 'snack-success' });
      this.carregar();
    });
  }

  eliminar(setor: SetorAdmin): void {
    this.dialog.open(ConfirmarDialogComponent, {
      data: {
        titulo: 'Eliminar Setor',
        mensagem: `Tem a certeza que pretende eliminar o setor <strong>${setor.nome}</strong>? Esta ação é irreversível.`,
        corBotao: 'warn',
        textoBotao: 'Eliminar',
      },
      width: '400px',
    }).afterClosed().subscribe((confirmado: boolean) => {
      if (!confirmado) return;
      this.adminService.eliminarSetor(setor.id_setor).subscribe({
        next: () => {
          this.snack.open('Setor eliminado.', 'Fechar', { duration: 3000 });
          this.carregar();
        },
        error: (err) => this.snack.open(err?.error?.message ?? 'Erro ao eliminar.', 'Fechar', { duration: 5000, panelClass: 'snack-error' }),
      });
    });
  }
}
