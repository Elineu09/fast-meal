import { Component, OnDestroy, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Subject, finalize, takeUntil } from 'rxjs';

import { QueuePosition, Ticket } from '../../models/ticket.model';
import { AuthService } from '../../services/auth.service';
import { QrService } from '../../services/qr.service';
import { TicketService } from '../../services/ticket.service';
import { extractApiErrorMessage } from '../../shared/helpers/http-error.helper';

@Component({
  selector: 'app-dashboard',
  standalone: false,
  templateUrl: './dashboard.component.html'
})
export class DashboardComponent implements OnInit, OnDestroy {
  private readonly destroy$ = new Subject<void>();

  readonly currentUser$ = this.authService.currentUser$;

  activeTicket: Ticket | null = null;
  queuePosition: QueuePosition | null = null;
  qrCodeUrl = '';
  errorMessage = '';
  qrErrorMessage = '';
  successMessage = '';
  isLoading = true;
  isRequesting = false;
  isQrLoading = false;

  constructor(
    private readonly authService: AuthService,
    private readonly router: Router,
    private readonly ticketService: TicketService,
    private readonly qrService: QrService
  ) {}

  ngOnInit(): void {
    const user = this.authService.currentUser;

    if (!user) {
      void this.router.navigate(['/login']);
      return;
    }

    if (user.role === 'admin') {
      void this.router.navigate(['/admin-dashboard']);
      return;
    }

    this.loadStudentTicket(user.id);
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  requestTicket(): void {
    const user = this.authService.currentUser;

    if (!user) {
      void this.router.navigate(['/login']);
      return;
    }

    this.errorMessage = '';
    this.successMessage = '';
    this.qrErrorMessage = '';
    this.isRequesting = true;

    this.ticketService.requestTicket(user.id).pipe(
      finalize(() => {
        this.isRequesting = false;
      }),
      takeUntil(this.destroy$)
    ).subscribe({
      next: (ticket) => {
        this.activeTicket = ticket;
        this.successMessage = `Senha ${ticket.ticket_number} solicitada com sucesso.`;
        this.generateQrCode(ticket);
        this.loadQueuePosition(user.id);
      },
      error: (error: unknown) => {
        this.errorMessage = extractApiErrorMessage(
          error,
          'Nao foi possivel solicitar uma senha. Tente novamente.'
        );
      }
    });
  }

  logout(): void {
    this.authService.logout();
    void this.router.navigate(['/login']);
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

  private loadStudentTicket(userId: number): void {
    this.isLoading = true;
    this.errorMessage = '';

    this.ticketService.getActiveTicket(userId).pipe(
      finalize(() => {
        this.isLoading = false;
      }),
      takeUntil(this.destroy$)
    ).subscribe({
      next: (ticket) => {
        this.activeTicket = ticket;

        if (ticket) {
          this.generateQrCode(ticket);
          this.loadQueuePosition(userId);
        }
      },
      error: (error: unknown) => {
        this.errorMessage = extractApiErrorMessage(
          error,
          'Nao foi possivel carregar a sua senha ativa.'
        );
      }
    });
  }

  private loadQueuePosition(userId: number): void {
    this.ticketService.getMyPosition(userId).pipe(
      takeUntil(this.destroy$)
    ).subscribe({
      next: (position) => {
        this.queuePosition = position;
      },
      error: (error: unknown) => {
        this.errorMessage = extractApiErrorMessage(
          error,
          'Nao foi possivel carregar a posicao na fila.'
        );
      }
    });
  }

  private generateQrCode(ticket: Ticket): void {
    this.qrCodeUrl = '';
    this.qrErrorMessage = '';
    this.isQrLoading = true;

    this.qrService.createTicketQrCode(ticket).pipe(
      finalize(() => {
        this.isQrLoading = false;
      }),
      takeUntil(this.destroy$)
    ).subscribe({
      next: (url) => {
        this.qrCodeUrl = url;
      },
      error: (error: unknown) => {
        this.qrErrorMessage = extractApiErrorMessage(
          error,
          'Nao foi possivel gerar o QR Code.'
        );
      }
    });
  }
}
