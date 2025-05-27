import { HttpClient } from '@angular/common/http';
import { Component } from '@angular/core';
import { environment } from '../../../environments/environment.development';
import { FormControl, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { KENDO_DROPDOWNS } from "@progress/kendo-angular-dropdowns";
import { KENDO_LABELS } from '@progress/kendo-angular-label';
import { KENDO_INPUTS } from '@progress/kendo-angular-inputs';
import { MatAutocompleteModule } from '@angular/material/autocomplete';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';


@Component({
  selector: 'search-bar',
  imports: [KENDO_DROPDOWNS,
    KENDO_LABELS,
    ReactiveFormsModule,
    CommonModule,
    KENDO_INPUTS,
    MatAutocompleteModule],
  templateUrl: './SearchBar.component.html',
  styleUrl: './SearchBar.component.css'
})
export class SearchBarComponent {
  public searchForm!: FormGroup;
  public suggestedCompanyList: any = null;
  public apiUrl: string = environment.apiUrl;

  constructor(private http: HttpClient, private router: Router) { }

  public ngOnInit() {
    this.searchForm = new FormGroup({
      search: new FormControl(null),
    });
  }

  public searchChange(event: any) {
    this.http.post(`${this.apiUrl}/searchCompany`, this.searchForm.value)
      .subscribe({
        next: (data: any) => {
          this.suggestedCompanyList = data.company_list;
        }
      });
  }

  public onCompanySelected(event: any) {
    sessionStorage.setItem("companyId", event.option.id);
    this.refreshRoute();
  }

  refreshRoute() {
    let currentUrl = this.router.url;
    if (currentUrl.endsWith('/companies') || currentUrl.endsWith('/people') || currentUrl.endsWith('/afa/dashboard')) {
      this.router.navigate(['/afa/company']);
      return;
    }
    this.router.navigateByUrl('/afa/dashboard', { skipLocationChange: true }).then(() => {
      this.router.navigate([currentUrl]);
    });
  }

}
