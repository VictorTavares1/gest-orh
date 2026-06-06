import { Routes } from '@angular/router';
import { authGuard } from './core/guards/auth.guard';
import { guestGuard } from './core/guards/guest.guard';
import { roleGuard } from './core/guards/role.guard';

export const routes: Routes = [
  {
    path: 'login',
    canActivate: [guestGuard],
    loadComponent: () =>
      import('./features/auth/login/login.component').then((m) => m.LoginComponent),
  },
  {
    path: '',
    canActivate: [authGuard],
    loadComponent: () =>
      import('./shared/components/shell/shell.component').then((m) => m.ShellComponent),
    children: [
      {
        path: 'dashboard',
        loadComponent: () =>
          import('./features/dashboard/dashboard.component').then((m) => m.DashboardComponent),
      },
      {
        path: 'pedidos',
        children: [
          {
            path: '',
            loadComponent: () =>
              import('./features/pedidos/lista/lista-pedidos.component').then((m) => m.ListaPedidosComponent),
          },
          {
            path: 'novo',
            loadComponent: () =>
              import('./features/pedidos/novo/novo-pedido.component').then((m) => m.NovoPedidoComponent),
          },
          {
            path: ':id',
            loadComponent: () =>
              import('./features/pedidos/detalhe/detalhe-pedido.component').then((m) => m.DetalhePedidoComponent),
          },
        ],
      },
      {
        path: 'perfil',
        loadComponent: () =>
          import('./features/perfil/perfil.component').then((m) => m.PerfilComponent),
      },
      {
        path: 'aprovacoes',
        canActivate: [roleGuard('diretora_executiva')],
        loadComponent: () =>
          import('./features/aprovacoes/aprovacoes.component').then((m) => m.AprovacoesComponent),
      },
      {
        path: 'admin',
        canActivate: [roleGuard('diretora_executiva')],
        children: [
          {
            path: 'utilizadores',
            loadComponent: () =>
              import('./features/admin/utilizadores/admin-utilizadores.component').then((m) => m.AdminUtilizadoresComponent),
          },
          {
            path: 'relatorio',
            loadComponent: () =>
              import('./features/admin/relatorio/admin-relatorio.component').then((m) => m.AdminRelatorioComponent),
          },
          {
            path: 'organizacoes',
            loadComponent: () =>
              import('./features/admin/organizacoes/admin-organizacoes.component').then((m) => m.AdminOrganizacoesComponent),
          },
          {
            path: 'setores',
            loadComponent: () =>
              import('./features/admin/setores/admin-setores.component').then((m) => m.AdminSetoresComponent),
          },
          {
            path: 'periodos',
            loadComponent: () =>
              import('./features/admin/periodos/admin-periodos.component').then((m) => m.AdminPeriodosComponent),
          },
          { path: '', redirectTo: 'utilizadores', pathMatch: 'full' },
        ],
      },

      { path: '', redirectTo: 'dashboard', pathMatch: 'full' },
    ],
  },
  { path: '**', redirectTo: '/login' },
];
