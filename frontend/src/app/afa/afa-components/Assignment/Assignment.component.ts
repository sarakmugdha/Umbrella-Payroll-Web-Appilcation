import { HttpClient } from '@angular/common/http';
import { Component } from '@angular/core';
import { KENDO_DATEINPUTS } from '@progress/kendo-angular-dateinputs';
import { GridDataResult, KENDO_GRID } from '@progress/kendo-angular-grid';
import { environment } from '../../../../environments/environment.development';
import { CommonModule, DatePipe } from '@angular/common';
import { KENDO_DIALOGS } from '@progress/kendo-angular-dialog';
import { KENDO_LABELS } from '@progress/kendo-angular-label';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { KENDO_ICONS, KENDO_SVGICON } from '@progress/kendo-angular-icons';
import { KENDO_BUTTONS } from '@progress/kendo-angular-buttons';
import { SwitchModule } from '@progress/kendo-angular-inputs';
import { pencilIcon, trashIcon, eyeIcon, plusIcon, xIcon} from '@progress/kendo-svg-icons';
import { SVGIcon, searchIcon, filterIcon, filterClearIcon } from "@progress/kendo-svg-icons";
import { AssignmentsType, PeopleType, CompanyType } from '../Model';
import { NotificationServiceWrapper } from '../../../shared/notification.service';
import { SpinnerComponent } from '../../../shared/spinner/spinner.component';
import { FilterExpression } from '@progress/kendo-angular-filter';
import { RouterLink } from '@angular/router';
import { ValidationService } from '../Validation.service';


@Component({
  selector: 'app-assignment',
  imports: [KENDO_GRID,
            CommonModule,
            KENDO_DIALOGS,
            KENDO_DATEINPUTS,
            KENDO_LABELS,
            CommonModule,
            ReactiveFormsModule,
            KENDO_ICONS,
            KENDO_BUTTONS,
            SwitchModule,
            KENDO_SVGICON,
            DatePipe,
            SpinnerComponent,
            RouterLink,

            ],
  templateUrl: './Assignment.component.html' ,
})
export class AssignmentComponent {
  constructor(
              private http:HttpClient,
              private notificationService: NotificationServiceWrapper,
              public validationService: ValidationService
               ) {}
  //grid
  public company_name!: string;
  public apiUrl: string = environment.apiUrl;
  public gridData: GridDataResult = {total: 0, data: []};
  public loading: boolean = true;
  //dialog box
  private company_id!: number;
  public assignmentForm!: FormGroup;
  public opened: boolean = false;
  public selectedAssignment: AssignmentsType | null = null;
  public start_date: Date | null = null;
  public end_date: Date | null = null;
  public companies = [] as CompanyType[];
  public peopleList = [] as PeopleType[];
  public today!: Date;
  public start_date_initial: Date = new Date();
  public assignments:any = [];
  public status_value: boolean = false;
  public isAssigned: boolean = false;
  //icon
  public addIcon: SVGIcon=plusIcon;
  public pencilIcon: SVGIcon=pencilIcon;
  public trashIcon: SVGIcon=trashIcon;
  public eyeIcon: SVGIcon=eyeIcon;
  public searchIcon: SVGIcon = searchIcon;
  public filterIcon: SVGIcon = filterIcon;
  public filterClearIcon: SVGIcon = filterClearIcon;
  public xIcon: SVGIcon = xIcon;
  //backend pagination
  public pageSize: number = 10;
  public skip: number = 0;
  //backend filteration
  public filters: FilterExpression[] = [
    {
      field: "assignment_id",
      title: "Assignment ID",
      editor: "number",
    },
    {
      field: "name",
      title: "People Name",
      editor: "string",
    },
    {
      field: "company_name",
      title: "Company Name",
      editor: "string"
    },
    {
      field: "start_date",
      title: "Start Date",
      editor: "date"
    },
    {
      field: "end_date",
      title: "End Date",
      editor: "date",
    },
  ]
  public onFilter: boolean = false;
  public filterForm!: FormGroup;
  // delete confirmation
  public isDeleteDialogOpen: boolean = false;
  public deleteDataItem: any;

  public ngOnInit(){
    this.loading = true;
    let companyId = sessionStorage.getItem('companyId');
    this.company_id = parseInt(companyId!);
    this.getCustomerDetails();
    this.getPeopleDetails();
    this.getTimesheetDetails();
    this.filterForm = new FormGroup ({
          "assignment_id": new FormControl(null),
          "company_name": new FormControl(null),
          "name": new FormControl(null),
          "start_date": new FormControl(null),
          "end_date" : new FormControl(null),
          "status" : new FormControl(null),
    });
    this.isAssigned = false;
    this.getFromAPI();
  }

  public getFromAPI(){
    this.loading = true;

    let details = {
      company_id: this.company_id,
      pageSize: this.pageSize,
      page: this.skip / this.pageSize,
      filter: this.filterForm.value,
    };
    this.http.post<AssignmentsType>(`${this.apiUrl}/getAssignmentsDetails`,details)
      .subscribe({
        next: (response:any )=>{
          this.gridData.data=response.assignments;
          this.company_name = response.company_name;
      }})
      setTimeout(() => {
        this.loading = false;
      }, 5500);
  }

  public getCustomerDetails(){
    let company_id = {company_id: this.company_id};
    this.http.post<CompanyType[]>(`${this.apiUrl}/getCustomerDetails`,company_id)
    .subscribe({
      next:(data)=>{
        this.companies = data;
      }});
  }

  public getPeopleDetails(){
    let company_id = {company_id: this.company_id};
    this.http.post<PeopleType[]>(`${this.apiUrl}/getPeopleDetails`,company_id)
    .subscribe({
      next:(data)=>{
        this.peopleList = data;
      }});
  }

