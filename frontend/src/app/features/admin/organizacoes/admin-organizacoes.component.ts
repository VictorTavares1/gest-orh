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
import { MatChipsModule } from '@angular/material/chips';

import { AdminService, OrganizacaoAdmin } from '../../../core/services/admin.service';
import { ConfirmarDialogComponent } from '../../../shared/components/confirmar-dialog/confirmar-dialog.component';
import { OrganizacaoDialogComponent } from './organizacao-dialog/organizacao-dialog.component';

@Component({
  selector: 'app-admin-organizacoes',
  standalone: true,
  imports: [
    FormsModule,
    MatTableModule, MatButtonModule, MatIconModule, MatCardModule,
    MatProgressBarModule, MatTooltipModule, MatDialogModule, MatSnackBarModule,
    MatFormFieldModule, MatInputModule, MatChipsModule,
  ],
  templateUrl: './admin-organizacoes.component.html',
  styleUrl: './admin-organizacoes.component.scss',
})
export class AdminOrganizacoesComponent implements OnInit {
  private adminService = inject(AdminService);
  private dialog = inject(MatDialog);
  private snack = inject(MatSnackBar);

  readonly colunas = ['nome', 'setores', 'acoes'];
  readonly loading = signal(false);
  readonly organizacoes = signal<OrganizacaoAdmin[]>([]);

  pesquisa = '';

  readonly organizacoesFiltradas = computed(() => {
    const termo = this.pesquisa.toLowerCase().trim();
    if (!termo) return this.organizacoes();
    return this.organizacoes().filter(o => o.nome.toLowerCase().includes(termo));
  });

  ngOnInit(): void {
    this.carregar();
  }

  carregar(): void {
    this.loading.set(true);
    this.adminService.listarOrganizacoes().subscribe({
      next: (res) => { this.organizacoes.set(res.data); this.loading.set(false); },
      error: () => this.loading.set(false),
    });
  }

  abrirCriar(): void {
    this.dialog.open(OrganizacaoDialogComponent, { data: {}, width: '420px', disableClose: true })
      .afterClosed().subscribe((resultado) => {
        if (!resultado) return;
        if (resultado.erro) { this.snack.open(resultado.erro, 'Fechar', { duration: 5000, panelClass: 'snack-error' }); return; }
        this.snack.open('Organização criada com sucesso.', 'Fechar', { duration: 3000, panelClass: 'snack-success' });
        this.carregar();
      });
  }

  abrirEditar(org: OrganizacaoAdmin): void {
    this.dialog.open(OrganizacaoDialogComponent, { data: { organizacao: org }, width: '420px', disableClose: true })
      .afterClosed().subscribe((resultado) => {
        if (!resultado) return;
        if (resultado.erro) { this.snack.open(resultado.erro, 'Fechar', { duration: 5000, panelClass: 'snack-error' }); return; }
        this.snack.open('Organização atualizada.', 'Fechar', { duration: 3000, panelClass: 'snack-success' });
        this.carregar();
      });
  }

  eliminar(org: OrganizacaoAdmin): void {
    this.dialog.open(ConfirmarDialogComponent, {
      data: {
        titulo: 'Eliminar Organização',
        mensagem: `Tem a certeza que pretende eliminar <strong>${org.nome}</strong>? Só é possível eliminar organizações sem setores associados.`,
        corBotao: 'warn',
        textoBotao: 'Eliminar',
      },
      width: '420px',
    }).afterClosed().subscribe((confirmado: boolean) => {
      if (!confirmado) return;
      this.adminService.eliminarOrganizacao(org.id_organizacao).subscribe({
        next: () => { this.snack.open('Organização eliminada.', 'Fechar', { duration: 3000 }); this.carregar(); },
        error: (err) => this.snack.open(err?.error?.message ?? 'Erro ao eliminar.', 'Fechar', { duration: 5000, panelClass: 'snack-error' }),
      });
    });
  }
}
