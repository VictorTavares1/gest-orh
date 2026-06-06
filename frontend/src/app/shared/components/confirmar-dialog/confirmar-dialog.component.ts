import { Component, inject } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';

export interface ConfirmarDialogData {
  titulo: string;
  mensagem: string;
  textoBotao?: string;
  corBotao?: 'primary' | 'warn' | 'accent';
}

@Component({
  selector: 'app-confirmar-dialog',
  standalone: true,
  imports: [MatDialogModule, MatButtonModule, MatIconModule],
  template: `
    <div class="dialog-header">
      <mat-icon>warning_amber</mat-icon>
      <h2>{{ data.titulo }}</h2>
    </div>
    <mat-dialog-content>
      <p [innerHTML]="data.mensagem"></p>
    </mat-dialog-content>
    <mat-dialog-actions align="end">
      <button mat-stroked-button (click)="ref.close(false)">Cancelar</button>
      <button mat-flat-button [color]="data.corBotao ?? 'warn'" (click)="ref.close(true)">
        {{ data.textoBotao ?? 'Confirmar' }}
      </button>
    </mat-dialog-actions>
  `,
  styles: [`
    .dialog-header {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 20px 24px 16px;
      background: #fef2f2;
      border-bottom: 1px solid #fecaca;
      border-radius: 12px 12px 0 0;
      mat-icon { color: #dc2626; font-size: 24px; width: 24px; height: 24px; }
      h2 { margin: 0; font-size: 18px; font-weight: 700; color: #111827; }
    }
    mat-dialog-content { padding: 20px 24px !important; }
    p { margin: 0; color: #374151; font-size: 14px; line-height: 1.6; }
    mat-dialog-actions { padding: 12px 24px 20px !important; gap: 8px; }
    button { border-radius: 8px !important; }
  `],
})
export class ConfirmarDialogComponent {
  readonly data = inject<ConfirmarDialogData>(MAT_DIALOG_DATA);
  readonly ref = inject(MatDialogRef<ConfirmarDialogComponent>);
}
