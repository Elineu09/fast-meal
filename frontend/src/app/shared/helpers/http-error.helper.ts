import { HttpErrorResponse } from '@angular/common/http';

export function extractApiErrorMessage(error: unknown, fallbackMessage: string): string {
  if (error instanceof HttpErrorResponse) {
    const apiMessage = readMessage(error.error);

    if (apiMessage) {
      return apiMessage;
    }

    if (error.status === 0) {
      return 'Não foi possível contactar a API. Confirme se o backend está ativo.';
    }
  }

  if (error instanceof Error && error.message) {
    return error.message;
  }

  return fallbackMessage;
}

function readMessage(body: unknown): string | null {
  if (typeof body === 'string' && body.trim().length > 0) {
    return body;
  }

  if (isRecord(body) && typeof body['message'] === 'string') {
    return body['message'];
  }

  return null;
}

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null;
}
