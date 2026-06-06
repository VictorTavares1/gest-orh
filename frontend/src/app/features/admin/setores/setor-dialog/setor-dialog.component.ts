import { Component, inject, OnInit, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';

import { AdminService, SetorAdmin, OrganizacaoAdmin } from '../../../../core/services/admin.service';

export interface SetorDialogData {
  setor?: SetorAdmin;
  organizacoes: OrganizacaoAdmin[];
}

@Component({
  selector: 'app-setor-dialog',
  standalone: true,
  imports: [
    ReactiveFormsModule, MatDialogModule, MatButtonModule,
    MatIconModule, MatFormFieldModule, MatInputModule,
    MatSelectModule, MatProgressSpinnerModule,
  ],
  template: `
    <div class="dialog-header">
      <mat-icon>{{ isEdicao ? 'edit' : 'add_business' }}</mat-icon>
      <h2>{{ isEdicao ? 'Editar Setor' : 'Novo Setor' }}</h2>
    </div>

    <mat-dialog-content>
      <form [formGroup]="form" class="form">

        <mat-form-field appearance="outline">
          <mat-label>Nome do setor</mat-label>
          <mat-icon matIconPrefix>business</mat-icon>
          <input matInput formControlName="nome" placeholder="Ex: Recursos Humanos" autocomplete="off" />
          @if (form.controls.nome.hasError('required') && form.controls.nome.touched) {
            <mat-error>Nome é obrigatório</mat-error>
          }
        </mat-form-field>

        <mat-form-field appearance="outline">
          <mat-label>Organização</mat-label>
          <mat-icon matIconPrefix>domain</mat-icon>
          <mat-select formControlName="id_organizacao">
            @for (o of data.organizacoes; track o.id_organizacao) {
              <mat-option [value]="o.id_organizacao">{{ o.nome }}</mat-option>
            }
          </mat-select>
          @if (form.controls.id_organizacao.hasError('min') && form.controls.id_organizacao.touched) {
            <mat-error>Selecione uma organização</mat-error>
          }
        </mat-form-field>

      </form>
    </mat-dialog-content>

    <mat-dialog-actions align="end">
      <button mat-stroked-button (click)="cancelar()" [disabled]="gravando()">Cancelar</button>
      <button mat-flat-button color="primary" (click)="gravar()" [disabled]="gravando()">
        @if (gravando()) { <mat-spinner diameter="18" /> } @else { <mat-icon>save</mat-icon> }
        {{ isEdicao ? 'Guardar' : 'Criar setor' }}
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
    mat-dialog-content { padding: 20px 24px !important; min-width: 400px; }
    .form { display: flex; flex-direction: column; gap: 4px; mat-form-field { width: 100%; } }
    mat-dialog-actions { padding: 12px 24px 20px !important; gap: 8px;
      button { display: flex; align-items: center; gap: 6px; border-radius: 8px !important; }
    }
  `],
})
export class SetorDialogComponent implements OnInit {
  readonly data = inject<SetorDialogData>(MAT_DIALOG_DATA);
  private ref = inject(MatDialogRef<SetorDialogComponent>);
  private fb = inject(FormBuilder);
  private adminService = inject(AdminService);

  readonly isEdicao = !!this.data.setor;
  readonly gravando = signal(false);

  form = this.fb.nonNullable.group({
    nome:            ['', [Validators.required, Validators.maxLength(255)]],
    id_organizacao:  [0, [Validators.required, Validators.min(1)]],
  });

  ngOnInit(): void {
    if (this.isEdicao) {
      this.form.patchValue({
        nome:           this.data.setor!.nome,
        id_organizacao: this.data.setor!.organizacao?.id_organizacao ?? 0,
      });
    } else if (this.data.organizacoes.length === 1) {
      this.form.patchValue({ id_organizacao: this.data.organizacoes[0].id_organizacao });
    }
  }

  gravar(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.gravando.set(true);
    const dados = this.form.getRawValue();

    const obs = this.isEdicao
      ? this.adminService.editarSetor(this.data.setor!.id_setor, dados)
      : this.adminService.criarSetor(dados);

    obs.subscribe({
      next: (res) => { this.gravando.set(false); this.ref.close(res.data); },
      error: (err) => { this.gravando.set(false); this.ref.close({ erro: err?.error?.message ?? 'Erro ao guardar.' }); },
    });
  }

  cancelar(): void { this.ref.close(null); }
}
