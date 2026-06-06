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

export interface OrganizacaoAdmin {
  id_organizacao: number;
  nome: string;
  setores?: { id_setor: number; nome: string }[];
}

export interface PeriodoAdmin {
  id_periodo: number;
  data_inicio: string;
  data_fim: string;
}

@Injectable({ providedIn: 'root' })
export class AdminService {
  private http = inject(HttpClient);
  private base = environment.apiUrl;

  // ─── Utilizadores ─────────────────────────────────────────────────────────

  listarTiposUtilizador(): Observable<ApiResponse<TipoUtilizador[]>> {
    return this.http.get<ApiResponse<TipoUtilizador[]>>(`${this.base}/tipos-utilizador`);
  }

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

  criarSetor(dados: { nome: string; id_organizacao: number }): Observable<ApiResponse<SetorAdmin>> {
    return this.http.post<ApiResponse<SetorAdmin>>(`${this.base}/setores`, dados);
  }

  editarSetor(id: number, dados: { nome: string; id_organizacao: number }): Observable<ApiResponse<SetorAdmin>> {
    return this.http.patch<ApiResponse<SetorAdmin>>(`${this.base}/setores/${id}`, dados);
  }

  eliminarSetor(id: number): Observable<ApiResponse<null>> {
    return this.http.delete<ApiResponse<null>>(`${this.base}/setores/${id}`);
  }

  // ─── Organizações ─────────────────────────────────────────────────────────

  listarOrganizacoes(): Observable<ApiResponse<OrganizacaoAdmin[]>> {
    return this.http.get<ApiResponse<OrganizacaoAdmin[]>>(`${this.base}/organizacoes`);
  }

  criarOrganizacao(dados: { nome: string }): Observable<ApiResponse<OrganizacaoAdmin>> {
    return this.http.post<ApiResponse<OrganizacaoAdmin>>(`${this.base}/organizacoes`, dados);
  }

  editarOrganizacao(id: number, dados: { nome: string }): Observable<ApiResponse<OrganizacaoAdmin>> {
    return this.http.patch<ApiResponse<OrganizacaoAdmin>>(`${this.base}/organizacoes/${id}`, dados);
  }

  eliminarOrganizacao(id: number): Observable<ApiResponse<null>> {
    return this.http.delete<ApiResponse<null>>(`${this.base}/organizacoes/${id}`);
  }

  // ─── Períodos ─────────────────────────────────────────────────────────────

  listarPeriodos(): Observable<ApiResponse<PeriodoAdmin[]>> {
    return this.http.get<ApiResponse<PeriodoAdmin[]>>(`${this.base}/periodos`);
  }

  criarPeriodo(dados: { data_inicio: string; data_fim: string }): Observable<ApiResponse<PeriodoAdmin>> {
    return this.http.post<ApiResponse<PeriodoAdmin>>(`${this.base}/periodos`, dados);
  }

  editarPeriodo(id: number, dados: { data_inicio: string; data_fim: string }): Observable<ApiResponse<PeriodoAdmin>> {
    return this.http.patch<ApiResponse<PeriodoAdmin>>(`${this.base}/periodos/${id}`, dados);
  }

  eliminarPeriodo(id: number): Observable<ApiResponse<null>> {
    return this.http.delete<ApiResponse<null>>(`${this.base}/periodos/${id}`);
  }

  // ─── Labels ───────────────────────────────────────────────────────────────

  readonly tipoLabel: Record<string, string> = {
    FUNCIONARIO:        'Funcionário',
    DIRETOR_TECNICO:    'Diretor Técnico',
    DIRETORA_EXECUTIVA: 'Diretora Executiva',
  };
}
