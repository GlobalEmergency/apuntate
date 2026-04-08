import {InjectionToken, NgModule} from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { HTTP_INTERCEPTORS, provideHttpClient, withInterceptorsFromDi } from '@angular/common/http';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';

// icons
import { TablerIconsModule } from 'angular-tabler-icons';
import * as TablerIcons from 'angular-tabler-icons/icons';

//Import all material modules
import { MaterialModule } from './material.module';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

//Import Layouts
import { FullComponent } from './layouts/full/full.component';
import { BlankComponent } from './layouts/blank/blank.component';

// Vertical Layout
import { SidebarComponent } from './layouts/full/sidebar/sidebar.component';
import { HeaderComponent } from './layouts/full/header/header.component';
import { BrandingComponent } from './layouts/full/sidebar/branding.component';
import { AppNavItemComponent } from './layouts/full/sidebar/nav-item/nav-item.component';
import { JwtInterceptor } from '../interceptor/jwt.interceptor';
import {ServicesInterface} from "../domain/ServicesInterface";
import {ApiService} from "../services/api.service";
import {ServiceComponent} from "./pages/service/service.component";
import {MatChipsModule} from "@angular/material/chips";
import {ServicestableComponent} from "./components/servicestable/servicestable.component";
import {MatTableModule} from "@angular/material/table";
import {MatIconModule} from "@angular/material/icon";
import {CommonModule} from "@angular/common";
// import { provideAnimationsAsync } from '@angular/platform-browser/animations/async';

@NgModule({ declarations: [
        AppComponent,
        FullComponent,
        BlankComponent,
        SidebarComponent,
        HeaderComponent,
        BrandingComponent,
        AppNavItemComponent,
        ServiceComponent,
    ],
    exports: [TablerIconsModule],
    bootstrap: [AppComponent], imports: [BrowserModule,
        AppRoutingModule,
        BrowserAnimationsModule,
        FormsModule,
        ReactiveFormsModule,
        MaterialModule,
        TablerIconsModule.pick(TablerIcons),
        MatChipsModule,
        MatTableModule,
        MatIconModule,
        CommonModule], providers: [
        { provide: HTTP_INTERCEPTORS, useClass: JwtInterceptor, multi: true },
        provideHttpClient(withInterceptorsFromDi()),
    ] })
export class AppModule {
  constructor() {
  }
}
