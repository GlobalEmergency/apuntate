import { Component, Input, Output, EventEmitter } from '@angular/core';
import { Service } from '../../../../domain/entities/Service';

@Component({
  standalone: false,
  selector: 'app-service-table',
  templateUrl: './service-table.component.html',
  styleUrls: ['./service-table.component.scss'],
  
})
export class ServiceTableComponent {
  displayedColumns = ['date', 'name', 'status'];

  @Input() services!: Service[];
  @Output() serviceClicked = new EventEmitter<Service>();

  onRowClick(service: Service): void {
    this.serviceClicked.emit(service);
  }
}
