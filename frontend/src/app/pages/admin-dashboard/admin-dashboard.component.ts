import { Component, OnDestroy, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Subject, finalize, takeUntil } from 'rxjs';

import { QueueStatus, QueueTicket, Ticket } from '../../models/ticket.model';
import { AuthService } from '../../services/auth.service';
import { TicketService } from '../../services/ticket.service';
import { extractApiErrorMessage } from '../../shared/helpers/http-error.helper';

@Component({
  selector: 'app-admin-dashboard',
  standalone: false,
  templateUrl: './admin-dashboard.component.html'
})
export class AdminDashboardComponent implements OnInit, OnDestroy {
  private readonly destroy$ = new Subject<void>();

  readonly currentUser$ = this.authService.currentUser$;

  queueStatus: QueueStatus | null = null;
  calledTicket: Ticket | null = null;
  errorMessage = '';
  successMessage = '';
  isLoading = true;
  isCalling = false;

  get hasPendingTickets(): boolean {
    return this.queueStatus?.queue.some((ticket) => ticket.status === 'pending') ?? false;
  }

  constructor(
    private readonly authService: AuthService,
    private readonly ticketService: TicketService,
    private readonly router: Router
  ) {}

  ngOnInit(): void {
    if (!this.authService.isAdmin()) {
      void this.router.navigate(['/dashboard']);
      return;
    }

    this.loadQueue();
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  loadQueue(): void {
    this.isLoading = true;
    this.errorMessage = '';

    this.ticketService.getQueueStatus().pipe(
      finalize(() => {
        this.isLoading = false;
      }),
      takeUntil(this.destroy$)
    ).subscribe({
      next: (queueStatus) => {
        this.queueStatus = queueStatus;
      },
      error: (error: unknown) => {
        this.errorMessage = extractApiErrorMessage(
          error,
          'Nao foi possivel carregar a fila atual.'
        );
      }
    });
  }

  callNext(): void {
    const user = this.authService.currentUser;

    if (!user) {
      void this.router.navigate(['/login']);
      return;
    }

    this.errorMessage = '';
    this.successMessage = '';
    this.isCalling = true;

    this.ticketService.callNextTicket(user.id).pipe(
      finalize(() => {
        this.isCalling = false;
      }),
      takeUntil(this.destroy$)
    ).subscribe({
      next: (ticket) => {
        this.calledTicket = ticket;
        this.successMessage = `Senha ${ticket.ticket_number} chamada para atendimento.`;
        this.loadQueue();
      },
      error: (error: unknown) => {
        this.errorMessage = extractApiErrorMessage(
          error,
          'Nao foi possivel chamar a proxima senha.'
        );
      }
    });
  }

  logout(): void {
    this.authService.logout();
    void this.router.navigate(['/login']);
  }

  trackTicket(_index: number, ticket: QueueTicket): number {
    return ticket.id;
  }

  statusLabel(status: Ticket['status']): string {
    const labels: Record<Ticket['status'], string> = {
      pending: 'Pendente',
      in_attendance: 'Em atendimento',
      completed: 'Concluida',
      cancelled: 'Cancelada'
    };

    return labels[status];
  }

  typeLabel(type: Ticket['type']): string {
    return type === 'priority' ? 'Prioritaria' : 'Normal';
  }
}
