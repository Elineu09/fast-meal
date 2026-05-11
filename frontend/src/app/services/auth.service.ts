import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, map, tap, throwError } from 'rxjs';

import {
  ApiResponse,
  AuthSession,
  LoginRequest,
  RegisterRequest,
  User
} from '../models/auth.model';
import { ApiService } from './api.service';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private readonly storageKey = 'fast_meal_auth_session';
  private readonly sessionSubject = new BehaviorSubject<AuthSession | null>(this.readStoredSession());

  readonly session$ = this.sessionSubject.asObservable();
  readonly currentUser$ = this.session$.pipe(
    map((session) => (session ? this.toUser(session) : null))
  );

  constructor(private readonly api: ApiService) {}

  get token(): string | null {
    return this.sessionSubject.value?.token ?? null;
  }

  get currentUser(): User | null {
    const session = this.sessionSubject.value;
    return session ? this.toUser(session) : null;
  }

  login(credentials: LoginRequest): Observable<AuthSession> {
    return this.api.post<ApiResponse<AuthSession>, LoginRequest>('auth/login', credentials).pipe(
      map((response) => this.unwrapResponse(response)),
      tap((session) => this.storeSession(session))
    );
  }

  register(payload: RegisterRequest): Observable<User> {
    return this.api.post<ApiResponse<User>, RegisterRequest>('auth/register', payload).pipe(
      map((response) => this.unwrapResponse(response))
    );
  }

  getCurrentUser(): Observable<User> {
    const token = this.token;

    if (!token) {
      return throwError(() => new Error('Sessão expirada. Inicie sessão novamente.'));
    }

    return this.api.get<ApiResponse<User>>('auth/user', {
      headers: {
        Authorization: `Bearer ${token}`
      }
    }).pipe(
      map((response) => this.unwrapResponse(response)),
      tap((user) => this.mergeCurrentUser(user))
    );
  }

  logout(): void {
    this.clearStoredSession();
    this.sessionSubject.next(null);
  }

  isAuthenticated(): boolean {
    return Boolean(this.token);
  }

  isAdmin(): boolean {
    return this.sessionSubject.value?.role === 'admin';
  }

  private unwrapResponse<TData>(response: ApiResponse<TData>): TData {
    if (!response.success || response.data === null || response.data === undefined) {
      throw new Error(response.message || 'Resposta inválida da API.');
    }

    return response.data;
  }

  private storeSession(session: AuthSession): void {
    if (this.hasStorage()) {
      localStorage.setItem(this.storageKey, JSON.stringify(session));
    }

    this.sessionSubject.next(session);
  }

  private mergeCurrentUser(user: User): void {
    const currentSession = this.sessionSubject.value;

    if (!currentSession) {
      return;
    }

    this.storeSession({
      ...currentSession,
      ...user
    });
  }

  private readStoredSession(): AuthSession | null {
    if (!this.hasStorage()) {
      return null;
    }

    const rawSession = localStorage.getItem(this.storageKey);

    if (!rawSession) {
      return null;
    }

    try {
      const parsedSession: unknown = JSON.parse(rawSession);

      if (this.isAuthSession(parsedSession)) {
        return parsedSession;
      }
    } catch {
      this.clearStoredSession();
    }

    return null;
  }

  private clearStoredSession(): void {
    if (this.hasStorage()) {
      localStorage.removeItem(this.storageKey);
    }
  }

  private hasStorage(): boolean {
    return typeof window !== 'undefined' && typeof window.localStorage !== 'undefined';
  }

  private isAuthSession(value: unknown): value is AuthSession {
    if (typeof value !== 'object' || value === null) {
      return false;
    }

    const session = value as Partial<AuthSession>;

    return (
      typeof session.id === 'number' &&
      typeof session.nome === 'string' &&
      typeof session.email === 'string' &&
      (session.role === 'admin' || session.role === 'employee' || session.role === 'student') &&
      typeof session.token === 'string' &&
      session.token.length > 0
    );
  }

  private toUser(session: AuthSession): User {
    const { token: _token, ...user } = session;
    return user;
  }
}
