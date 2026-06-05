export interface PedidoRecente {
  id_pedido: number;
  tipo: string;
  estado: string;
  data_criacao: string;
  utilizador?: string;
}

export interface PorEstado {
  RASCUNHO: number;
  PENDENTE: number;
  EM_APROVACAO_COLEGA: number;
  EM_APROVACAO_EXECUTIVA: number;
  APROVADO: number;
  REJEITADO: number;
  CANCELADO: number;
  [key: string]: number;
}

export interface DashboardFuncionario {
  tipo: 'funcionario';
  meus_pedidos: { total: number; por_estado: PorEstado };
  pendentes_minha_aprovacao: number;
  recentes: PedidoRecente[];
}

export interface DashboardDiretor {
  tipo: 'diretor_tecnico';
  meus_pedidos: { total: number; por_estado: PorEstado };
  pendentes_minha_aprovacao: number;
  recentes: PedidoRecente[];
  pedidos_setor_recentes: PedidoRecente[];
}

export interface DashboardExecutiva {
  tipo: 'diretora_executiva';
  global: { total: number; por_estado: PorEstado; por_tipo: Record<string, number> };
  aguardam_aprovacao: number;
  recentes: PedidoRecente[];
}

export type DashboardData = DashboardFuncionario | DashboardDiretor | DashboardExecutiva;