  public getTimesheetDetails(){
    this.http.post(`${this.apiUrl}/isTimesheetCreated`,{})
    .subscribe({
      next:(data)=>{
        this.assignments = data;
      }
    })
  }

  public open(assignment?: AssignmentsType){
    this.loading = true;
    this.opened = true;
    this.isAssigned = false;
    this.today = new Date();
    this.selectedAssignment = assignment || null;
    this.status_value = assignment?.status === '1' || false;

    this.assignmentForm = new FormGroup({
      assignment_id : new FormControl(assignment?.assignment_id),
      company_id: new FormControl(this.company_id),
      start_date: new FormControl(assignment?.start_date ? new Date(assignment.start_date) : null,
      [Validators.required]),
      end_date: new FormControl(assignment?.end_date ? new Date(assignment.end_date) : null,
      [Validators.required]),
      role: new FormControl(assignment?.role, [Validators.required,Validators.pattern(/^[a-zA-Z ]*$/)]),
      location: new FormControl(assignment?.location, [Validators.required,Validators.pattern(/^[a-zA-Z ]*$/)]),
      status: new FormControl(assignment?.status || '0'),
      type: new FormControl(assignment?.type, [Validators.required]),
      name: new FormControl(assignment?.name),
      company_name: new FormControl(assignment?.company_name),
      customer_id: new FormControl(assignment?.customer_id, [Validators.required]),
      people_id: new FormControl(assignment?.people_id, [Validators.required]),
    });
    let startDate = this.assignmentForm.get("start_date")?.value;

    if (this.selectedAssignment != null && startDate && new Date(startDate) <= this.today) {
      this.assignmentForm.get("end_date")?.disable();
    }
  }

  public close(){
    this.opened = false;
    this.assignmentForm.reset();
    this.getFromAPI();
    this.loading = false;
  }

  public onstart_dateChange(event: any){
    this.start_date = event;
    this.isAssigned = false;
  }

  public onStatusChange(checked: boolean){
    this.assignmentForm.get('status')?.setValue(checked ? 1 : 0);
  }

  public onSave(){
    if (this.assignmentForm.invalid) return;
    let formValue = this.assignmentForm.value;
    this.loading = true;

    if(this.selectedAssignment){
      //edit
      this.http.post(`${this.apiUrl}/updateAssignment`,formValue)
      .subscribe({
        next: (response: any)=>{
          if (response.status === 422) {
            this.notificationService.showError("Exception Occured");
          }
          if (response.status === 409) {
            this.isAssigned = true;
            return;
          }
          this.close();
          this.getFromAPI();
          this.loading = false;
          this.notificationService.showSuccess("Assignment Updated Successfully");
      },
        error: (err) => {
            this.notificationService.showError("Execption Occured!");
        }
    })}
    else{
      //add
      this.http.post(`${this.apiUrl}/insertAssignment`,formValue)
      .subscribe({
        next: (response: any)=>{
          if (response.status === 422) {
            this.notificationService.showError("Exception Occured");
          }
          if (response.status === 409) {
            this.isAssigned = true;
            return;
          }
          this.close();
          this.getFromAPI();
          this.loading = false;
          this.notificationService.showSuccess("Assignment Added Successfully");
        },
        error: (err)=>{
            this.notificationService.showError( "Execption Occured!");
        }});
    }
  }

  public removeHandler(dataItem: any){
    this.loading = true;
    let id = {assignment_id: dataItem.dataItem.assignment_id};
    this.http.post<AssignmentsType>(`${this.apiUrl}/deleteAssignments`,id)
    .subscribe({
      next: (response: any)=>{
        if(response.status === 422){
          this.notificationService.showError("Exception Occured");
          return;
        }
        this.getFromAPI();
        this.notificationService.showSuccess("Assignment Deleted Successfully");
      },
      error: (err) => {
        let errorMsg = err?.error?.error || 'An unexpected error occurred.';
            if (err.status === 409) {
              this.notificationService.showError(errorMsg);
            }
            else {
              this.notificationService.showError(errorMsg);
            }}
    })
  }

  public isInvalid(event: any): boolean {
    let control = this.assignmentForm.get(event);
    return !!(control && control.invalid && (control.dirty || control.touched));
  }

  // backend pagination
  public pageChange(event: any){
    this.loading = true;
    this.skip = event.skip;
    this.pageSize = event.take;
    this.getFromAPI();
  }

  // backend filteration
  public onFilterValue(){
    this.onFilter = !this.onFilter;
    this.searchIcon = this.searchIcon == searchIcon ? xIcon : searchIcon;
  }

  public onSubmit(){
    this.loading = true;
    let start_date = this.filterForm.get('start_date')?.value;
    let end_date = this.filterForm.get('end_date')?.value;

    start_date ? this.filterForm.get('start_date')?.setValue(new Date(start_date)): this.filterForm.get('start_date')?.setValue(null);
    end_date ? this.filterForm.get('end_date')?.setValue(new Date(end_date)): this.filterForm.get('end_date')?.setValue(null);

    this.getFromAPI();
  }

  public onClear(){
    this.loading = true;
    this.filterForm.reset();
    this.getFromAPI();
  }

  //delete confirmation
  public onConfirmDelete(dataItem?: any){
    if(dataItem){
      this.deleteDataItem = dataItem;
      this.isDeleteDialogOpen = true;
      return;
    }
    this.removeHandler(this.deleteDataItem);
    this.isDeleteDialogOpen = false;
  }

  public onCancelDelete(){
    this.isDeleteDialogOpen = false;
    this.deleteDataItem = null;
  }


}