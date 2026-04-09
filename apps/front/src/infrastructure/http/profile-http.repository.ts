import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { ProfileRepository } from '../../domain/interfaces/ProfileRepository';

@Injectable()
export class ProfileHttpRepository extends ProfileRepository {
  private readonly url = environment.api_url;

  constructor(private http: HttpClient) {
    super();
  }

  getProfile(): Observable<any> {
    return this.http.get<any>(`${this.url}/profile`);
  }
}
