import { Component } from '@angular/core';
import { AddEvent, CancelEvent, EditEvent, KENDO_GRID, RemoveEvent, SaveEvent, GridDataResult } from '@progress/kendo-angular-grid';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { KENDO_DROPDOWNLIST, KENDO_DROPDOWNS } from '@progress/kendo-angular-dropdowns';
import { FormsModule } from "@angular/forms";
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Router, RouterLink } from '@angular/router';
import { KENDO_BUTTONS } from '@progress/kendo-angular-buttons';
import { CommonModule, formatDate, DatePipe } from '@angular/common';
import { environment } from '../../../../../environments/environment.development';
import {  KENDO_SVGICON } from '@progress/kendo-angular-icons';
import {  plusIcon, exportIcon, pencilIcon, saveIcon, checkIcon, xIcon, cancelIcon, trashIcon, eyeIcon, searchIcon, filterClearIcon, filterIcon,filePdfIcon} from '@progress/kendo-svg-icons';
import { SpinnerComponent } from '../../../../shared/spinner/spinner.component';
import { KENDO_TEXTBOX } from '@progress/kendo-angular-inputs';
import { FilterExpression } from '@progress/kendo-angular-filter';
import { KENDO_DATEPICKER } from '@progress/kendo-angular-dateinputs';
import { NotificationServiceWrapper } from '../../../../shared/notification.service';
import { saveAs } from 'file-saver';
@Component({
  selector: 'app-timesheets',
  standalone: true,
  imports: [KENDO_GRID, KENDO_DROPDOWNS, KENDO_DROPDOWNLIST, FormsModule, KENDO_BUTTONS,KENDO_SVGICON,CommonModule,DatePipe,SpinnerComponent,KENDO_TEXTBOX,ReactiveFormsModule,RouterLink,KENDO_DATEPICKER],
  templateUrl: './timesheets.component.html'
})
export class TimesheetsComponent {
  public companyId:any=sessionStorage.getItem('companyId');
  public companyName!:string;
  public isLoading!:boolean;
  public formGroup?:FormGroup;
  public baseUrl= environment.baseUrl;
  public headers = new HttpHeaders({
    'Content-Type': 'application/json'
  });
  // GRID VARIABLES
  public isGridInEditMode:boolean=false;
  public isGridLoading!:boolean;
  public inEditMode:boolean=false;
  public editedRowIndex!:number;
  public pageSize = 10;
  public skip = 0;
  public gridData :GridDataResult={total:0,data:[]};
  public loading = false;
  public filterContent!:string;
  public onFilter!:boolean;
  // ICONS
  public addIcon =plusIcon;
  public exportIcon=exportIcon;
  public pencilIcon=pencilIcon;
  public saveIcon=saveIcon;
  public checkIcon=checkIcon;
  public xIcon=xIcon;
  public cancelIcon=cancelIcon;
  public trashIcon=trashIcon;
  public eyeIcon=eyeIcon;
  public searchIcon=searchIcon;
  public pdfIcon=filePdfIcon;

  public filterIcon=filterIcon;
  public filterClearIcon=filterClearIcon;
  public filterForm!:FormGroup;
  public lastValue!:any;

   public filters: FilterExpression[] = [
      {
        field: "timesheet_name",
        title: "Timesheet Name",
        editor: "string",
      },
      {
        field: "period_end_date",
        title: "Period End Date",
        editor: "date",
      },
      {
        field: "status",
        title: 'Status',
        editor:'string',
      }

    ]

  constructor(private router: Router ,private http:HttpClient ,private notify:NotificationServiceWrapper) { }
  ngOnInit() {
    this.isLoading=true;
    this.filterForm = new FormGroup({
      "timesheet_name": new FormControl(null),
      "period_end_date": new FormControl(null),
      "status": new FormControl(null)

    });
    this.getFromApi();
  }



  private getFromApi() {
    const page = this.skip / this.pageSize;
    const formData={'filter':this.filterForm.value,'page':page,'companyId':this.companyId,'pageSize':this.pageSize};
    this.http.post(`${this.baseUrl}/retriveTimesheetData`,formData)
             .subscribe(
              { next:(data: any) =>
              {
                this.gridData ={data:data.items,total:data.total} ;
                this.companyName = data.company;
                  console.log(data) ;
                  setTimeout(() => {

                  this.isLoading=false;
                  this.isGridLoading=false;
                }, 700);
                },
                error:(error:any)=>{
                  console.log('error in fetching data', error);
                }
              });
  }

  public onFilterValue() {
    this.onFilter = !this.onFilter;
    this.searchIcon = this.searchIcon == searchIcon ? xIcon : searchIcon;
  }

