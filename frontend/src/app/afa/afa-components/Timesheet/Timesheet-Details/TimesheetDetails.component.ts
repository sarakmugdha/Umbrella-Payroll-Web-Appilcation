import { Component } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { AddEvent, CancelEvent, EditEvent, ExcelCommandToolbarDirective, GridDataResult, KENDO_GRID, KENDO_GRID_EXCEL_EXPORT, RemoveEvent, SaveEvent } from '@progress/kendo-angular-grid';
import { CurrencyPipe, JsonPipe, NgFor, NgIf } from '@angular/common';
import { uploadIcon, plusIcon, exportIcon, pencilIcon, saveIcon, checkIcon, xIcon, cancelIcon, trashIcon, eyeIcon ,filterIcon,filterClearIcon,searchIcon,fileExcelIcon} from '@progress/kendo-svg-icons';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { FormControl, FormGroup, FormsModule, NgModel, ReactiveFormsModule, Validators } from '@angular/forms';
import { KENDO_UPLOADS } from '@progress/kendo-angular-upload';
import { KENDO_BUTTON } from '@progress/kendo-angular-buttons';
import { environment } from '../../../../../environments/environment.development';
import { KENDO_DROPDOWNLIST } from '@progress/kendo-angular-dropdowns';
import { KENDO_SVGICON } from '@progress/kendo-angular-icons';
import { KENDO_DIALOGS } from '@progress/kendo-angular-dialog';
import { SpinnerComponent } from '../../../../shared/spinner/spinner.component';
import { FilterExpression } from '@progress/kendo-angular-filter';
import { NotificationServiceWrapper } from '../../../../shared/notification.service';
@Component({
  selector: 'app-file-upload',
  imports: [KENDO_GRID, ReactiveFormsModule, NgIf, KENDO_UPLOADS, KENDO_BUTTON, KENDO_SVGICON, KENDO_DROPDOWNLIST, NgFor, FormsModule, KENDO_DIALOGS, CurrencyPipe,SpinnerComponent,RouterLink,KENDO_GRID_EXCEL_EXPORT],
  templateUrl: 'TimesheetDetails.component.html'
})
export class TimesheetDetailsComponent {
  public baseUrl = environment.baseUrl;
  public isLoading!: boolean;
  // GRID STATE
  public gridData: GridDataResult = { total: 0, data: [] };
  public inEditMode: boolean = false;
  public isGridLoading!: boolean;
  public editedRowIndex!: number;
  public invoiceSent: number = 1;
  public inAddMode: boolean = false;
  public pageSize = 10;
  public skip = 0;
  public filterContent!:string;

  public isGridInEditMode: boolean = false;
  // DATA ELEMENTS
  public companyId: any = sessionStorage.getItem('companyId');
  public companyName!:string;
  public timesheetName!:string;
  public assignmentIdList!: any;
  public assignmentId!: number;
  public timesheetId: any;
  public fileName!: any;
  public formGroup!: FormGroup;
  public assignmentDetails: any[] = [{
    "name": "",
    "customer_name": ""
  }]
  public dialogOpen: boolean = false;
  // GRID ICONS
  public uploadIcon = uploadIcon
  public addIcon = plusIcon;
  public exportIcon = exportIcon;
  public pencilIcon = pencilIcon;
  public saveIcon = saveIcon;
  public checkIcon = checkIcon;
  public xIcon = xIcon;
  public cancelIcon = cancelIcon;
  public trashIcon = trashIcon;
  public eyeIcon = eyeIcon;
  public filterIcon=filterIcon;
  public filterClearIcon=filterClearIcon;
  public fileExcelIcon=fileExcelIcon;

  public searchIcon=searchIcon;
  public filterForm!:FormGroup;
  public lastValue!:any;

  public onFilter!:boolean;

