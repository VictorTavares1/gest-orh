import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { ApiResponse } from '../models/api.model';

export interface TipoPedido {
  id_tipo_pedido: number;
  nome: string;
  slug: string;
}

export interface Periodo {
  id_periodo: number;
  nome: string;
  data_inicio: string;
  data_fim: string;
}

export interface Utilizador {
  id_utilizador: number;
  nome: string;
  email: string;
}

export interface Setor {
  id_setor: number;
  nome: string;
}

@Injectable({ providedIn: 'root' })
export class ReferenciaService {
  private http = inject(HttpClient);
  private base = environment.apiUrl;

  tiposPedido(): Observable<ApiResponse<TipoPedido[]>> {
    return this.http.get<ApiResponse<TipoPedido[]>>(`${this.base}/tipos-pedido`);
  }

  periodos(): Observable<ApiResponse<Periodo[]>> {
    return this.http.get<ApiResponse<Periodo[]>>(`${this.base}/periodos`);
  }

  utilizadores(): Observable<ApiResponse<Utilizador[]>> {
    return this.http.get<ApiResponse<Utilizador[]>>(`${this.base}/utilizadores`);
  }

  setores(): Observable<ApiResponse<Setor[]>> {
    return this.http.get<ApiResponse<Setor[]>>(`${this.base}/setores`);
  }
}
