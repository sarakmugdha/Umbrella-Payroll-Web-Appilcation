import { HttpClient } from '@angular/common/http';
import { Component } from '@angular/core';
import { FormControl, FormGroup,  ReactiveFormsModule, Validators } from '@angular/forms';
import { GridDataResult, KENDO_GRID } from '@progress/kendo-angular-grid';
import { environment } from '../../../../environments/environment.development';
import { KENDO_DIALOGS } from '@progress/kendo-angular-dialog';
import { IconModule, KENDO_ICONS, KENDO_SVGICON } from '@progress/kendo-angular-icons';
import { SVGIcon, searchIcon, filterIcon, filterClearIcon } from "@progress/kendo-svg-icons";
import { CommonModule } from '@angular/common';
import { KENDO_DATEINPUTS } from '@progress/kendo-angular-dateinputs';
import { KENDO_LABELS } from '@progress/kendo-angular-label';
import { KENDO_BUTTONS } from '@progress/kendo-angular-buttons';
import { plusIcon, pencilIcon, trashIcon, eyeIcon, xIcon} from '@progress/kendo-svg-icons';
import { PeopleType, CompanyType } from '../Model';
import { NotificationServiceWrapper } from '../../../shared/notification.service';
import { SpinnerComponent } from '../../../shared/spinner/spinner.component';
import { FilterExpression } from "@progress/kendo-angular-filter";
import { RouterLink } from '@angular/router';
import { ValidationService } from '../Validation.service';

@Component({
  selector: 'app-employee',
  templateUrl: './People.component.html',
  imports: [KENDO_GRID,
            ReactiveFormsModule,
            KENDO_DIALOGS,
            IconModule,
            KENDO_ICONS,
            CommonModule,
            KENDO_DATEINPUTS,
            KENDO_LABELS,
            KENDO_BUTTONS,
            KENDO_SVGICON,
            SpinnerComponent,
            RouterLink
            ],
  styleUrl: './People.component.css'
})
export class PeopleComponent {

  constructor(private http: HttpClient,
              private notificationService: NotificationServiceWrapper,
              public validationService: ValidationService
            ) {}

  //grid
  public gridData: GridDataResult =  {total: 0, data: []};
  public loading: boolean = true;
  private apiUrl: string = environment.apiUrl;
  //dialog box (edit and add)
  public selectedPeople: PeopleType | null = null;
  public opened: boolean = false;
  public peopleForm!: FormGroup ;
  public emailExists: boolean = false;
  public eighteenYearsAgo!: Date;
  public companies = [] as CompanyType[];
  public date_of_birth: Date | null = null;
  public isNinoExists: boolean = false;
  //icons
  public addIcon: SVGIcon =plusIcon;
  public pencilIcon: SVGIcon=pencilIcon;
  public trashIcon: SVGIcon=trashIcon;
  public eyeIcon: SVGIcon=eyeIcon;
  public searchIcon: SVGIcon = searchIcon;
  public filterIcon: SVGIcon = filterIcon;
  public filterClearIcon: SVGIcon = filterClearIcon;
  public xIcon: SVGIcon=xIcon;
  //variables for backend pagination
  public pageSize: number = 10;
  public skip: number = 0;
  //variables for backend filteration
  public filters: FilterExpression[] = [
    {
      field: "company_name",
      title: "Company Name",
      editor: "string",
    },
    {
      field: "name",
      title: "People Name",
      editor: "string"
    },
    {
      field: "email",
      title: "Email Id",
      editor: "string",
    },
    {
      field: "job_type",
      title: "Job Domain",
      editor: "string"
    },
    {
      field: "ni_no",
      title: "NI. NO.",
      editor: "string"
    }
  ]
  public onFilter: boolean = false;
  public filterForm!: FormGroup;
  // delete confirmation
  public isDeleteDialogOpen: boolean = false;
  public deleteDataItem: any;

  public ngOnInit() {
    this.getCompanyList();
    this.eighteenYearsAgo = new Date();
    let restriction = this.eighteenYearsAgo.setUTCFullYear(this.eighteenYearsAgo.getUTCFullYear() - 18);
    this.eighteenYearsAgo = new Date(restriction);

    this.filterForm = new FormGroup({
      "company_name": new FormControl(null),
      "name": new FormControl(null),
      "email": new FormControl(null),
      "job_type": new FormControl(null),
      "ni_no": new FormControl(null),
    });
    this.getFromAPI();
  }

  public getFromAPI(): void {

    let details = {
                filter: this.filterForm.value,
                pageSize: this.pageSize,
                page: this.skip / this.pageSize,
    };
    this.http.post(`${this.apiUrl}/peopleDetails`,details)
      .subscribe((data: any) => {
        this.gridData = data;
        setTimeout(()=>{
          this.loading = false;
        },1500)

      });
  }

