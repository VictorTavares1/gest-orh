import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { ApiResponse } from '../models/api.model';

export interface UtilizadorAdmin {
  id_utilizador: number;
  nome: string;
  email: string;
  ativo: boolean;
  tipo_utilizador: { id: number; nome: string } | null;
  setor: { id: number; nome: string; organizacao: { id_organizacao: number; nome: string } | null } | null;
  roles: string[];
}

export interface CriarUtilizadorDto {
  nome: string;
  email: string;
  password: string;
  id_setor: number;
  id_tipo_utilizador: number;
}

export interface EditarUtilizadorDto {
  nome?: string;
  email?: string;
  password?: string;
  id_setor?: number;
  id_tipo_utilizador?: number;
}

export interface TipoUtilizador {
  id_tipo_utilizador: number;
  nome: string;
}

export interface SetorAdmin {
  id_setor: number;
  nome: string;
  organizacao: { id_organizacao: number; nome: string } | null;
}

@Injectable({ providedIn: 'root' })
export class AdminService {
  private http = inject(HttpClient);
  private base = environment.apiUrl;

  // ─── Utilizadores ─────────────────────────────────────────────────────────

  listarUtilizadores(): Observable<ApiResponse<UtilizadorAdmin[]>> {
    return this.http.get<ApiResponse<UtilizadorAdmin[]>>(`${this.base}/utilizadores`);
  }

  criarUtilizador(dados: CriarUtilizadorDto): Observable<ApiResponse<UtilizadorAdmin>> {
    return this.http.post<ApiResponse<UtilizadorAdmin>>(`${this.base}/utilizadores`, dados);
  }

  editarUtilizador(id: number, dados: EditarUtilizadorDto): Observable<ApiResponse<UtilizadorAdmin>> {
    return this.http.put<ApiResponse<UtilizadorAdmin>>(`${this.base}/utilizadores/${id}`, dados);
  }

  ativarUtilizador(id: number): Observable<ApiResponse<UtilizadorAdmin>> {
    return this.http.patch<ApiResponse<UtilizadorAdmin>>(`${this.base}/utilizadores/${id}/ativar`, {});
  }

  desativarUtilizador(id: number): Observable<ApiResponse<UtilizadorAdmin>> {
    return this.http.patch<ApiResponse<UtilizadorAdmin>>(`${this.base}/utilizadores/${id}/desativar`, {});
  }

  // ─── Setores ──────────────────────────────────────────────────────────────

  listarSetores(): Observable<ApiResponse<SetorAdmin[]>> {
    return this.http.get<ApiResponse<SetorAdmin[]>>(`${this.base}/setores`);
  }

  // ─── Tipos de Utilizador ──────────────────────────────────────────────────

  readonly tipoLabel: Record<string, string> = {
    FUNCIONARIO:       'Funcionário',
    DIRETOR_TECNICO:   'Diretor Técnico',
    DIRETORA_EXECUTIVA:'Diretora Executiva',
  };
}
