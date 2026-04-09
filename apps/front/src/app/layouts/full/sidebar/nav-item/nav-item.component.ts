import { Component, Input } from '@angular/core';
import { NavItem } from './nav-item';
import { Router } from '@angular/router';
import { AuthenticationService } from '../../../../../services/authentication.service';

@Component({
  standalone: false,
  selector: 'app-nav-item',
  templateUrl: './nav-item.component.html',
  styleUrls: [],
})
export class AppNavItemComponent {
  @Input() item: NavItem;
  @Input() depth = 0;
  isAdmin: boolean;

  constructor(
    public router: Router,
    private authService: AuthenticationService,
  ) {
    this.isAdmin = this.authService.checkAuthentication('ROLE_ADMIN');
  }

  onItemSelected(item: NavItem) {
    if (!item.children || !item.children.length) {
      this.router.navigate([item.route]);
    }

    document.querySelector('.page-wrapper')?.scroll({ top: 0, left: 0 });
  }
}
