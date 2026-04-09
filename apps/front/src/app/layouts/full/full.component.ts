import { BreakpointObserver } from '@angular/cdk/layout';
import { Component, OnInit, OnDestroy, ViewChild } from '@angular/core';
import { Subscription } from 'rxjs';
import { MatSidenav } from '@angular/material/sidenav';

const MOBILE_BREAKPOINT = 'screen and (max-width: 768px)';

@Component({
  selector: 'app-full',
  templateUrl: './full.component.html',
  standalone: false,
})
export class FullComponent implements OnInit, OnDestroy {
  @ViewChild('leftsidenav') sidenav!: MatSidenav;

  private layoutSubscription = Subscription.EMPTY;
  private isMobileScreen = false;

  get isOver(): boolean {
    return this.isMobileScreen;
  }

  constructor(private breakpointObserver: BreakpointObserver) {}

  ngOnInit(): void {
    this.layoutSubscription = this.breakpointObserver
      .observe([MOBILE_BREAKPOINT])
      .subscribe((state) => {
        this.isMobileScreen = state.breakpoints[MOBILE_BREAKPOINT];
      });
  }

  ngOnDestroy(): void {
    this.layoutSubscription.unsubscribe();
  }

  toggleSidenav(): void {
    this.sidenav.toggle();
  }
}
