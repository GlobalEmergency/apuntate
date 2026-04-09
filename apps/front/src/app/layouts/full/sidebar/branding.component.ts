import { Component } from '@angular/core';

@Component({
  standalone: false,
  selector: 'app-branding',
  template: `
    <div class="branding">
      <a routerLink="/">
        <img
          src="./assets/images/logos/apuntate.png"
          style="max-width: 100%; height: auto;"
          class="align-middle m-2"
          alt="logo"
        />
      </a>
    </div>
  `,
})
export class BrandingComponent {}
