import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { AdminRouting } from './admin.routing';

@NgModule({
  imports: [
    RouterModule.forChild(AdminRouting),
  ],
})
export class AdminModule {}
