import { Component } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '../../../../environments/environment.development';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { DialogModule } from '@progress/kendo-angular-dialog';
import { InputsModule } from '@progress/kendo-angular-inputs';
import { GridDataResult, GridModule } from '@progress/kendo-angular-grid';
import { Router } from '@angular/router';
import { KENDO_ICON, KENDO_SVGICON } from '@progress/kendo-angular-icons';
import { plusIcon, exportIcon, pencilIcon, filterClearIcon, saveIcon, searchIcon, filterIcon, checkIcon, xIcon, cancelIcon, trashIcon, eyeIcon } from '@progress/kendo-svg-icons';
import { KENDO_BUTTONS } from '@progress/kendo-angular-buttons';
import { NotificationServiceWrapper } from '../../../shared/notification.service';
import { Organization } from '../../../afa/afa-components/Model';
import { SpinnerComponent } from '../../../shared/spinner/spinner.component';
import { FilterExpression } from '@progress/kendo-angular-filter';
import { ThemeComponent } from '../../../shared/theme/theme.component';





@Component({
  selector: 'app-dashboard-admin',
  templateUrl: './dashboard-admin.component.html',
  imports: [
    CommonModule,
    ReactiveFormsModule,
    DialogModule,
    InputsModule,
    GridModule,
    KENDO_ICON,
    KENDO_SVGICON,
    KENDO_BUTTONS,
    SpinnerComponent,
    ThemeComponent
  ],
  styleUrl: './dashboard-admin.component.css'
})

export class DashboardAdminComponent {
  public apiurl = environment.apiUrl;
  public gridData: GridDataResult = { data: [], total: 0 };
  public orgForm!: FormGroup;
  public selectedOrg: Organization | null = null;
  public isDialogOpen = false;
  public count!: any;
  public countActive!: any;
  public isLoading!: boolean;
  public addIcon = plusIcon;
  public exportIcon = exportIcon;
  public pencilIcon = pencilIcon;
  public saveIcon = saveIcon;
  public checkIcon = checkIcon;
  public xIcon = xIcon;
  public cancelIcon = cancelIcon;
  public trashIcon = trashIcon;
  public eyeIcon = eyeIcon;
  public searchIcon = searchIcon;
  public filterIcon = filterIcon;
  public filterClearIcon = filterClearIcon;
  public base64String!: string;
  public currentPage: number = 1;
  public pageSize: number = 5;
  public skip: number = 0;
  public loading = false;
  public onFilter: boolean = false;
  public emailExists = true;
  public filterForm!: FormGroup;
  public filters: FilterExpression[] = [
    {
      field: "organization_name",
      title: "Organization Name",
      editor: "string",
    }
  ]
  public isDeleteDialogOpen: boolean = false;
  public orgToDelete: Organization | null = null;


  constructor(
    private http: HttpClient,
    private fb: FormBuilder,
    private router: Router,
    private notify: NotificationServiceWrapper,

  ) { }

  public ngOnInit(): void {
    this.isLoading = true;
    this.skip = 0;
    this.filterForm = new FormGroup({
      "organization_name": new FormControl(null),
    });
    this.loadData();
    this.skip = 0;
    this.countOrganization();
    this.countActiveOrganization();
    this.loadData();
    console.log(this.count);
  }

  public countOrganization() {
    this.http.post<Organization[]>(`${this.apiurl}/countOrganization`, {
      headers: new HttpHeaders({
        'Content-Type': 'application/json'
      })
    }).subscribe(
      (data) => {
        this.count = data;
      })
  }

  public countActiveOrganization() {
    this.http.post<Organization[]>(`${this.apiurl}/countActiveOrganization`, {
      headers: new HttpHeaders({
        'Content-Type': 'application/json'
      })
    }).subscribe((data) => {
      this.countActive = data;
    })
  }



  public loadData() {
    const currentPage = this.skip / this.pageSize + 1;

    console.log("Loading laravel page:", currentPage);


    let details = {
      pageSize: this.pageSize,
      page: this.skip / this.pageSize,
      filter: this.filterForm.value,
    };
    console.log(details);
    this.http.post(`${this.apiurl}/getOrganizationDetails`, details)
      .subscribe({
        next: (data: any) => {
          this.gridData = {
            data: data.data,
            total: data.total
          };

          setTimeout(() => {
            this.isLoading = false;
          }, 700);
        },


        error: (err) => {
          console.error('Error loading data', err);
          this.isLoading = false;
        }
      });

  }

  public openDialog(org?: Organization) {
    this.selectedOrg = org || null;
    this.isDialogOpen = true;
    this.emailExists = false;

    this.orgForm = this.fb.group({
      organization_id: [org?.organization_id],
      organization_name: [
        org?.organization_name || '',
        [Validators.required, Validators.pattern(/^[a-zA-Z 0-9]+$/)]
      ],
      email: [org?.email || '', [Validators.required, Validators.email]],
      address: [org?.address || '', Validators.required],
      state: [
        org?.state || '',
        [Validators.required, Validators.pattern(/^[a-zA-Z ]+$/)]
      ],
      pincode: [
        org?.pincode || '',
        [Validators.required, Validators.pattern(/^[0-9]{1,6}$/)]
      ],
      organization_logo: [org?.organization_logo || '', Validators.required],

      status: [org?.status?.toString() === '1'],

    });

    console.log('Editing org status:', org?.status);

  }

