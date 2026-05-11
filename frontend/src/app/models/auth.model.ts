export type UserRole = 'user' | 'admin';

export interface ApiResponse<TData> {
  success: boolean;
  message: string;
  data: TData;
  errors?: unknown;
  timestamp?: string;
}

export interface User {
  id: number;
  nome: string;
  email: string;
  role: UserRole;
  created_at?: string;
}

export interface AuthSession extends User {
  token: string;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  nome: string;
  email: string;
  password: string;
}
