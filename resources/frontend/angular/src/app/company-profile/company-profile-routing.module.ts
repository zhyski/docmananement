import { NgModule } from '@angular/core';
import { CompanyProfileComponent } from './company-profile.component';
import { RouterModule, Routes } from '@angular/router';
import { AuthGuard } from '@core/security/auth.guard';
import { CompanyProfileResolver } from './company-profile.resolver';
import { S3Resolver } from './s3-profile.resolver';

const routes: Routes = [
  {
    path: '',
    component: CompanyProfileComponent,
    data: {
      claimType: ['SETTING_MANAGE_PROFILE', 'SETTINGS_STORAGE_SETTINGS'],
    },
    canActivate: [AuthGuard],
    resolve: {
      profile: CompanyProfileResolver,
      s3Profile: S3Resolver,
    },
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class CompanyProfileRoutingModule {}
