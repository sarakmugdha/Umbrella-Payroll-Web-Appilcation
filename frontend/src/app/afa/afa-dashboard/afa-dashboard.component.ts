import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment.development';
import { GanttComponent } from './gantt/gantt.component';
import { SpinnerComponent } from '../../shared/spinner/spinner.component';


@Component({
  selector: 'app-afa-dashboard',
  imports: [CommonModule, GanttComponent, SpinnerComponent],
  templateUrl: './afa-dashboard.component.html',
  styleUrl: './afa-dashboard.component.css'
})

export class AfaDashboardComponent {

  public company_name: string='' ;
  //card 
  public apiUrl = environment.apiUrl;
  public totalCompanies!: number;
  public totalAssignments!: number;
  public totalPeople!: number;
  public stats: { title: string, value: number, icon: string; }[] = [];
  //spinner
  public loading: boolean = false;

  //sidebar
 public isSidebarCollapsed = true;

  constructor(private http: HttpClient) { }

  public ngOnInit() {
    this.loading = true;
    this.getDashboardDetails();
  }
  
  public getDashboardDetails() {
    this.http.post(`${this.apiUrl}/getDashboardDetails`, {})
      .subscribe({
        next: (response: any) => {
          this.totalCompanies = response.totalCompanies;
          this.totalAssignments = response.totalAssignments;
          this.totalPeople = response.totalPeople;
          this.company_name = response.orgName[0];

          this.stats = [
            { title: 'Total Companies', value: this.totalCompanies, icon: 'fa-building' },
            { title: 'Total People', value: this.totalPeople, icon: 'fa-users' },
            { title: 'Total Assignments', value: this.totalAssignments, icon: 'fa-tasks' }
          ];
          this.loading = false;
        }  
      });
  }
}