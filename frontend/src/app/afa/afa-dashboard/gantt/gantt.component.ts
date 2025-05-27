import { HttpClient } from '@angular/common/http';
import { Component } from '@angular/core';
import { DependencyType, GanttDependency, GanttTask, KENDO_GANTT } from '@progress/kendo-angular-gantt';
import { environment } from '../../../../environments/environment.development';
import { CommonModule } from '@angular/common';
import { ValidationService } from '../../afa-components/Validation.service';

@Component({
  selector: 'app-gantt',
  imports: [KENDO_GANTT, CommonModule],
  templateUrl: './gantt.component.html',
  styleUrl: './gantt.component.css'
})
export class GanttComponent {
  
  public apiUrl = environment.apiUrl;
  //gantt chart
  public data: GanttTask[] = [];
  public dependencies: GanttDependency[] = [];
  //sidebar toggle
  public isSidebarToggled: boolean = false;

  constructor(private http: HttpClient, private validationService: ValidationService){
    this.validationService.isSidebarCollapsed.subscribe(data=>{
      this.isSidebarToggled = data;
    })
  }

  public ngOnInit(){
    this.getFromAPI();
  }

  public getFromAPI() {
    this.http.post(`${this.apiUrl}/getAllChartDetails`, {})
      .subscribe({
        next: (response: any) => {
          let today = new Date();
          this.data = response.allAssignments.map((a: any) => {
            let start = new Date(a.start_date);
            let end = new Date(a.end_date);
            let completionRatio = 0;

            if (end < today) {
              completionRatio = 1;
            }
            else if (start > today) {
              completionRatio = 0;
            }
            else {
              let totalDuration = (end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24);
              let elapsedDuration = (today.getTime() - start.getTime()) / (1000 * 60 * 60 * 24);
              completionRatio = elapsedDuration / totalDuration;
              completionRatio = Math.min(Math.max(completionRatio, 0), 1);
            }

            return {
              id: a.assignment_id,
              title: `${a.name} (${a.role}) - ${a.customer_name}`,
              start: start,
              end: end,
              people_id: a.people_id,
              company_name: a.company_name,
              customer_name: a.customer_name,
              name: a.name,
              completionRatio: completionRatio,
              children: []
            };
          });
       this.dependencies = this.createGroupedDependencies(this.data);
      }
    });
  }

  public createGroupedDependencies(tasks: GanttTask[]): GanttDependency[] {
    let groupedByPerson: { [key: number]: GanttTask[] } = {};
    let dependencies: GanttDependency[] = [];
  
    for (let task of tasks) {
      let personId = (task as any).people_id;
      if (!groupedByPerson[personId]) {
        groupedByPerson[personId] = [];
      }
      groupedByPerson[personId].push(task);
    }
    let depId = 0;
    for (let personId in groupedByPerson) {
      let personTasks = groupedByPerson[personId];
      personTasks.sort((firstAssignment, secondAssignment) => firstAssignment.start.getTime() - secondAssignment.start.getTime());
  
      for (let people = 0; people < personTasks.length - 1; people++) {
        dependencies.push({
          id: depId++,
          fromId: personTasks[people].id,
          toId: personTasks[people + 1].id,
          type: DependencyType.FS  
        });
      }
    }
    return dependencies;
  }

  
}