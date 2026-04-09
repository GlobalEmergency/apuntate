import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { HTTP_INTERCEPTORS, provideHttpClient, withInterceptorsFromDi } from '@angular/common/http';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';

import { TablerIconsModule } from 'angular-tabler-icons';
import * as TablerIcons from 'angular-tabler-icons/icons';

import { MaterialModule } from './material.module';
import { MatChipsModule } from '@angular/material/chips';
import { MatTableModule } from '@angular/material/table';
import { MatIconModule } from '@angular/material/icon';

import { FullComponent } from './layouts/full/full.component';
import { BlankComponent } from './layouts/blank/blank.component';
import { SidebarComponent } from './layouts/full/sidebar/sidebar.component';
import { HeaderComponent } from './layouts/full/header/header.component';
import { BrandingComponent } from './layouts/full/sidebar/branding.component';
import { AppNavItemComponent } from './layouts/full/sidebar/nav-item/nav-item.component';

import { JwtInterceptor } from '../interceptor/jwt.interceptor';

import { ServiceRepository } from '../domain/interfaces/ServiceRepository';
import { ServiceHttpRepository } from '../infrastructure/http/service-http.repository';
import { AlertRepository } from '../domain/interfaces/AlertRepository';
import { AlertHttpRepository } from '../infrastructure/http/alert-http.repository';
import { CalendarRepository } from '../domain/interfaces/CalendarRepository';
import { CalendarHttpRepository } from '../infrastructure/http/calendar-http.repository';
import { AdminRepository } from '../domain/interfaces/AdminRepository';
import { AdminHttpRepository } from '../infrastructure/http/admin-http.repository';

@NgModule({
  declarations: [
    AppComponent,
    FullComponent,
    BlankComponent,
    SidebarComponent,
    HeaderComponent,
    BrandingComponent,
    AppNavItemComponent,
  ],
  exports: [TablerIconsModule],
  bootstrap: [AppComponent],
  imports: [
    BrowserModule,
    AppRoutingModule,
    BrowserAnimationsModule,
    FormsModule,
    ReactiveFormsModule,
    MaterialModule,
    TablerIconsModule.pick(TablerIcons),
    MatChipsModule,
    MatTableModule,
    MatIconModule,
    CommonModule,
  ],
  providers: [
    { provide: HTTP_INTERCEPTORS, useClass: JwtInterceptor, multi: true },
    provideHttpClient(withInterceptorsFromDi()),
    { provide: ServiceRepository, useClass: ServiceHttpRepository },
    { provide: AlertRepository, useClass: AlertHttpRepository },
    { provide: CalendarRepository, useClass: CalendarHttpRepository },
    { provide: AdminRepository, useClass: AdminHttpRepository },
  ],
})
export class AppModule {}
