import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';

import { Ticket } from '../models/ticket.model';

interface QrCodeOptions {
  size?: number;
  margin?: number;
  caption?: string;
}

@Injectable({
  providedIn: 'root'
})
export class QrService {
  private readonly endpoint = 'https://quickchart.io/qr';

  createTicketQrCode(ticket: Ticket, options: QrCodeOptions = {}): Observable<string> {
    const qrText = JSON.stringify({
      ticketNumber: ticket.ticket_number,
      type: ticket.type,
      status: ticket.status,
      createdAt: ticket.created_at
    });

    const url = this.buildQrUrl(qrText, {
      size: options.size ?? 280,
      margin: options.margin ?? 2,
      caption: options.caption ?? ticket.ticket_number
    });

    return this.preloadImage(url);
  }

  private buildQrUrl(text: string, options: Required<QrCodeOptions>): string {
    const params = new URLSearchParams({
      text,
      size: String(options.size),
      margin: String(options.margin),
      format: 'png',
      ecLevel: 'M',
      dark: '17211d',
      light: 'ffffff',
      caption: options.caption,
      captionFontSize: '18'
    });

    return `${this.endpoint}?${params.toString()}`;
  }

  private preloadImage(url: string): Observable<string> {
    return new Observable<string>((subscriber) => {
      const image = new Image();

      image.onload = () => {
        subscriber.next(url);
        subscriber.complete();
      };

      image.onerror = () => {
        subscriber.error(new Error('Nao foi possivel gerar o QR Code neste momento.'));
      };

      image.src = url;

      return () => {
        image.onload = null;
        image.onerror = null;
      };
    });
  }
}
