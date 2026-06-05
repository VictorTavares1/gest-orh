export interface LoginRequest {
  email: string;
  password: string;
}

export interface Utilizador {
  id_utilizador: number;
  nome: string;
  email: string;
  ativo: boolean;
  id_setor: number | null;
  tipo_utilizador: { id_tipo_utilizador: number; nome: string } | null;
  setor: { id_setor: number; nome: string; organizacao: { id_organizacao: number; nome: string } | null } | null;
  roles: string[];
  permissions: string[];
}

export interface LoginResponse {
  success: boolean;
  message: string;
  data: {
    utilizador: Utilizador;
    token: string;
  };
}

export interface MeResponse {
  success: boolean;
  data: Utilizador;
}
