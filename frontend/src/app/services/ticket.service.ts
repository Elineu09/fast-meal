import { Injectable } from '@angular/core';
import { Observable, map } from 'rxjs';

import { ApiResponse } from '../models/auth.model';
import { QueuePosition, QueueStatus, Ticket } from '../models/ticket.model';
import { ApiService } from './api.service';

@Injectable({
  providedIn: 'root'
})
export class TicketService {
  constructor(private readonly api: ApiService) {}

  requestTicket(userId: number): Observable<Ticket> {
    return this.api.post<ApiResponse<Ticket>, { user_id: number }>('tickets/request', {
      user_id: userId
    }).pipe(
      map((response) => this.unwrapResponse(response))
    );
  }

  getActiveTicket(userId: number): Observable<Ticket | null> {
    return this.api.get<ApiResponse<Ticket | null>>('tickets/active', {
      params: {
        user_id: userId
      }
    }).pipe(
      map((response) => this.unwrapNullableResponse(response))
    );
  }

  getMyPosition(userId: number): Observable<QueuePosition> {
    return this.api.get<ApiResponse<QueuePosition>>('tickets/my-position', {
      params: {
        user_id: userId
      }
    }).pipe(
      map((response) => this.unwrapResponse(response))
    );
  }

  getQueueStatus(): Observable<QueueStatus> {
    return this.api.get<ApiResponse<QueueStatus>>('tickets/queue').pipe(
      map((response) => this.unwrapResponse(response))
    );
  }

  callNextTicket(adminUserId: number, counterNumber = 1): Observable<Ticket> {
    return this.api.post<ApiResponse<Ticket>, { user_id: number; counter_number: number }>(
      'tickets/call-next',
      {
        user_id: adminUserId,
        counter_number: counterNumber
      }
    ).pipe(
      map((response) => this.unwrapResponse(response))
    );
  }

  private unwrapResponse<TData>(response: ApiResponse<TData>): TData {
    if (!response.success || response.data === null || response.data === undefined) {
      throw new Error(response.message || 'Resposta invalida da API.');
    }

    return response.data;
  }

  private unwrapNullableResponse<TData>(response: ApiResponse<TData | null>): TData | null {
    if (!response.success) {
      throw new Error(response.message || 'Resposta invalida da API.');
    }

    return response.data;
  }
}
