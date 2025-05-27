import { HttpClient } from '@angular/common/http';
import { Component } from '@angular/core';
import { GridDataResult, KENDO_GRID } from '@progress/kendo-angular-grid';
import { environment } from '../../../../environments/environment.development';
import { Router, RouterLink } from '@angular/router';
import { KENDO_DIALOGS } from '@progress/kendo-angular-dialog';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { IconModule, KENDO_ICON, KENDO_SVGICON } from '@progress/kendo-angular-icons';
import { ButtonsModule, KENDO_BUTTONS } from '@progress/kendo-angular-buttons';
import { GridModule } from '@progress/kendo-angular-grid';
import { InputsModule } from '@progress/kendo-angular-inputs';
import { plusIcon, pencilIcon, trashIcon, eyeIcon, xIcon } from '@progress/kendo-svg-icons';
import { SVGIcon, searchIcon, filterIcon, filterClearIcon } from "@progress/kendo-svg-icons";
import { NotificationServiceWrapper } from '../../../shared/notification.service';
import { CompanyType } from '../Model';
import { SpinnerComponent } from '../../../shared/spinner/spinner.component';
import { FilterExpression } from '@progress/kendo-angular-filter';
import { ValidationService } from '../Validation.service';

@Component({
  selector: 'app-umbrella-company',
  imports: [KENDO_GRID,
    KENDO_DIALOGS,
    CommonModule,
    ReactiveFormsModule,
    KENDO_ICON,
    IconModule,
    ButtonsModule,
    InputsModule,
    GridModule,
    KENDO_BUTTONS,
    KENDO_SVGICON,
    SpinnerComponent ,RouterLink
  ],
  templateUrl: './Companies.component.html',
  styleUrl: '../People/People.component.css'
})
export class CompaniesComponent {

  constructor(private http: HttpClient,
    private router: Router,
    private notificationService: NotificationServiceWrapper,
    public validationService: ValidationService
  ) { }

  //grid
  public company: GridDataResult = { total: 0, data: [] };
  public loading: boolean = true;
  public apiUrl: string = environment.apiUrl;
  public orgId!: string | null;
  //dialog box
  public selectedCompany: CompanyType | null = null;
  public selectedCompanyId: string | null = null;
  public opened: boolean = false;
  public companyForm!: FormGroup;
  public previewLogo: string | ArrayBuffer | null = null;
  public logoPreviews: { [key: number]: string | ArrayBuffer | null } = {};
  public base64String!: string;
  public emailExists: boolean = false;
  public imageType: boolean  = false;
  //icons
  public addIcon: SVGIcon  = plusIcon;
  public pencilIcon: SVGIcon = pencilIcon;
  public trashIcon: SVGIcon = trashIcon;
  public eyeIcon: SVGIcon = eyeIcon;
  public searchIcon: SVGIcon = searchIcon;
  public filterIcon: SVGIcon = filterIcon;
  public filterClearIcon: SVGIcon = filterClearIcon;
  public xIcon: SVGIcon = xIcon;
  //backend pagination
  public pageSize: number = 5;
  public skip: number = 0;
  //backend filteration
  public filters: FilterExpression[] = [
      {
        field: "company_name",
        title: "Company Name",
        editor: "string",
      },
      {
        field: "email",
        title: "Email Id",
        editor: "string",
      },
      {
        field: "vat_percent",
        title: "VAT Percentage",
        editor: "number"
      },
      {
        field: "city",
        title: "City",
        editor: "string"
      }
    ]
  public onFilter: boolean = false;
  public filterForm!: FormGroup;
  // delete confirmation
  public isDeleteDialogOpen: boolean = false;
  public deleteDataItem: any;

  public ngOnInit() {
    this.filterForm = new FormGroup ({
      "company_name": new FormControl(null),
      "email": new FormControl(null),
      "vat_percent": new FormControl(null),
      "city" : new FormControl(null),
    });
    this.getFromAPI();
  }

  public getFromAPI() {
    this.loading = true;
    let details = {
      pageSize: this.pageSize,
      page: this.skip / this.pageSize,
      filter: this.filterForm.value,
    };
    this.http.post(`${this.apiUrl}/companiesList`, details)
      .subscribe((data: any) => {
        this.company = { data: data.company, total: data.total };
        this.loading = false;
      });
  }

  public viewHandler(dataItem: any) {
    this.loading = true;
    this.selectedCompanyId = dataItem.company_id;
    sessionStorage.setItem('companyId',dataItem.company_id);
    this.router.navigate(['afa/company']);
    
  }

