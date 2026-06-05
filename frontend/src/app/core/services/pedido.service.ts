import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Pedido, PedidoFiltros, HistoricoItem } from '../models/pedido.model';
import { ApiResponse, PaginatedResponse } from '../models/api.model';

@Injectable({ providedIn: 'root' })
export class PedidoService {
  private http = inject(HttpClient);
  private base = `${environment.apiUrl}/pedidos`;

  listar(filtros: PedidoFiltros = {}): Observable<PaginatedResponse<Pedido>> {
    let params = new HttpParams();
    if (filtros.estado) params = params.set('estado', filtros.estado);
    if (filtros.id_tipo_pedido) params = params.set('id_tipo_pedido', filtros.id_tipo_pedido);
    if (filtros.per_page) params = params.set('per_page', filtros.per_page);
    if (filtros.page) params = params.set('page', filtros.page);
    return this.http.get<PaginatedResponse<Pedido>>(this.base, { params });
  }

  mostrar(id: number): Observable<ApiResponse<Pedido>> {
    return this.http.get<ApiResponse<Pedido>>(`${this.base}/${id}`);
  }

  cancelar(id: number): Observable<ApiResponse<Pedido>> {
    return this.http.delete<ApiResponse<Pedido>>(`${this.base}/${id}`);
  }

  submeter(id: number): Observable<ApiResponse<Pedido>> {
    return this.http.post<ApiResponse<Pedido>>(`${this.base}/${id}/submeter`, {});
  }

  historico(id: number): Observable<ApiResponse<HistoricoItem[]>> {
    return this.http.get<ApiResponse<HistoricoItem[]>>(`${this.base}/${id}/historico`);
  }

  criar(tipo: string, dados: Record<string, unknown>): Observable<ApiResponse<Pedido>> {
    return this.http.post<ApiResponse<Pedido>>(`${this.base}/${tipo}`, dados);
  }

}