   public filters: FilterExpression[] = [
        {
          field: "assignment_number",
          title: "Assignment Number",
          editor: "number",
        },
        {
          field: "people_name",
          title: "People Name",
          editor: "string",
        },
        {
          field: "customer_name",
          title: 'Customer Name',
          editor:'string',
        },
        {
          field: "hours_worked",
          title: 'Hours Worked',
          editor:'number',
        },
        {
          field: "hourly_pay",
          title: 'Hourly Pay',
          editor:'number',
        }

      ]
  constructor(private route: ActivatedRoute, private http: HttpClient, private router: Router, private notify: NotificationServiceWrapper) { }
  ngOnInit() {
    this.isLoading= true;
    this.timesheetId = this.route.snapshot.paramMap.get('id');
    this.filterForm = new FormGroup({
      "assignment_number": new FormControl(null),
      "people_name": new FormControl(null),
      "customer_name": new FormControl(null),
      "hours_worked": new FormControl(null),
      "hourly_pay": new FormControl(null),


    });
    this.getFromApi();
  }
  public getFromApi() {
    const page = this.skip / this.pageSize;
   const formData={'filter':this.filterForm.value,'page':page,'companyId':this.companyId,'pageSize':this.pageSize,'timesheetId':this.timesheetId};
    this.http.post(`${this.baseUrl}/retriveTimesheetDetails`,formData)
      .subscribe({next:(data: any) => {
        this.gridData = { data: data.items, total: data.total };
        this.assignmentIdList = data.assignmentId;
        this.invoiceSent = data.invoiceSent;
        this.companyName = data.companyName;
        this.timesheetName = data.timesheetName;
        console.log(data)
        setTimeout(() => {
          this.isLoading = false;
          this.isGridLoading = false;
        },700);

      },error:(error:any)=>{
        console.log('error in fetching data', error);
      }});
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



  public fileUpload(event: any, inputFile: any) {
    this.isLoading= true;
    this.fileName = event.target.files[0].name;
    console.log(inputFile);
    const file = event.target.files[0];
    console.log(file);
    if (file) {
      const formData = new FormData();
      formData.append('file', file);
      formData.append('timesheet_id', this.timesheetId);
      formData.append('company_id', this.companyId);
      this.http.post(`${this.baseUrl}/extractCsvData`, formData)
        .subscribe(
          {next:(Response: any) => {
           this.getFromApi();

           setTimeout(() => {
            this.isLoading = false;
            this.notify.showSuccess('File uploaded successfully');
          },1300);

        },
          error:(error) => {
            console.log(error);
            this.notify.showError('Failed to upload ');

          }}
        );
      inputFile.value = "";
    }
  }
  // KENDO EVENTS HANDLER
  public editHandler(args: EditEvent) {
    args.sender.closeRow(this.editedRowIndex);
    this.editedRowIndex = args.rowIndex;
    console.log(args);
    this.isGridInEditMode = true;
    this.assignmentId = args.dataItem.assignment_id;
    this.http.get(`${this.baseUrl}/assignmentDetails/${this.assignmentId}`).subscribe({next:(response: any) => { this.assignmentDetails = response; },error:(error:any)=>{
      console.log('error in fetching data', error);
    }});
    console.log(this.assignmentId);
    this.formGroup = this.createFormGroup(args.dataItem);
    args.sender.editRow(args.rowIndex, this.formGroup);
  }
  public addHandler(args: AddEvent) {
    this.lastValue = this.gridData.data.pop();
    this.inAddMode = true;
    args.sender.closeRow(this.editedRowIndex);
    this.inEditMode = true;
    console.log(args);
    console.log(this.gridData);
    this.formGroup = this.createFormGroup();
    args.sender.addRow(this.formGroup);
  }
  public cancelHandler(args: CancelEvent) {
    if (this.lastValue){this.gridData.data.push(this.lastValue)}
    this.inAddMode = false;
    this.isGridInEditMode = false
    this.inEditMode = false;
    this.assignmentDetails = [{
      "name": "",
      "customer_name": ""
    }];
    args.sender.closeRow(args.rowIndex)
  }
  public saveHandler(args: SaveEvent) {
    this.inAddMode = false;
    this.isGridInEditMode = false;
    this.inEditMode = false;
    const headers = new HttpHeaders({
      'Content-Type': 'application/json'
    });
    let data = args.formGroup.value;
    data.assignment_id = this.assignmentId;
    data.timesheet_id = parseInt(this.timesheetId);
    data.people_name = this.assignmentDetails[0].name;
    data.customer_name = this.assignmentDetails[0].customer_name;
    if (args.rowIndex == -1) {
      console.log(args);
      this.gridData.data.unshift(data);
      this.http.post(`${this.baseUrl}/insertTimesheetDetails`, data, { headers }).subscribe({next:(response: any) => {
        this.getFromApi();
        this.notify.showSuccess('Timesheet details added successfully');
        console.log('Data sent successfully');
      },
        error:(error) => {
          console.error('Error sending data:', error);
          this.notify.showSuccess('Error adding timesheet detail');
        }});
    }
    else {
      data.timesheet_detail_id = args.dataItem.timesheet_detail_id;
      data.is_mapped = 1;
      console.log('DATA', data);
      console.log(args);
      this.gridData.data[args.rowIndex] = data;
      console.log('grid', this.gridData.data[args.rowIndex]);
      this.http.post(`${this.baseUrl}/insertTimesheetDetails`, data, { headers }).subscribe({next:(response: any) => {
        this.getFromApi();
        this.notify.showSuccess('Timesheet details edited successfully');
        console.log('Data edited successfully:', response);
      },
        error:(error) => {
          console.error('Error editing data:', error);
          this.notify.showError('Error editing timesheet detail');
        }});
    }
    args.sender.closeRow(args.rowIndex);
    this.refreshGrid();
    this.assignmentDetails = [{
      "name": "",
      "customer_name": ""
    }]
    console.log('griddata', this.gridData);
  }
  public removeHandler(args: RemoveEvent) {
    this.isGridLoading = true;
    const timesheetDetailId = args.dataItem.timesheet_detail_id;
    this.http.get(`${this.baseUrl}/deleteTimesheetDetail/${timesheetDetailId}`).subscribe({

    next:
      (response: any) => {

        this.getFromApi();

        console.log('Data deleted successfully:', response);
        this.notify.showSuccess('Timesheet details deleted successfully');
      },
      error:(error) => {
        console.error('Error deleting data:', error);
        this.notify.showError('Error deleting timesheet detail');
      }});

  }
  // OTHER FUNCTIONALITES
  public assignAssignment(args: any) {
    console.log(args)
    console.log(args.target.value)
    this.assignmentId = parseInt(args.target.value);
    this.http.get(`${this.baseUrl}/assignmentDetails/${this.assignmentId}`).
      subscribe({next:(response: any) => { this.assignmentDetails = response; },error:(error:any)=>{
        console.log('error in fetching data', error);
      }});
    console.log(this.assignmentDetails);
  }
  public createFormGroup(data: any = {}) {
    return new FormGroup({
      hours_worked: new FormControl(parseFloat(data.hours_worked) || "", Validators.required),
      hourly_pay: new FormControl(parseFloat(data.hourly_pay) || "", [Validators.required, Validators.min(5)])
    });
  }
  public proceedToInvoice(data: any) {
    let isUnMapped = false;
    for (let item of this.gridData.data) {
      if (!item.is_mapped) {
        isUnMapped = true;
        break;
      }
    }
    if (isUnMapped) {
      this.dialogOpen = true;
      console.log('unmapped found');
    }
    else {
      this.isLoading=true;
      this.http.get(`${this.baseUrl}/addInvoice/timesheet/${this.timesheetId}`).subscribe({next:(response) => { this.getFromApi(); console.log("success"); this.notify.showSuccess('Timesheet sent to invoice') }, error:(error) => { console.log(error) }});
      this.invoiceSent = 1;
    }
  }
  public close(data: any) {
    console.log(data);
    this.dialogOpen = false;
    if (data) {
      this.isLoading=true;
      this.http.get(`${this.baseUrl}/deleteUnmappedTimesheet/${this.timesheetId}`).subscribe(
        {next:(response: any) => {
          this.gridData = response;
          console.log('Data deleted successfully:', response);
        },
        error:(error) => {
          console.error('Error deleting data:', error);
        }});
      this.http.get(`${this.baseUrl}/addInvoice/timesheet/${this.timesheetId}`).subscribe({next:(response) => { this.getFromApi(); console.log("success");this.notify.showSuccess('Timesheet sent to invoice') }, error:(error) => { console.log(error) }});
      this.invoiceSent = 1;
    }
  }
  public refreshGrid() {
    this.gridData.data = [...this.gridData.data];
  }
  public pageChange(event: any) {
    this.isGridLoading = true;
    console.log(event);
    this.skip = event.skip;
    this.pageSize = event.take;
    this.getFromApi();
  }

  public searchGrid(event :any){
    console.log(event.target.value);
    this.filterContent=event.target.value;
    this.getFromApi();




  }
}
