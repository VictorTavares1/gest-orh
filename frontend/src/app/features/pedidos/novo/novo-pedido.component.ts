import { Component } from '@angular/core';
import { MatCardModule } from '@angular/material/card';

@Component({
  selector: 'app-novo-pedido',
  standalone: true,
  imports: [MatCardModule],
  template: `<div style="padding:24px"><h1>Novo Pedido</h1><p>Em construção...</p></div>`,
})
export class NovoPedidoComponent {}
