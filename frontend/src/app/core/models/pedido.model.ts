export interface PedidoEstado {
  id: number;
  nome: string;
  label: string;
}

export interface PedidoTipo {
  id: number;
  nome: string;
}

export interface PedidoUtilizador {
  id: number;
  nome: string;
}

export interface HistoricoItem {
  id_historico: number;
  data_alteracao: string;
  utilizador_acao: PedidoUtilizador | null;
  estado_anterior: PedidoEstado | null;
  estado_novo: PedidoEstado;
}

export interface Pedido {
  id_pedido: number;
  data_criacao: string;
  estado: PedidoEstado;
  tipo: PedidoTipo;
  utilizador: PedidoUtilizador;
  especializacao: Record<string, unknown> | null;
  historico?: HistoricoItem[];
}

export interface PedidoFiltros {
  estado?: string;
  id_tipo_pedido?: number;
  per_page?: number;
  page?: number;
}
