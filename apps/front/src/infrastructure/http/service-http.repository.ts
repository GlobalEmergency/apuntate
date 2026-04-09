import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { ServiceRepository } from '../../domain/interfaces/ServiceRepository';
import { Service } from '../../domain/entities/Service';
import { Gap } from '../../domain/entities/Gap';

@Injectable()
export class ServiceHttpRepository extends ServiceRepository {
  private readonly url = environment.api_url;

  constructor(private http: HttpClient) {
    super();
  }

  getNextEvents(): Observable<Service[]> {
    return this.http.get<Service[]>(`${this.url}/services/nexts`);
  }

  getService(id: string): Observable<Service> {
    return this.http.get<Service>(`${this.url}/services/${id}`);
  }

  addService(service: Service): Observable<any> {
    return this.http.post<any>(`${this.url}/services`, service);
  }

  updateService(serviceId: string, data: Partial<Service>): Observable<any> {
    return this.http.put<any>(`${this.url}/services/${serviceId}`, data);
  }

  publishService(serviceId: string): Observable<any> {
    return this.http.post<any>(`${this.url}/services/${serviceId}/publish`, {});
  }

  cancelService(serviceId: string): Observable<void> {
    return this.http.delete<void>(`${this.url}/services/${serviceId}`);
  }

  getServiceGaps(serviceId: string): Observable<Gap[]> {
    return this.http.get<Gap[]>(`${this.url}/services/${serviceId}/gaps`);
  }

  signupForService(serviceId: string, gapId?: string): Observable<any> {
    const body = gapId ? { gap_id: gapId } : {};
    return this.http.post(`${this.url}/services/${serviceId}/signup`, body);
  }

  withdrawFromService(serviceId: string, gapId: string): Observable<any> {
    return this.http.post(`${this.url}/services/${serviceId}/withdraw`, { gap_id: gapId });
  }

  addUnitToService(serviceId: string, unitId: string): Observable<any> {
    return this.http.post(`${this.url}/services/${serviceId}/units/${unitId}`, {});
  }

  removeUnitFromService(serviceId: string, unitId: string): Observable<void> {
    return this.http.delete<void>(`${this.url}/services/${serviceId}/units/${unitId}`);
  }

  addGapToService(serviceId: string, unitComponentId: string): Observable<any> {
    return this.http.post(`${this.url}/services/${serviceId}/gaps`, { unit_component_id: unitComponentId });
  }

  removeGapFromService(serviceId: string, gapId: string): Observable<void> {
    return this.http.delete<void>(`${this.url}/services/${serviceId}/gaps/${gapId}`);
  }

  getUnits(): Observable<any[]> {
    return this.http.get<any[]>(`${this.url}/units`);
  }

  getComponents(): Observable<any[]> {
    return this.http.get<any[]>(`${this.url}/components`);
  }
}
