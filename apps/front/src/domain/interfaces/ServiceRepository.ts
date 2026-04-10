import { Observable } from 'rxjs';
import { Service } from '../entities/Service';
import { Gap } from '../entities/Gap';

export abstract class ServiceRepository {
  abstract getNextEvents(): Observable<Service[]>;
  abstract getService(id: string): Observable<Service>;
  abstract addService(service: Service, organizationId: string): Observable<any>;
  abstract updateService(serviceId: string, data: Partial<Service>): Observable<any>;
  abstract publishService(serviceId: string): Observable<any>;
  abstract cancelService(serviceId: string): Observable<void>;
  abstract getServiceGaps(serviceId: string): Observable<Gap[]>;
  abstract signupForService(serviceId: string, gapId?: string): Observable<any>;
  abstract withdrawFromService(serviceId: string, gapId: string): Observable<any>;
  abstract addUnitToService(serviceId: string, unitId: string): Observable<any>;
  abstract removeUnitFromService(serviceId: string, unitId: string): Observable<void>;
  abstract addGapToService(serviceId: string, unitComponentId: string): Observable<any>;
  abstract removeGapFromService(serviceId: string, gapId: string): Observable<void>;
  abstract getUnits(): Observable<any[]>;
  abstract getComponents(): Observable<any[]>;
}
