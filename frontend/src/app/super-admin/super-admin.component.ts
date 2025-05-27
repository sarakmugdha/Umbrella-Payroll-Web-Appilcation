import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';


import { SidebarComponent } from '../afa/afa-components/sidebar/sidebar.component';

@Component({
  selector: 'app-super-admin',
  imports: [RouterOutlet,SidebarComponent],
  templateUrl: './super-admin.component.html'
})
export class SuperAdminComponent {

}
