import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { HttpErrorResponse } from '@angular/common/http';

import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';

import { AuthService } from '../../../core/services/auth.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [
    ReactiveFormsModule,
    MatCardModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
  ],
  templateUrl: './login.component.html',
  styleUrl: './login.component.scss',
})
export class LoginComponent {
  private fb = inject(FormBuilder);
  private auth = inject(AuthService);
  private router = inject(Router);
  private snackBar = inject(MatSnackBar);

  readonly currentYear = new Date().getFullYear();
  readonly loading = signal(false);
  readonly hidePassword = signal(true);
  readonly serverError = signal<string | null>(null);

  form = this.fb.nonNullable.group({
    email: ['', [Validators.required, Validators.email]],
    password: ['', [Validators.required, Validators.minLength(6)]],
  });

  get emailError(): string {
    const ctrl = this.form.controls.email;
    if (ctrl.hasError('required')) return 'E-mail obrigatório.';
    if (ctrl.hasError('email')) return 'E-mail inválido.';
    return '';
  }

  get passwordError(): string {
    const ctrl = this.form.controls.password;
    if (ctrl.hasError('required')) return 'Palavra-passe obrigatória.';
    if (ctrl.hasError('minlength')) return 'Mínimo 6 caracteres.';
    return '';
  }

  submit(): void {
    if (this.form.invalid || this.loading()) return;

    this.serverError.set(null);
    this.loading.set(true);

    this.auth.login(this.form.getRawValue()).subscribe({
      next: () => this.router.navigate(['/dashboard']),
      error: (err: HttpErrorResponse) => {
        this.loading.set(false);
        const msg = err.error?.message ?? 'Erro ao iniciar sessão. Tente novamente.';
        this.serverError.set(msg);
      },
    });
  }
}