  public getCompanyList(){
    this.http.post<CompanyType[]>(`${this.apiUrl}/getCompaniesList`,{})
      .subscribe((data)=>{
        this.companies = data;
      })
  }

  public open(people?: PeopleType): void{
    this.loading = true;
    this.opened = true;
    this.emailExists = false;
    this.isNinoExists = false;
    this.date_of_birth = people?.date_of_birth ? new Date(people.date_of_birth) : null;
    this.selectedPeople = people || null;

    this.peopleForm = new FormGroup({
      people_id : new FormControl(people?.people_id),
      name: new FormControl(people?.name,[Validators.required, Validators.pattern(/^[a-zA-Z ].*$/)]),
      email: new FormControl(people?.email,[Validators.required, Validators.email]),
      gender: new FormControl(people?.gender,[Validators.required]),
      date_of_birth: new FormControl (people?.date_of_birth? new Date(people.date_of_birth): null,[Validators.required]),
      job_type: new FormControl(people?.job_type,[Validators.required, Validators.pattern(/^[a-zA-Z ]*$/)]),
      address: new FormControl(people?.address,[Validators.required]),
      city: new FormControl(people?.city, [Validators.required,Validators.pattern(/^[a-zA-Z ]*$/)]),
      state: new FormControl(people?.state,[Validators.required ,Validators.pattern(/^[a-zA-Z ]*$/)]),
      country: new FormControl(people?.country,[Validators.required, Validators.pattern(/^[a-zA-Z ]*$/)]),
      pincode: new FormControl(people?.pincode, [Validators.required ,Validators.pattern(/^[0-9]{1,10}$/)]),
      company_id: new FormControl(people?.company_id, [Validators.required]),
      company_name: new FormControl(people?.company_name),
      ni_no: new FormControl(people?.ni_no, [Validators.required, Validators.pattern(/^\s*[a-zA-Z]{2}(?:\s*\d\s*){6}[a-zA-Z]?\s*$/)]),
    });
  }

  public close(){
    this.opened = false;
    this.peopleForm.reset();
    this.getFromAPI();

  }

  public onSave(){
    this.loading = true;
    if (this.peopleForm.invalid) return;

    if(this.selectedPeople){
      this.http.post<any>(`${this.apiUrl}/updatePeople`, this.peopleForm.value)
        .subscribe({
          next: (response) => {
            if (response.status === 409) {
              this.notificationService.showError("Email already exists");
              this.emailExists = true;
              return;
            }
            if(response.status === 422){
              this.notificationService.showError("Exception Occured");
            }
            this.loading = true;
            this.close();

            this.getFromAPI();

            this.notificationService.showSuccess("People Updated Successfully");
          },
          error: (err) => {
            let errorMsg = err?.error?.errorMsg || 'An unexpected error occurred.';
            this.notificationService.showError(errorMsg);
          }
      });
    }

    else{
      this.http.post<any>(`${this.apiUrl}/insertPeople`, this.peopleForm.value)
      .subscribe({
        next: (response) => {
          if (response.status === 409) {
            response.emailExists ? (this.emailExists = true) : (this.isNinoExists = true);
            return;
          }
          if(response.status === 422){
            this.notificationService.showError("Exception Occured");
            return;
          }
          this.close();
          if(response.status === 200){
            this.notificationService.showSuccess("People Added Successfully");
          }
          this.getFromAPI();
          this.loading = false;
        },
        error: (err) =>{
          let errorMsg = err?.error?.errorMsg || 'An unexpected error occurred.';
          this.notificationService.showError(errorMsg);
        }
      });
    }
  }

  public removeHandler(dataItem: any){
    let id = {people_id: dataItem.dataItem.people_id};
    this.loading = true;
    this.http.post<PeopleType[]>(`${this.apiUrl}/deletePeople`, id)
    .subscribe({
      next: (response) => {
        this.getFromAPI();
        this.loading = false;
        this.notificationService.showSuccess("People Removed Successfully");
      },
      error: (err) => {
        let errorMsg = err?.error?.error || 'An unexpected error occurred.';
        this.notificationService.showError(errorMsg);
        }
    });
  }

  public restrictEmailSymbols(event: any) {
    this.emailExists = false;
    this.validationService.restrictEmailSymbols(event);
  }

  public restrictNINO(event: any){
    this.isNinoExists = false;
    this.validationService.restrictNINO(event);
  }

  public isInvalid(event: string): boolean {
    let control = this.peopleForm.get(event);
    return !!(control && control.invalid && (control.dirty || control.touched));
  }

  // backend pagination
  public pageChange(event: any){
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