  public closeDialog() {
    this.isDialogOpen = false;
  }

  public save() {
    this.closeDialog();
     if (this.orgForm.invalid) return;

    const formValue = this.orgForm.value;
    formValue.status = formValue.status ? '1' : '0';
    console.log('Saving form value:', formValue);
    if (this.selectedOrg) {
      // EDIT

      const id = this.selectedOrg.organization_id;
      console.log(formValue);
      this.isLoading=true;
      this.http.post(`${this.apiurl}/updateOrganization/`, formValue, {
        headers: new HttpHeaders({
          'Content-Type': 'application/json'
        })
      }).subscribe({
        next: (data) => {
          
          this.loadData();
          this.isLoading = false;
          this.countActiveOrganization();
          this.notify.showSuccess('Organization Updated Successfully');
        },
        error: (error) => {
          console.error('Error updating data', error);
          this.notify.showError(error);
        }
      },
      );
    } else {
      // ADD
      
      console.log(formValue);
      
      
      if (this.orgForm.invalid) return;

      this.isLoading = true;
      this.http.post(`${this.apiurl}/storeOrganization`, formValue, {
        headers: new HttpHeaders({
          'Content-Type': 'application/json'
        })
      }).subscribe({
        next: (response: any) => {
          console.log(response);
          if (response.status === 409) {
            this.emailExists = true;
            this.isLoading = false;
            return;
          }

          this.closeDialog();
          this.loadData();
          this.countOrganization();
          this.countActiveOrganization();
          
          this.isLoading=false;
          this.notify.showSuccess('Organization Added Successfully');
          
        }, error: (error) => {
          console.error('Error adding data', error);
          this.notify.showError(error);
          this.isLoading = false;
        }
      });
    }
  }

  public removeHandler(item: Organization) {
    this.orgToDelete = item;
    this.isDeleteDialogOpen = true;
  }

  public viewHandler(dataItem: any) {
    this.isLoading = true;
    setTimeout(() => {
      this.isLoading = false;
    }, 700);
    this.router.navigate(['super-admin/user', dataItem.organization_id]);
  }
  onFileChange(event: any) {
    console.log(event);
    const file = event.target.files[0];
    const reader = new FileReader();

    reader.onload = () => {
      this.base64String = reader.result as string;
      this.orgForm.get('organization_logo')?.setValue(this.base64String);
      console.log("Base64 image:", this.base64String);
      console.log(this.orgForm.value);
    };

    if (file) {
      reader.readAsDataURL(file);
    }
  }

  getImage(base64: string): string {
    if (!base64) return '';
    if (base64.startsWith('data:image')) {
      return base64;
    }
    const mimeType = 'image/jpeg';

    return `data:${mimeType};base64,${base64}`;
  }

  public pageChange(event: any) {
    this.skip = event.skip;
    this.pageSize = event.take;
    this.loadData();
  }

  public onFilterValue() {
    if (this.onFilter) {

      this.onFilter = false;
      this.searchIcon = this.searchIcon == searchIcon ? xIcon : searchIcon;
      this.pageSize = 5;
      this.loadData();
    }
    else {

      this.onFilter = true;
      this.isLoading = true;
      this.searchIcon = this.searchIcon == searchIcon ? xIcon : searchIcon;
      this.pageSize = 5;
      this.loadData();
    }
  }

  public onSubmit() {
    this.isLoading = true;
    this.loadData();
  }

  public onClear() {
    this.isLoading = true;
    this.filterForm.reset();
    this.pageSize = 5;
    this.loadData();
  }
  public emailChange(event: any) {
    this.emailExists = false;
  }
  public onCancelDelete(): void {
    this.isDeleteDialogOpen = false;
    this.orgToDelete = null;
  }

  public onConfirmDelete(): void {
    if (!this.orgToDelete) return;
    const id = { organization_id: this.orgToDelete.organization_id };
    this.onCancelDelete();


    setTimeout(() => {
      this.isLoading = true;



      this.http.post(`${this.apiurl}/deleteOrganization`, id, {
        headers: new HttpHeaders({ 'Content-Type': 'application/json' })
      }).subscribe({
        next: () => {
          this.loadData();
          this.countOrganization();
          this.countActiveOrganization();
          this.notify.showSuccess('Organization Deleted Successfully');
          this.isLoading = false;
        },
        error: (error) => {
          console.error('Error deleting organization', error);
          this.notify.showError('Failed to delete organization');
          this.isLoading = false;
        }
      });
    }, 700);
  }
}


