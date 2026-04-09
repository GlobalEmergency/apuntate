import { BreakpointObserver } from '@angular/cdk/layout';
import { Component, OnInit, OnDestroy, ViewChild } from '@angular/core';
import { Subscription } from 'rxjs';
import { filter } from 'rxjs/operators';
import { MatSidenav } from '@angular/material/sidenav';
import { NavigationEnd, Router } from '@angular/router';

const MOBILE_BREAKPOINT = 'screen and (max-width: 768px)';

@Component({
  standalone: false,
  selector: 'app-full',
  templateUrl: './full.component.html',
})
export class FullComponent implements OnInit, OnDestroy {
  @ViewChild('leftsidenav') sidenav!: MatSidenav;

  private layoutSubscription = Subscription.EMPTY;
  private routerSubscription = Subscription.EMPTY;
  private isMobileScreen = false;

  get isOver(): boolean {
    return this.isMobileScreen;
  }

  constructor(
    private breakpointObserver: BreakpointObserver,
    private router: Router,
  ) {}

  ngOnInit(): void {
    this.layoutSubscription = this.breakpointObserver.observe([MOBILE_BREAKPOINT]).subscribe((state) => {
      this.isMobileScreen = state.breakpoints[MOBILE_BREAKPOINT];
    });

    this.routerSubscription = this.router.events
      .pipe(filter((event) => event instanceof NavigationEnd))
      .subscribe(() => {
        if (this.isMobileScreen && this.sidenav?.opened) {
          this.sidenav.close();
        }
      });
  }

  ngOnDestroy(): void {
    this.layoutSubscription.unsubscribe();
    this.routerSubscription.unsubscribe();
  }

  toggleSidenav(): void {
    this.sidenav.toggle();
  }
}
