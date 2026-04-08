import { TestBed } from '@angular/core/testing';
import { HttpClientTestingModule, HttpTestingController } from '@angular/common/http/testing';
import { RouterTestingModule } from '@angular/router/testing';
import { AuthenticationService } from './authentication.service';
import { environment } from '../environments/environment';

describe('AuthenticationService', () => {
  let service: AuthenticationService;
  let httpMock: HttpTestingController;

  beforeEach(() => {
    localStorage.clear();
    TestBed.configureTestingModule({
      imports: [HttpClientTestingModule, RouterTestingModule],
      providers: [AuthenticationService],
    });
    service = TestBed.inject(AuthenticationService);
    httpMock = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    httpMock.verify();
    localStorage.clear();
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });

  it('should store tokens on login', () => {
    const mockToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6InRlc3RAdGVzdC5jb20iLCJyb2xlcyI6WyJST0xFX0FETUlOIl19.hYJpS9hYMwXPnB4nUYBqwwj6K8';
    const mockResponse = { token: mockToken, refresh_token: 'refresh123' };

    service.login({ username: 'test@test.com', password: 'secret' }).subscribe();

    const req = httpMock.expectOne(`${environment.api_url}/auth/login`);
    expect(req.request.method).toBe('POST');
    expect(req.request.body).toEqual({ username: 'test@test.com', password: 'secret' });
    req.flush(mockResponse);

    expect(localStorage.getItem('access_token')).toBe(mockToken);
    expect(localStorage.getItem('refresh_token')).toBe('refresh123');
    expect(service.currentAccessToken).toBe(mockToken);
  });

  it('should clear tokens on logout', () => {
    localStorage.setItem('access_token', 'fake-token');
    localStorage.setItem('refresh_token', 'fake-refresh');
    service.currentAccessToken = 'fake-token';

    service.logout();

    expect(localStorage.getItem('access_token')).toBeNull();
    expect(localStorage.getItem('refresh_token')).toBeNull();
    expect(service.currentAccessToken).toBeNull();
    expect(service.payload).toBeNull();
  });

  it('should return false for unauthenticated user', () => {
    service.currentAccessToken = null;
    service.payload = null;
    expect(service.checkAuthentication()).toBeFalse();
  });

  it('should return true for authenticated user', () => {
    service.currentAccessToken = 'token';
    service.payload = { roles: ['ROLE_ADMIN'] };
    expect(service.checkAuthentication()).toBeTrue();
  });

  it('should check role when provided', () => {
    service.currentAccessToken = 'token';
    service.payload = { roles: ['ROLE_ADMIN'] };
    expect(service.checkAuthentication('ROLE_ADMIN')).toBeTrue();
    expect(service.checkAuthentication('ROLE_SUPER')).toBeFalse();
  });

  it('should call register endpoint with correct data', () => {
    const data = { org: 'Test Org', uname: 'Test', email: 'test@test.com', password: 'secret' };

    service.register(data).subscribe();

    const req = httpMock.expectOne(`${environment.api_url}/auth/register`);
    expect(req.request.method).toBe('POST');
    expect(req.request.body).toEqual(data);
    req.flush({ id: '123', email: 'test@test.com' });
  });
});
