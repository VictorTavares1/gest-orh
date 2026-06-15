import { Component, inject, signal, OnInit, HostListener } from '@angular/core';
import { RouterOutlet, RouterLink, RouterLinkActive } from '@angular/router';

import { MatToolbarModule } from '@angular/material/toolbar';
import { MatSidenavModule } from '@angular/material/sidenav';
import { MatListModule } from '@angular/material/list';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';
import { MatMenuModule } from '@angular/material/menu';
import { MatDividerModule } from '@angular/material/divider';
import { MatBadgeModule } from '@angular/material/badge';

import { AuthService } from '../../../core/services/auth.service';
import { PedidoService } from '../../../core/services/pedido.service';

@Component({
  selector: 'app-shell',
  standalone: true,
  imports: [
    RouterOutlet, RouterLink, RouterLinkActive,
    MatToolbarModule, MatSidenavModule, MatListModule,
    MatIconModule, MatButtonModule, MatMenuModule, MatDividerModule, MatBadgeModule,
  ],
  templateUrl: './shell.component.html',
  styleUrl: './shell.component.scss',
})
export class ShellComponent implements OnInit {
  readonly auth = inject(AuthService);
  private pedidoService = inject(PedidoService);

  readonly isMobile = signal(window.innerWidth < 768);
  readonly sidenavOpen = signal(window.innerWidth >= 768);
  readonly isDiretora = this.auth.hasRole('diretora_executiva');
  readonly isSubstituta = this.auth.hasRole('substituta');
  readonly pendentesCount = signal(0);

  @HostListener('window:resize')
  onResize(): void {
    const mobile = window.innerWidth < 768;
    this.isMobile.set(mobile);
    if (!mobile) this.sidenavOpen.set(true);
    else this.sidenavOpen.set(false);
  }

  ngOnInit(): void {
    this.carregarPendentes();
  }

  private carregarPendentes(): void {
    if (!this.isDiretora() && !this.isSubstituta()) return;

    this.pedidoService.listar({ estado: 'EM_APROVACAO_EXECUTIVA', per_page: 1 }).subscribe({
      next: (res) => this.pendentesCount.set(res.meta.total),
    });
  }

  logout(): void {
    this.auth.logout();
  }
}
