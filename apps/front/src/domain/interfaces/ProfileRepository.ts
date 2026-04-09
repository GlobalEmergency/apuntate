import { Observable } from 'rxjs';

export abstract class ProfileRepository {
  abstract getProfile(): Observable<any>;
}
