import { Component, inject } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';

export interface AcaoDialogData {
  acao: 'aprovar' | 'rejeitar' | 'devolver';
  tipoPedido: string;
  funcionario: string;
  pedirComentario?: boolean;
}

@Component({
  selector: 'app-acao-dialog',
  standalone: true,
  imports: [FormsModule, MatDialogModule, MatButtonModule, MatIconModule, MatFormFieldModule, MatInputModule],
  template: `
    <div class="dialog-header" [class]="'header-' + data.acao">
      <mat-icon>{{ icone }}</mat-icon>
      <h2>{{ titulo }}</h2>
    </div>

    <mat-dialog-content>
      <p class="descricao">{{ descricao }}</p>
      <div class="info-box">
        <div class="info-row">
          <span class="info-label">Pedido</span>
          <span class="info-valor">{{ data.tipoPedido }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Funcionário</span>
          <span class="info-valor">{{ data.funcionario }}</span>
        </div>
      </div>

      @if (data.pedirComentario) {
        <mat-form-field appearance="outline" class="comentario-field">
          <mat-label>Motivo (opcional)</mat-label>
          <textarea matInput [(ngModel)]="comentario" rows="3"
                    placeholder="Explique o motivo da devolução..."></textarea>
        </mat-form-field>
      }
    </mat-dialog-content>

    <mat-dialog-actions align="end">
      <button mat-stroked-button (click)="cancelar()">Cancelar</button>
      <button mat-flat-button [color]="corBotao" (click)="confirmar()">
        <mat-icon>{{ icone }}</mat-icon>
        {{ titulo }}
      </button>
    </mat-dialog-actions>
  `,
  styles: [`
    .dialog-header {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 20px 24px 16px;
      border-radius: 12px 12px 0 0;

      h2 { margin: 0; font-size: 18px; font-weight: 700; }
      mat-icon { font-size: 24px; width: 24px; height: 24px; }

      &.header-aprovar  { background: #f0fdf4; color: #166534; }
      &.header-rejeitar { background: #fef2f2; color: #991b1b; }
      &.header-devolver { background: #fffbeb; color: #92400e; }
    }

    mat-dialog-content {
      padding: 16px 24px !important;
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .descricao { margin: 0; color: #374151; font-size: 14px; line-height: 1.5; }

    .info-box {
      background: #f9fafb;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 12px 16px;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .info-row {
      display: flex;
      gap: 12px;
      font-size: 14px;

      .info-label { color: #6b7280; min-width: 80px; font-weight: 500; }
      .info-valor { color: #111827; font-weight: 600; }
    }

    .comentario-field { width: 100%; }

    mat-dialog-actions {
      padding: 12px 24px 20px !important;
      gap: 8px;
    }
  `],
})
export class AcaoDialogComponent {
  readonly data = inject<AcaoDialogData>(MAT_DIALOG_DATA);
  private ref = inject(MatDialogRef<AcaoDialogComponent>);

  comentario = '';

  get titulo(): string {
    return { aprovar: 'Aprovar', rejeitar: 'Rejeitar', devolver: 'Devolver' }[this.data.acao];
  }

  get icone(): string {
    return { aprovar: 'check_circle', rejeitar: 'cancel', devolver: 'undo' }[this.data.acao];
  }

  get descricao(): string {
    return {
      aprovar:  'Tem a certeza que pretende aprovar este pedido? Esta ação é irreversível.',
      rejeitar: 'Tem a certeza que pretende rejeitar este pedido? Esta ação é irreversível.',
      devolver: 'O pedido será devolvido ao funcionário no estado Rascunho para que possa efectuar correções.',
    }[this.data.acao];
  }

  get corBotao(): string {
    return { aprovar: 'primary', rejeitar: 'warn', devolver: 'accent' }[this.data.acao];
  }

  cancelar(): void {
    this.ref.close(this.data.pedirComentario ? undefined : false);
  }

  confirmar(): void {
    if (this.data.pedirComentario) {
      this.ref.close({ confirmado: true, comentario: this.comentario || undefined });
    } else {
      this.ref.close(true);
    }
  }
}