  public open(company?: CompanyType): void {
    this.loading = true;
    this.opened = true;
    this.selectedCompany = company || null;
    this.emailExists = false;
    this.imageType = false;

    this.companyForm = new FormGroup({
      company_id: new FormControl(company?.company_id),
      company_name: new FormControl(company?.company_name, [Validators.required, Validators.pattern(/^[a-zA-Z. ]*$/)]),
      email: new FormControl(company?.email, [Validators.required, Validators.email]),
      phone_number: new FormControl(company?.phone_number, [Validators.required, Validators.pattern(/^[0-9]{10}$/)]),
      vat_percent: new FormControl(company?.vat_percent, [Validators.required, Validators.pattern(/^[0-9.]/)]),
      domain: new FormControl(company?.domain, [Validators.required, Validators.pattern(/^[a-zA-Z ]*$/)]),
      address: new FormControl(company?.address, [Validators.required]),
      city: new FormControl(company?.city, [Validators.required, Validators.pattern(/^[a-zA-Z ]*$/)]),
      state: new FormControl(company?.state, [Validators.required, Validators.pattern(/^[a-zA-Z ]*$/)]),
      country: new FormControl(company?.country, [Validators.required, Validators.pattern(/^[a-zA-Z ]*$/)]),
      pincode: new FormControl(company?.pincode, [Validators.required, Validators.pattern(/^[0-9]{1,10}$/)]),
      company_logo: new FormControl(company?.company_logo, [Validators.required]),
    });
  }
  
  public close(): void {
    this.opened = false;
    this.companyForm.reset();
    this.getFromAPI();
    this.loading = false;
  }

  public onSave() {
    if (this.companyForm.invalid) return;
    this.loading = true;
    if (this.selectedCompany) {
      //edit
      this.http.post<any>(`${this.apiUrl}/updateCompanies`, this.companyForm.value)
        .subscribe({
          next: (response) => {
            if (response.status === 422) {
              this.notificationService.showError("Exception Occured");
              this.emailExists = true;
              return;
            }
            if(response.status === 415){
              const errorMsg = response.message || 'Logo must be a .jpeg or .jpg format';
              this.notificationService.showError(errorMsg);
              this.imageType = true;
              return;
            }
            this.close();
            this.getFromAPI();
            this.loading = false;
            this.notificationService.showSuccess("Company updated successfully");
          },
          error: (err) => {
            this.notificationService.showError("Exception occured!");
          }
      });
    }
    else {
      //add
      this.http.post<any>(`${this.apiUrl}/insertCompany`, this.companyForm.value)
        .subscribe({
          next: (response) => {
            if (response.status === 422) {
              this.notificationService.showError("Exception Occured");
              this.emailExists = true;
              return;
            }
            if(response.status === 415){
              const errorMsg = response.message || 'Logo must be a .jpeg or .jpg format';
              this.notificationService.showError(errorMsg);
              this.imageType = true;
              return;
            }
            this.close();
            this.getFromAPI();
            this.loading = false;
            this.notificationService.showSuccess("Company created successfully");
          },
          error: (err) => {
            this.notificationService.showError("Exception occured!");
          }
        });
    }
  }

  public removeHandler(dataItem: any) {
    this.loading = true;
    let id = { company_id: dataItem.dataItem.company_id };
    this.http.post<CompanyType[]>(`${this.apiUrl}/deleteCompanies`, id)
      .subscribe({
        next: (response: any) => {
          if (response.status === 422) {
            this.notificationService.showError("Exception Occured");
            return;
          }
          this.getFromAPI();
          this.loading = false;
          this.notificationService.showSuccess("Company Deleted Successfully");
        },
        error: (err) => {
          this.notificationService.showError("Exception occured!");
        }
      });
  }

  public restrictEmailSymbols(event: any) {
    this.emailExists = false;
    this.validationService.restrictEmailSymbols(event);
  }

  public isInvalid(event: string): boolean {
    let control = this.companyForm.get(event);
    return !!(control && control.invalid && (control.dirty || control.touched));
  }

  public onFileChange(event: any) {
    let file = event.target.files[0];
    let reader = new FileReader();
    this.imageType = false;
    reader.onload = () => {
      this.base64String = reader.result as string;
      this.companyForm.get('company_logo')?.setValue(this.base64String);
    };
    if (file) {
      reader.readAsDataURL(file);
    }
  }

  // backend pagination
  public pageChange(event: any) {
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