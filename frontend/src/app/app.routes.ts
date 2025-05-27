import { Routes } from '@angular/router';
import { UserLoginComponent } from './Auth/login/login.component';
import { ForgotPasswordComponent } from './Auth/forgot-password/forgot-password.component';

import { PasswordSetupComponent } from './Auth/password-setup/password-setup.component';
import { AfaDashboardComponent } from './afa/afa-dashboard/afa-dashboard.component';

import { AfaComponent } from './afa/afa.component';


import { SuperAdminComponent } from './super-admin/super-admin.component';
import { DashboardAdminComponent } from './super-admin/dashboard/dashboard-admin/dashboard-admin.component';
import { MfaComponent } from './Auth/mfa/mfa.component';

import { InvoiceComponent } from './afa/afa-components/invoice/invoice/invoice.component';
import { InvoiceDetailsComponent } from './afa/afa-components/invoice/invoice-details/invoice-details.component';
import { AfaUserComponent } from './super-admin/user/afa-user.component';
import { PeopleComponent } from './afa/afa-components/People/People.component';
import { CompaniesComponent } from './afa/afa-components/Companies/Companies.component';
import { AssignmentComponent } from './afa/afa-components/Assignment/Assignment.component';
import { TimesheetDetailsComponent } from './afa/afa-components/Timesheet/Timesheet-Details/TimesheetDetails.component';
import { TimesheetsComponent } from './afa/afa-components/Timesheet/Timesheets/timesheets.component';

import { NotFoundComponent } from './not-found/not-found.component';
import { CustomerComponent } from './afa/afa-components/customer/customer.component';


export const routes: Routes = [
  { path: '', component: UserLoginComponent },
  { path: 'forgot-password', component: ForgotPasswordComponent },
  { path: 'password-setup/:id/:hash', component: PasswordSetupComponent },
  { path: 'forgot-pwd/:id/:hash', component: PasswordSetupComponent },
  { path: 'qrtest',component:MfaComponent},



  { path:'afa',component:AfaComponent,children:[
    {
      path:'dashboard',
      component: AfaDashboardComponent,
    },

    { path:'people',
      component: PeopleComponent,
    },
    {
      path: 'companies',
      component: CompaniesComponent,
    },
    {
      path: 'company',
      component: CustomerComponent
    },
    {
      path:'assignment',
      component: AssignmentComponent
    },
    { path: 'timesheetDetails/:id', component: TimesheetDetailsComponent },
    { path: 'timesheet', component: TimesheetsComponent },

    {path:'invoice/grid',component:InvoiceComponent},
    {path:'invoice/details/:invoiceNumber',component:InvoiceDetailsComponent},
  ]},

  {
    path: 'super-admin', component: SuperAdminComponent, children: [

      {
        path: 'dashboard',
        component: DashboardAdminComponent
      },


  {
    path:'user/:id', component:AfaUserComponent
  },
  {
    path:'**',component:NotFoundComponent
  }



  ]},


  {path:'**',component:NotFoundComponent}


];