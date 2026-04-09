import { Component } from '@angular/core';
import { navItems } from './sidebar-data';

@Component({
  selector: 'app-sidebar',
  templateUrl: './sidebar.component.html',
  standalone: false,
})
export class SidebarComponent {
  navItems = navItems;
}
