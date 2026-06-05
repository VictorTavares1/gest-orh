import { Injectable, signal, computed, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { Router } from '@angular/router';
import { LoginRequest, LoginResponse, MeResponse, Utilizador } from '../models/auth.model';
import { ApiResponse } from '../models/api.model';
import { environment } from '../../../environments/environment';

const TOKEN_KEY = 'grh_token';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private http = inject(HttpClient);
  private router = inject(Router);

  private _utilizador = signal<Utilizador | null>(null);
  private _token = signal<string | null>(localStorage.getItem(TOKEN_KEY));

  readonly utilizador = this._utilizador.asReadonly();
  readonly token = this._token.asReadonly();
  readonly isAuthenticated = computed(() => !!this._token());
  readonly isLoading = signal(false);

  readonly hasRole = (role: string) =>
    computed(() => this._utilizador()?.roles?.includes(role) ?? false);

  readonly hasPermission = (permission: string) =>
    computed(() => this._utilizador()?.permissions?.includes(permission) ?? false);

  login(credentials: LoginRequest): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(`${environment.apiUrl}/auth/login`, credentials).pipe(
      tap((res) => {
        this._token.set(res.data.token);
        this._utilizador.set(res.data.utilizador);
        localStorage.setItem(TOKEN_KEY, res.data.token);
      }),
    );
  }

  logout(): void {
    this.http.post(`${environment.apiUrl}/auth/logout`, {}).subscribe({
      complete: () => this.clearSession(),
      error: () => this.clearSession(),
    });
  }

  loadMe(): Observable<MeResponse> {
    return this.http.get<MeResponse>(`${environment.apiUrl}/auth/me`).pipe(
      tap((res) => this._utilizador.set(res.data)),
    );
  }

  atualizarPerfil(dados: { nome?: string; email?: string; password?: string }): Observable<ApiResponse<Utilizador>> {
    const id = this._utilizador()?.id_utilizador;
    return this.http.put<ApiResponse<Utilizador>>(`${environment.apiUrl}/utilizadores/${id}`, dados).pipe(
      tap((res) => this._utilizador.set(res.data)),
    );
  }

  getToken(): string | null {
    return this._token();
  }

  private clearSession(): void {
    this._token.set(null);
    this._utilizador.set(null);
    localStorage.removeItem(TOKEN_KEY);
    this.router.navigate(['/login']);
  }
}
