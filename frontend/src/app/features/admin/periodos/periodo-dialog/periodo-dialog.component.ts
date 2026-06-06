import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';

import { AdminService, PeriodoAdmin } from '../../../../core/services/admin.service';

export interface PeriodoDialogData {
  periodo?: PeriodoAdmin;
}

@Component({
  selector: 'app-periodo-dialog',
  standalone: true,
  imports: [
    ReactiveFormsModule, MatDialogModule, MatButtonModule,
    MatIconModule, MatFormFieldModule, MatInputModule,
    MatDatepickerModule, MatProgressSpinnerModule,
  ],
  template: `
    <div class="dialog-header">
      <mat-icon>{{ isEdicao ? 'edit' : 'date_range' }}</mat-icon>
      <h2>{{ isEdicao ? 'Editar Período' : 'Novo Período' }}</h2>
    </div>

    <mat-dialog-content>
      <form [formGroup]="form" class="form">

        <mat-form-field appearance="outline">
          <mat-label>Data de início</mat-label>
          <mat-icon matIconPrefix>today</mat-icon>
          <input matInput [matDatepicker]="pickerInicio" formControlName="data_inicio" />
          <mat-datepicker-toggle matIconSuffix [for]="pickerInicio" />
          <mat-datepicker #pickerInicio />
          @if (form.controls.data_inicio.hasError('required') && form.controls.data_inicio.touched) {
            <mat-error>Data de início obrigatória</mat-error>
          }
        </mat-form-field>

        <mat-form-field appearance="outline">
          <mat-label>Data de fim</mat-label>
          <mat-icon matIconPrefix>event</mat-icon>
          <input matInput [matDatepicker]="pickerFim" formControlName="data_fim" />
          <mat-datepicker-toggle matIconSuffix [for]="pickerFim" />
          <mat-datepicker #pickerFim />
          @if (form.controls.data_fim.hasError('required') && form.controls.data_fim.touched) {
            <mat-error>Data de fim obrigatória</mat-error>
          }
        </mat-form-field>

      </form>
    </mat-dialog-content>

    <mat-dialog-actions align="end">
      <button mat-stroked-button (click)="cancelar()" [disabled]="gravando()">Cancelar</button>
      <button mat-flat-button color="primary" (click)="gravar()" [disabled]="gravando()">
        @if (gravando()) { <mat-spinner diameter="18" /> } @else { <mat-icon>save</mat-icon> }
        {{ isEdicao ? 'Guardar' : 'Criar período' }}
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
    mat-dialog-content { padding: 20px 24px !important; min-width: 380px; }
    .form { display: flex; flex-direction: column; gap: 4px; mat-form-field { width: 100%; } }
    mat-dialog-actions { padding: 12px 24px 20px !important; gap: 8px;
      button { display: flex; align-items: center; gap: 6px; border-radius: 8px !important; }
    }
  `],
})
export class PeriodoDialogComponent {
  readonly data = inject<PeriodoDialogData>(MAT_DIALOG_DATA);
  private ref = inject(MatDialogRef<PeriodoDialogComponent>);
  private fb = inject(FormBuilder);
  private adminService = inject(AdminService);

  readonly isEdicao = !!this.data.periodo;
  readonly gravando = signal(false);

  form = this.fb.nonNullable.group({
    data_inicio: [
      this.data.periodo ? new Date(this.data.periodo.data_inicio) : null as Date | null,
      [Validators.required],
    ],
    data_fim: [
      this.data.periodo ? new Date(this.data.periodo.data_fim) : null as Date | null,
      [Validators.required],
    ],
  });

  gravar(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.gravando.set(true);

    const { data_inicio, data_fim } = this.form.getRawValue();
    const formatar = (d: Date | null) => d ? `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}` : '';

    const dados = { data_inicio: formatar(data_inicio), data_fim: formatar(data_fim) };

    const obs = this.isEdicao
      ? this.adminService.editarPeriodo(this.data.periodo!.id_periodo, dados)
      : this.adminService.criarPeriodo(dados);

    obs.subscribe({
      next: (res) => { this.gravando.set(false); this.ref.close(res.data); },
      error: (err) => { this.gravando.set(false); this.ref.close({ erro: err?.error?.message ?? 'Erro ao guardar.' }); },
    });
  }

  cancelar(): void { this.ref.close(null); }
}
