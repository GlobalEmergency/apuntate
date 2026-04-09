import { Service } from '../../domain/entities/Service';

export class Unit {
  id: string;
  identifier: string;
  name: string;
  services: Service[];
  speciality: string;
}
