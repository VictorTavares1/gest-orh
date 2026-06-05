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

export interface Pedido {
  id_pedido: number;
  data_criacao: string;
  estado: PedidoEstado;
  tipo: PedidoTipo;
  utilizador: PedidoUtilizador;
  especializacao: Record<string, unknown> | null;
}

export interface PedidoFiltros {
  estado?: string;
  id_tipo_pedido?: number;
  per_page?: number;
  page?: number;
}
