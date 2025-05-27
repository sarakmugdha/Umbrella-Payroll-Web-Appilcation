import { CommonModule } from '@angular/common';
import { Component, Input } from '@angular/core';
import { NavigationEnd, Router, RouterModule } from '@angular/router';
import { filter } from 'rxjs';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '../../../../environments/environment.development';
import { ValidationService } from '../Validation.service';


@Component({
  selector: 'app-sidebar',
  imports: [RouterModule,CommonModule],
  templateUrl: './sidebar.component.html',
})
export class SidebarComponent {
  public baseUrl= environment.baseUrl;
  sideBarLogo = '';

  currentRoute: string = '';
  home :string = '';
  isSidebarCollapsed = false;
  constructor(private router: Router ,private http:HttpClient, private validationService: ValidationService) {
    this.router.events.pipe(filter((event)=>event instanceof NavigationEnd)).subscribe((event:NavigationEnd)=>
      {
      this.currentRoute=event.urlAfterRedirects;
      if(this.currentRoute.includes('/afa')){
        this.home = '/afa/dashboard'
        this.http.get(`${this.baseUrl}/getOrgLogo`)
        .subscribe({
          next:(data:any)=>{
          this.sideBarLogo = data[0].organization_logo;

        },error:(error:any)=>{console.log("error fetching logo",error)}});

      }
      else{
        this.home = '/super-admin/dashboard'
      }

    });

}
toggleSidebar() {
  this.isSidebarCollapsed = !this.isSidebarCollapsed;
  this.validationService.changeSidebardata(this.isSidebarCollapsed);
}

logOut(){


  this.http.get(`${this.baseUrl}/logout`).subscribe((data:any)=>{});
  sessionStorage.clear();
  this.router.navigate(['/']);
}
}

