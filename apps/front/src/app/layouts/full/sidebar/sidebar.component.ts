import { Component } from '@angular/core';
import { navItems } from './sidebar-data';

@Component({
  standalone: false,
  selector: 'app-sidebar',
  templateUrl: './sidebar.component.html',
})
export class SidebarComponent {
  navItems = navItems;
}
