import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';

import { AdminService, OrganizacaoAdmin } from '../../../../core/services/admin.service';

export interface OrganizacaoDialogData {
  organizacao?: OrganizacaoAdmin;
}

@Component({
  selector: 'app-organizacao-dialog',
  standalone: true,
  imports: [
    ReactiveFormsModule, MatDialogModule, MatButtonModule,
    MatIconModule, MatFormFieldModule, MatInputModule, MatProgressSpinnerModule,
  ],
  template: `
    <div class="dialog-header">
      <mat-icon>{{ isEdicao ? 'edit' : 'domain_add' }}</mat-icon>
      <h2>{{ isEdicao ? 'Editar Organização' : 'Nova Organização' }}</h2>
    </div>

    <mat-dialog-content>
      <form [formGroup]="form" class="form">
        <mat-form-field appearance="outline">
          <mat-label>Nome da organização</mat-label>
          <mat-icon matIconPrefix>domain</mat-icon>
          <input matInput formControlName="nome" placeholder="Ex: Santa Casa da Misericórdia" autocomplete="off" />
          @if (form.controls.nome.hasError('required') && form.controls.nome.touched) {
            <mat-error>Nome é obrigatório</mat-error>
          }
        </mat-form-field>
      </form>
    </mat-dialog-content>

    <mat-dialog-actions align="end">
      <button mat-stroked-button (click)="cancelar()" [disabled]="gravando()">Cancelar</button>
      <button mat-flat-button color="primary" (click)="gravar()" [disabled]="gravando()">
        @if (gravando()) { <mat-spinner diameter="18" /> } @else { <mat-icon>save</mat-icon> }
        {{ isEdicao ? 'Guardar' : 'Criar organização' }}
      </button>
    </mat-dialog-actions>
  `,
  styles: [`
    .dialog-header {
      display: flex; align-items: center; gap: 12px;
      padding: 20px 24px 16px; background: #f8fafc;
      border-bottom: 1px solid #e5e7eb; border-radius: 12px 12px 0 0;
      mat-icon { color: #4f46e5; font-size: 24px; width: 24px; height: 24px; }
      h2 { margin: 0; font-size: 18px; font-weight: 700; color: #111827; }
    }
    mat-dialog-content { padding: 20px 24px !important; min-width: 360px; }
    .form { mat-form-field { width: 100%; } }
    mat-dialog-actions { padding: 12px 24px 20px !important; gap: 8px;
      button { display: flex; align-items: center; gap: 6px; border-radius: 8px !important; }
    }
  `],
})
export class OrganizacaoDialogComponent {
  readonly data = inject<OrganizacaoDialogData>(MAT_DIALOG_DATA);
  private ref = inject(MatDialogRef<OrganizacaoDialogComponent>);
  private fb = inject(FormBuilder);
  private adminService = inject(AdminService);

  readonly isEdicao = !!this.data.organizacao;
  readonly gravando = signal(false);

  form = this.fb.nonNullable.group({
    nome: [this.data.organizacao?.nome ?? '', [Validators.required, Validators.maxLength(255)]],
  });

  gravar(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.gravando.set(true);
    const dados = { nome: this.form.getRawValue().nome };

    const obs = this.isEdicao
      ? this.adminService.editarOrganizacao(this.data.organizacao!.id_organizacao, dados)
      : this.adminService.criarOrganizacao(dados);

    obs.subscribe({
      next: (res) => { this.gravando.set(false); this.ref.close(res.data); },
      error: (err) => { this.gravando.set(false); this.ref.close({ erro: err?.error?.message ?? 'Erro ao guardar.' }); },
    });
  }

  cancelar(): void { this.ref.close(null); }
}