  public onSubmit() {
    this.isLoading = true;
    this.getFromApi();
  }

  public onClear() {
    this.isLoading = true;

    this.filterForm.reset();
    this.getFromApi();
  }


  public addHandler(args: AddEvent) {
    this.inEditMode=true;
    args.sender.closeRow(this.editedRowIndex);
    if((this.gridData.data.length)>=this.pageSize){
    this.lastValue = this.gridData.data.pop();}


    this.formGroup = this.createFormGroup();
    args.sender.addRow(this.formGroup);
  }
  public editHandler(args: EditEvent) {
    args.sender.closeRow(this.editedRowIndex);
    this.editedRowIndex=args.rowIndex;
    this.isGridInEditMode=true;
    this.formGroup = this.createFormGroup(args.dataItem);
    args.sender.editRow(args.rowIndex, this.formGroup);
  }
  public saveHandler(args: SaveEvent) {
    console.log(args);
    this.isGridInEditMode=false;
    this.inEditMode=false;
    if (args.rowIndex == -1) {

      console.log('griddata', args.formGroup.value);
      const data = args.formGroup.value;
      data.company_id = this.companyId;
      data.timesheet_count=0;
      data.period_end_date= formatDate(args.formGroup.value.period_end_date, 'yyyy-MM-dd','en-US', 'IST');
      this.gridData.data.unshift(data);
      this.http.post(`${this.baseUrl}/insert`, data).subscribe(
        {next:
        (response: any) => {
          console.log(response);
          this.getFromApi();
          this.loading = false;
          this.notify.showSuccess('Timesheet added Successfully');
        },
        error:(error) => {
          console.error('Error sending data:', error);
          this.notify.showError('Error Adding Timesheet');
        }});
    }
    else {
      this.loading = true;
      const editedDataId = this.gridData.data[args.rowIndex].timesheet_id;
      const editedData = args.formGroup.value;
      editedData.timesheet_id = editedDataId;
      editedData.company_id = this.companyId;
      editedData.timesheet_count=0;
      editedData.period_end_date= formatDate(args.formGroup.value.period_end_date, 'yyyy-MM-dd','en-US', 'IST');
      this.gridData.data[args.rowIndex] = editedData;
      this.http.post(`${this.baseUrl}/insert`, editedData).subscribe(
        {next:
        (response:any) => {
          this.getFromApi();
          this.loading = false;
          console.log('Data edited successfully:', response);
          this.notify.showSuccess('Timesheet edited Successfully')
        },
        error:(error) => {
          console.error('Error editing data:', error);
          this.notify.showError('Error Editing Timesheet');
        }});
    }
    args.sender.closeRow(args.rowIndex);
    this.isGridInEditMode=false;
    console.log('griddata', this.gridData);
  }
  public cancelHandler(args: CancelEvent) {
    args.sender.closeRow(args.rowIndex);
    if (this.lastValue){this.gridData.data.push(this.lastValue)}
    this.isGridInEditMode=false;
    this.inEditMode=false;
  }
  public removeHandler(args: RemoveEvent) {
    this.isGridLoading = true;
    const timesheet_id = args.dataItem.timesheet_id;
    this.http.get(`${this.baseUrl}/deleteTimesheet/${timesheet_id}`).subscribe(
      {next:
      (response) => {
        this.getFromApi();
        console.log('Data deleted successfully:', response);
        this.notify.showSuccess('Timesheet deleted successfully');

      },
      error:(error) => {
        console.error('Error deleting data:', error);
        this.notify.showError('Error deleting timesheet')
      }});

  }
  public timeSheetDetail(dataItem: any) {
    console.log(dataItem);
    this.router.navigate(['/afa/timesheetDetails', dataItem.timesheet_id]);
  }
  public pageChange(event: any) {
    this.isGridLoading = true;
    console.log(event);
    this.skip = event.skip;
    this.pageSize = event.take;
    this.getFromApi();
}
  public createFormGroup(data:any={}): FormGroup {
    return new FormGroup({
      name: new FormControl(data.name || '', Validators.required),
      period_end_date: new FormControl(data.period_end_date ? new Date(data.period_end_date) : null, Validators.required),
    });
  }

  public downloadExcel(timesheetId:number,timesheetName:string){

    this.http.get(`${this.baseUrl}/downloadTimesheet/${timesheetId}`,{responseType:'blob'}).subscribe({
      next:(response:Blob)=>{
          console.log(response,timesheetName)
          saveAs(response,timesheetName);

        },
        error:(error:any)=>{console.log(error)}

    })
  }




}
