import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, of } from 'rxjs';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { catchError, tap } from 'rxjs/operators';
import { environment } from '../environments/environment';
import { jwtDecode } from 'jwt-decode';

const ACCESS_TOKEN_KEY = 'access_token';
const REFRESH_TOKEN_KEY = 'refresh_token';

@Injectable({ providedIn: 'root' })
export class AuthenticationService {
  isAuthenticated: BehaviorSubject<boolean> = new BehaviorSubject<boolean>(false);
  currentAccessToken: string | null = null;
  private _payload: any = null;
  private url = environment.api_url;

  get payload(): any {
    return this._payload;
  }

  constructor(
    private http: HttpClient,
    private router: Router,
  ) {
    this.loadToken();
  }

  checkAuthentication(role: string | null = null): boolean {
    if (this.currentAccessToken === null || this._payload === null) {
      return false;
    }
    if (role !== null) {
      return !!this._payload.roles?.includes(role);
    }
    return true;
  }

  loadToken(): void {
    const token = localStorage.getItem(ACCESS_TOKEN_KEY);
    if (token) {
      this.currentAccessToken = token;
      this._payload = jwtDecode(token);
      this.isAuthenticated.next(true);
    } else {
      this.isAuthenticated.next(false);
    }
  }

  login(credentials: { username: string; password: string }): Observable<any> {
    return this.http
      .post<{ token: string; refresh_token: string }>(`${this.url}/auth/login`, credentials)
      .pipe(tap((tokens) => this.storeAccessToken(tokens.token, tokens.refresh_token)));
  }

  storeAccessToken(token: string, refreshToken: string): void {
    localStorage.setItem(ACCESS_TOKEN_KEY, token);
    localStorage.setItem(REFRESH_TOKEN_KEY, refreshToken);
    this.currentAccessToken = token;
    this._payload = jwtDecode(token);
    this.isAuthenticated.next(true);
  }

  logout(): void {
    this.currentAccessToken = null;
    this._payload = null;
    localStorage.removeItem(ACCESS_TOKEN_KEY);
    localStorage.removeItem(REFRESH_TOKEN_KEY);
    this.isAuthenticated.next(false);
    this.router.navigateByUrl('/login', { replaceUrl: true });
  }

  getNewAccessToken(): Observable<any> | null {
    const refreshToken = localStorage.getItem(REFRESH_TOKEN_KEY);
    if (refreshToken && refreshToken !== 'undefined') {
      return this.http.post(`${this.url}/auth/refresh`, { refresh_token: refreshToken }).pipe(
        catchError(() => {
          this.logout();
          return of(null);
        }),
      );
    }
    this.logout();
    return null;
  }

  register(data: any): Observable<any> {
    return this.http.post(`${this.url}/auth/register`, data);
  }

  requestPasswordReset(email: string): Observable<any> {
    return this.http.post(`${this.url}/auth/password-reset/request`, { email });
  }

  confirmPasswordReset(token: string, password: string): Observable<any> {
    return this.http.post(`${this.url}/auth/password-reset/confirm`, { token, password });
  }
}
