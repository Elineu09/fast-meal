export type TicketType = 'normal' | 'priority';
export type TicketStatus = 'pending' | 'in_attendance' | 'completed' | 'cancelled';

export interface Ticket {
  id: number;
  ticket_number: string;
  type: TicketType;
  status: TicketStatus;
  user_id: number;
  user_name?: string;
  user_role?: string;
  priority: boolean;
  created_at: string;
  updated_at?: string;
  called_at?: string;
  counter_number?: number | null;
  position?: number;
  tickets_ahead?: number;
  estimated_wait_seconds?: number;
  estimated_wait_formatted?: string;
}

export interface QueueTicket extends Ticket {
  user_name: string;
  position: number;
  tickets_ahead: number;
  estimated_wait_seconds: number;
  estimated_wait_formatted: string;
}

export interface QueueStatistics {
  total_pending: number;
  priority_tickets: number;
  normal_tickets: number;
  average_service_time: number;
  average_service_time_formatted: string;
  longest_wait_estimated: number;
  longest_wait_formatted: string;
}

export interface QueueStatus {
  queue: QueueTicket[];
  statistics: QueueStatistics;
  timestamp: string;
}

export interface QueuePosition {
  has_active_ticket: boolean;
  ticket_number?: string;
  status?: TicketStatus;
  estimated_wait_seconds?: number;
  estimated_wait_formatted?: string;
  tickets_ahead?: number;
  average_service_time?: number;
  average_service_time_formatted?: string;
  estimated_call_time?: string;
  total_in_queue?: number;
  message?: string;
}
