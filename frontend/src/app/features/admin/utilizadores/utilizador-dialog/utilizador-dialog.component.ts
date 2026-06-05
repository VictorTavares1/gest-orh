import { Component, inject, OnInit, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { forkJoin } from 'rxjs';

import { AdminService, UtilizadorAdmin, SetorAdmin, TipoUtilizador, EditarUtilizadorDto } from '../../../../core/services/admin.service';

export interface UtilizadorDialogData {
  utilizador?: UtilizadorAdmin;
}

@Component({
  selector: 'app-utilizador-dialog',
  standalone: true,
  imports: [
    ReactiveFormsModule, MatDialogModule, MatButtonModule,
    MatIconModule, MatFormFieldModule, MatInputModule,
    MatSelectModule, MatProgressSpinnerModule,
  ],
  templateUrl: './utilizador-dialog.component.html',
  styleUrl: './utilizador-dialog.component.scss',
})
export class UtilizadorDialogComponent implements OnInit {
  readonly data = inject<UtilizadorDialogData>(MAT_DIALOG_DATA);
  private ref = inject(MatDialogRef<UtilizadorDialogComponent>);
  private fb = inject(FormBuilder);
  private adminService = inject(AdminService);

  readonly isEdicao = !!this.data.utilizador;
  readonly loadingRefs = signal(true);
  readonly gravando = signal(false);
  readonly mostrarPassword = signal(false);

  setores: SetorAdmin[] = [];
  tiposUtilizador: TipoUtilizador[] = [];
  tipoLabel = this.adminService.tipoLabel;

  form = this.fb.nonNullable.group({
    nome:               ['', [Validators.required, Validators.maxLength(255)]],
    email:              ['', [Validators.required, Validators.email]],
    password:           ['', this.isEdicao ? [] : [Validators.required, Validators.minLength(8)]],
    id_setor:           [0, [Validators.required, Validators.min(1)]],
    id_tipo_utilizador: [0, [Validators.required, Validators.min(1)]],
  });

  ngOnInit(): void {
    forkJoin([
      this.adminService.listarSetores(),
      this.adminService.listarUtilizadores(),
    ]).subscribe({
      next: ([setoresRes, utilizadoresRes]) => {
        this.setores = setoresRes.data;
        // Extrai tipos únicos dos utilizadores existentes
        const vistos = new Set<number>();
        this.tiposUtilizador = utilizadoresRes.data
          .map(u => u.tipo_utilizador)
          .filter((t): t is NonNullable<typeof t> => !!t && !vistos.has(t.id) && vistos.add(t.id) !== undefined)
          .map(t => ({ id_tipo_utilizador: t.id, nome: t.nome }));
        this.loadingRefs.set(false);
        if (this.isEdicao) this.preencherFormulario();
      },
      error: () => this.loadingRefs.set(false),
    });
  }

  private preencherFormulario(): void {
    const u = this.data.utilizador!;
    this.form.patchValue({
      nome:               u.nome,
      email:              u.email,
      id_setor:           u.setor?.id ?? 0,
      id_tipo_utilizador: u.tipo_utilizador?.id ?? 0,
    });
  }

  get titulo(): string {
    return this.isEdicao ? 'Editar Utilizador' : 'Novo Utilizador';
  }

  get nomeTipoUtilizador(): string {
    const id = this.form.controls.id_tipo_utilizador.value;
    const tipo = this.tiposUtilizador.find(t => t.id_tipo_utilizador === id);
    return tipo ? (this.tipoLabel[tipo.nome] ?? tipo.nome) : '';
  }

  gravar(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.gravando.set(true);
    const valores = this.form.getRawValue();

    if (this.isEdicao) {
      const dados: EditarUtilizadorDto = {
        nome:               valores.nome,
        email:              valores.email,
        id_setor:           valores.id_setor,
        id_tipo_utilizador: valores.id_tipo_utilizador,
      };
      if (valores.password) dados.password = valores.password;

      this.adminService.editarUtilizador(this.data.utilizador!.id_utilizador, dados).subscribe({
        next: (res) => { this.gravando.set(false); this.ref.close(res.data); },
        error: (err) => { this.gravando.set(false); this.ref.close({ erro: err?.error?.message ?? 'Erro ao guardar.' }); },
      });
    } else {
      this.adminService.criarUtilizador({
        nome:               valores.nome,
        email:              valores.email,
        password:           valores.password,
        id_setor:           valores.id_setor,
        id_tipo_utilizador: valores.id_tipo_utilizador,
      }).subscribe({
        next: (res) => { this.gravando.set(false); this.ref.close(res.data); },
        error: (err) => { this.gravando.set(false); this.ref.close({ erro: err?.error?.message ?? 'Erro ao criar utilizador.' }); },
      });
    }
  }

  cancelar(): void {
    this.ref.close(null);
  }
}
