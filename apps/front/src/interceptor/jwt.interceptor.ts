import { Injectable } from '@angular/core';
import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpInterceptor,
  HttpErrorResponse,
  HttpHeaders,
} from '@angular/common/http';
import { Observable, throwError, BehaviorSubject, of } from 'rxjs';
import { AuthenticationService } from '../services/authentication.service';
import { catchError, finalize, switchMap, filter, take } from 'rxjs/operators';
import { environment } from '../environments/environment';

@Injectable()
export class JwtInterceptor implements HttpInterceptor {
  // Used for queued API calls while refreshing tokens
  tokenSubject: BehaviorSubject<string | null> = new BehaviorSubject<string | null>(null);
  isRefreshingToken = false;

  constructor(private authenticationService: AuthenticationService) {}

  // Intercept every HTTP call
  intercept(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    if (!this.requiresAuthToken(request.url)) {
      return next.handle(request);
    }

    return next.handle(this.addToken(request)).pipe(
      catchError((err) => {
        if (err instanceof HttpErrorResponse && err.status === 401) {
          return this.handle401Error(request, next);
        }
        return throwError(() => err);
      }),
    );
  }

  private requiresAuthToken(url: string): boolean {
    return url.startsWith(environment.api_url) && !url.startsWith(environment.api_url + '/auth');
  }

  private addToken(req: HttpRequest<any>) {
    if (this.authenticationService.currentAccessToken) {
      return req.clone({
        headers: new HttpHeaders({
          Authorization: `Bearer ${this.authenticationService.currentAccessToken}`,
        }),
      });
    } else {
      return req;
    }
  }

  // Indicates our access token is invalid, try to load a new one
  private handle401Error(request: HttpRequest<any>, next: HttpHandler): Observable<any> {
    if (this.isRefreshingToken) {
      return this.tokenSubject.pipe(
        filter((token) => token !== null),
        take(1),
        switchMap((_token) => {
          return next.handle(this.addToken(request));
        }),
      );
    }

    // Set to null so other requests will wait
    // until we got a new token!
    this.tokenSubject.next(null);
    this.isRefreshingToken = true;
    this.authenticationService.currentAccessToken = null;

    // First, get a new access token
    return (
      this.authenticationService.getNewAccessToken()?.pipe(
        switchMap((tokens: any) => {
          if (tokens) {
            // Store the new token
            this.authenticationService.storeAccessToken(tokens.token, tokens.refresh_token);
            // Use the subject so other calls can continue with the new token
            this.tokenSubject.next(tokens.token);

            // Perform the initial request again with the new token
            return next.handle(this.addToken(request));
          } else {
            // No new token or other problem occurred
            return of(null);
          }
        }),
        finalize(() => {
          // Unblock the token reload logic when everything is done
          this.isRefreshingToken = false;
        }),
      ) ?? of(null)
    );
  }
}
