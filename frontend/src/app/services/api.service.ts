import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';

import { environment } from '../../environments/environment';

type ApiQueryValue = string | number | boolean;
type ApiQueryParams = Record<string, ApiQueryValue | readonly ApiQueryValue[]>;

export interface ApiRequestOptions {
  headers?: Record<string, string>;
  params?: ApiQueryParams;
}

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private readonly baseUrl = environment.apiBaseUrl.replace(/\/+$/, '');

  constructor(private readonly http: HttpClient) {}

  get<TResponse>(endpoint: string, options?: ApiRequestOptions): Observable<TResponse> {
    return this.http.get<TResponse>(this.buildUrl(endpoint), this.buildOptions(options));
  }

  post<TResponse, TBody extends object>(
    endpoint: string,
    body: TBody,
    options?: ApiRequestOptions
  ): Observable<TResponse> {
    return this.http.post<TResponse>(this.buildUrl(endpoint), body, this.buildOptions(options));
  }

  private buildUrl(endpoint: string): string {
    return `${this.baseUrl}/${endpoint.replace(/^\/+/, '')}`;
  }

  private buildOptions(options?: ApiRequestOptions): { headers: HttpHeaders; params: HttpParams } {
    return {
      headers: new HttpHeaders(options?.headers ?? {}),
      params: this.buildParams(options?.params)
    };
  }

  private buildParams(params?: ApiQueryParams): HttpParams {
    let httpParams = new HttpParams();

    if (!params) {
      return httpParams;
    }

    Object.entries(params).forEach(([key, value]) => {
      if (Array.isArray(value)) {
        value.forEach((item) => {
          httpParams = httpParams.append(key, String(item));
        });
        return;
      }

      httpParams = httpParams.set(key, String(value));
    });

    return httpParams;
  }
}
