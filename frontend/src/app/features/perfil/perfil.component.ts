import { Component, inject, signal, OnInit } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';

import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatDividerModule } from '@angular/material/divider';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';

import { AuthService } from '../../core/services/auth.service';

@Component({
  selector: 'app-perfil',
  standalone: true,
  imports: [
    ReactiveFormsModule,
    MatCardModule, MatButtonModule, MatIconModule,
    MatFormFieldModule, MatInputModule, MatDividerModule,
    MatProgressSpinnerModule,
  ],
  templateUrl: './perfil.component.html',
  styleUrl: './perfil.component.scss',
})
export class PerfilComponent implements OnInit {
  private fb = inject(FormBuilder);
  private auth = inject(AuthService);
  private snackBar = inject(MatSnackBar);

  readonly utilizador = this.auth.utilizador;
  readonly savingDados = signal(false);
  readonly savingPassword = signal(false);
  readonly showPassword = signal(false);
  readonly showPasswordNova = signal(false);

  dadosForm = this.fb.nonNullable.group({
    nome:  ['', [Validators.required, Validators.minLength(2)]],
    email: ['', [Validators.required, Validators.email]],
  });

  passwordForm = this.fb.nonNullable.group({
    password:              ['', [Validators.required, Validators.minLength(8)]],
    password_confirmation: ['', [Validators.required]],
  });

  ngOnInit(): void {
    const u = this.utilizador();
    if (u) {
      this.dadosForm.patchValue({ nome: u.nome, email: u.email });
    }
  }

  guardarDados(): void {
    if (this.dadosForm.invalid) {
      this.dadosForm.markAllAsTouched();
      return;
    }
    this.savingDados.set(true);
    const { nome, email } = this.dadosForm.getRawValue();
    this.auth.atualizarPerfil({ nome, email }).subscribe({
      next: () => {
        this.savingDados.set(false);
        this.snackBar.open('Dados atualizados com sucesso.', 'Fechar', {
          duration: 3000,
          panelClass: 'snack-success',
        });
      },
      error: (err) => {
        this.savingDados.set(false);
        const msg = err.error?.message ?? 'Erro ao atualizar dados.';
        this.snackBar.open(msg, 'Fechar', { duration: 4000, panelClass: 'snack-error' });
      },
    });
  }

  alterarPassword(): void {
    const { password, password_confirmation } = this.passwordForm.getRawValue();

    if (this.passwordForm.invalid) {
      this.passwordForm.markAllAsTouched();
      return;
    }
    if (password !== password_confirmation) {
      this.snackBar.open('As passwords não coincidem.', 'Fechar', { duration: 3000, panelClass: 'snack-error' });
      return;
    }

    this.savingPassword.set(true);
    this.auth.atualizarPerfil({ password }).subscribe({
      next: () => {
        this.savingPassword.set(false);
        this.passwordForm.reset();
        this.snackBar.open('Password alterada com sucesso.', 'Fechar', {
          duration: 3000,
          panelClass: 'snack-success',
        });
      },
      error: (err) => {
        this.savingPassword.set(false);
        const msg = err.error?.message ?? 'Erro ao alterar password.';
        this.snackBar.open(msg, 'Fechar', { duration: 4000, panelClass: 'snack-error' });
      },
    });
  }

  roleLabel(role: string): string {
    const map: Record<string, string> = {
      funcionario:        'Funcionário',
      diretor_tecnico:    'Diretor Técnico',
      diretora_executiva: 'Diretora Executiva',
    };
    return map[role] ?? role;
  }

  iniciais(): string {
    const nome = this.utilizador()?.nome ?? '';
    return nome.split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase();
  }
}
