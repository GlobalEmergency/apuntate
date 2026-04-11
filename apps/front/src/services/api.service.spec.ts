import { TestBed } from '@angular/core/testing';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { RouterTestingModule } from '@angular/router/testing';
import { environment } from '../environments/environment';
import { provideHttpClient, withInterceptorsFromDi } from '@angular/common/http';
import { ServiceHttpRepository } from '../infrastructure/http/service-http.repository';
import { AlertHttpRepository } from '../infrastructure/http/alert-http.repository';
import { AdminHttpRepository } from '../infrastructure/http/admin-http.repository';
import { AdminRepository } from '../domain/interfaces/AdminRepository';

const apiUrl = environment.api_url;

describe('ServiceHttpRepository', () => {
  let service: ServiceHttpRepository;
  let httpMock: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [RouterTestingModule],
      providers: [ServiceHttpRepository, provideHttpClient(withInterceptorsFromDi()), provideHttpClientTesting()],
    });
    service = TestBed.inject(ServiceHttpRepository);
    httpMock = TestBed.inject(HttpTestingController);
  });

  afterEach(() => httpMock.verify());

  it('should be created', () => {
    expect(service).toBeTruthy();
  });

  it('should fetch next events', () => {
    service.getNextEvents().subscribe((data) => {
      expect(data.length).toBe(2);
    });
    const req = httpMock.expectOne(`${apiUrl}/services/nexts`);
    expect(req.request.method).toBe('GET');
    req.flush([
      { id: '1', name: 'S1' },
      { id: '2', name: 'S2' },
    ]);
  });

  it('should fetch single service', () => {
    service.getService('abc-123').subscribe((data) => {
      expect(data.name).toBe('Test Service');
    });
    const req = httpMock.expectOne(`${apiUrl}/services/abc-123`);
    expect(req.request.method).toBe('GET');
    req.flush({ id: 'abc-123', name: 'Test Service' });
  });

  it('should fetch service gaps', () => {
    service.getServiceGaps('abc-123').subscribe((data) => {
      expect(data.length).toBe(3);
    });
    const req = httpMock.expectOne(`${apiUrl}/services/abc-123/gaps`);
    expect(req.request.method).toBe('GET');
    req.flush([{ id: '1' }, { id: '2' }, { id: '3' }]);
  });

  it('should signup for service with specific gap', () => {
    service.signupForService('svc-1', 'gap-1').subscribe();
    const req = httpMock.expectOne(`${apiUrl}/services/svc-1/signup`);
    expect(req.request.method).toBe('POST');
    expect(req.request.body).toEqual({ gap_id: 'gap-1' });
    req.flush({ id: 'gap-1', user: 'John' });
  });

  it('should signup for service without specific gap', () => {
    service.signupForService('svc-1').subscribe();
    const req = httpMock.expectOne(`${apiUrl}/services/svc-1/signup`);
    expect(req.request.method).toBe('POST');
    expect(req.request.body).toEqual({});
    req.flush({ id: 'gap-auto', user: 'John' });
  });

  it('should withdraw from service', () => {
    service.withdrawFromService('svc-1', 'gap-1').subscribe();
    const req = httpMock.expectOne(`${apiUrl}/services/svc-1/withdraw`);
    expect(req.request.method).toBe('POST');
    expect(req.request.body).toEqual({ gap_id: 'gap-1' });
    req.flush(null);
  });

  it('should create service', () => {
    const svc = { name: 'New Service' } as any;
    service.addService(svc, 'org-1').subscribe();
    const req = httpMock.expectOne(`${apiUrl}/services`);
    expect(req.request.method).toBe('POST');
    expect(req.request.body.organizationId).toBe('org-1');
    req.flush({ id: 'new-id', name: 'New Service' });
  });

  it('should update service', () => {
    service.updateService('svc-1', { name: 'Updated' } as any).subscribe();
    const req = httpMock.expectOne(`${apiUrl}/services/svc-1`);
    expect(req.request.method).toBe('PUT');
    req.flush({ id: 'svc-1', name: 'Updated' });
  });

  it('should cancel service', () => {
    service.cancelService('svc-1').subscribe();
    const req = httpMock.expectOne(`${apiUrl}/services/svc-1`);
    expect(req.request.method).toBe('DELETE');
    req.flush(null);
  });

  it('should publish service', () => {
    service.publishService('svc-1').subscribe();
    const req = httpMock.expectOne(`${apiUrl}/services/svc-1/publish`);
    expect(req.request.method).toBe('POST');
    req.flush({ id: 'svc-1', status: 'confirmed' });
  });
});

describe('AlertHttpRepository', () => {
  let service: AlertHttpRepository;
  let httpMock: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [RouterTestingModule],
      providers: [AlertHttpRepository, provideHttpClient(withInterceptorsFromDi()), provideHttpClientTesting()],
    });
    service = TestBed.inject(AlertHttpRepository);
    httpMock = TestBed.inject(HttpTestingController);
  });

  afterEach(() => httpMock.verify());

  it('should fetch alerts', () => {
    service.getAlerts().subscribe((data) => {
      expect(data.length).toBe(1);
    });
    const req = httpMock.expectOne(`${apiUrl}/alerts`);
    expect(req.request.method).toBe('GET');
    req.flush([{ id: '1', title: 'Alert 1', show: true }]);
  });

  it('should discard alert', () => {
    const alert = { id: 'a-1', title: 'Test', resume: '', message: '', type: 'info', show: true };
    service.discardAlert(alert as any).subscribe();
    const req = httpMock.expectOne(`${apiUrl}/alerts/a-1`);
    expect(req.request.method).toBe('POST');
    req.flush(true);
  });
});

describe('AdminHttpRepository', () => {
  let service: AdminHttpRepository;
  let httpMock: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [RouterTestingModule],
      providers: [
        { provide: AdminRepository, useClass: AdminHttpRepository },
        AdminHttpRepository,
        provideHttpClient(withInterceptorsFromDi()),
        provideHttpClientTesting(),
      ],
    });
    service = TestBed.inject(AdminHttpRepository);
    httpMock = TestBed.inject(HttpTestingController);
  });

  afterEach(() => httpMock.verify());

  it('should fetch profile', () => {
    const mockProfile = { name: 'John', email: 'john@test.com', stats: { totalServices: 5, totalHours: 12 } };
    service.getProfile().subscribe((data: any) => {
      expect(data).toEqual(mockProfile);
    });
    const req = httpMock.expectOne(`${apiUrl}/profile`);
    expect(req.request.method).toBe('GET');
    req.flush(mockProfile);
  });
});
