import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

export const roleGuard = (...roles: string[]): CanActivateFn =>
  () => {
    const auth = inject(AuthService);
    const router = inject(Router);

    const utilizador = auth.utilizador();
    const temRole = roles.some((r) => utilizador?.roles?.includes(r));

    if (!temRole) {
      router.navigate(['/dashboard']);
      return false;
    }

    return true;
  };
