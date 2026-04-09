import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';
import { PagesRoutes } from './pages.routing.module';
import { MaterialModule } from '../material.module';
import { FormsModule } from '@angular/forms';

import { TablerIconsModule } from 'angular-tabler-icons';
import * as TablerIcons from 'angular-tabler-icons/icons';
import { FullCalendarModule } from '@fullcalendar/angular';

import { CalendarComponent } from './calendar/calendar.component';
import { DashboardComponent } from './dashboard/dashboard.component';
import { ServiceComponent } from './service/service.component';
import { ServiceTableComponent } from '../components/organisms/service-table/service-table.component';

import { FeedbackMessageComponent } from '../components/atoms/feedback-message/feedback-message.component';
import { SpinnerOverlayComponent } from '../components/atoms/spinner-overlay/spinner-overlay.component';
import { StatIconComponent } from '../components/atoms/stat-icon/stat-icon.component';
import { SummaryRowComponent } from '../components/atoms/summary-row/summary-row.component';
import { ServiceHeaderComponent } from '../components/organisms/service-header/service-header.component';
import { ServiceGapsComponent } from '../components/organisms/service-gaps/service-gaps.component';

@NgModule({
  declarations: [
    CalendarComponent,
    DashboardComponent,
    ServiceComponent,
    ServiceTableComponent,
  ],
  imports: [
    CommonModule,
    MaterialModule,
    FormsModule,
    RouterModule.forChild(PagesRoutes),
    TablerIconsModule.pick(TablerIcons),
    FullCalendarModule,
    FeedbackMessageComponent,
    SpinnerOverlayComponent,
    StatIconComponent,
    SummaryRowComponent,
    ServiceHeaderComponent,
    ServiceGapsComponent,
  ],
  exports: [TablerIconsModule],
})
export class PagesModule {}
