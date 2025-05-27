import { CommonModule, JsonPipe } from '@angular/common';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute,  RouterLink, Router } from '@angular/router';
import { NotificationServiceWrapper } from '../../shared/notification.service';
import { AuthService } from '../../Service/auth-service.service';
import { environment } from '../../../environments/environment.development';

@Component({
  selector: 'app-password-setup-component',
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './password-setup.component.html',
  styleUrl: '../Auth.css'
})

export class PasswordSetupComponent {
  id!: string | null;
  hash!: string | null;
  exp!: string | null;
  apiUrl = environment.baseUrl;
  constructor(private route: ActivatedRoute, private http: HttpClient, private router: Router,private notify:NotificationServiceWrapper,
    private authService:AuthService
  ) { }

  ngOnInit() {
    this.id = this.route.snapshot.paramMap.get('id');
    this.hash = this.route.snapshot.paramMap.get('hash');
    this.exp = this.route.snapshot.queryParamMap.get('exp');
  }

  onSubmit(form: any) {
    console.log(this.id, this.hash, this.exp, form.value);
    const url=`${this.apiUrl}/password-setup/${this.id}/${this.hash}?exp=${this.exp}`
    this.authService.pwdSetup(form.value,url)
      .subscribe({
        next: (res: any) => { 
          this.notify.showSuccess('Password Set-up successful');
          this.router.navigate(['/']);
        },
        error: (err: any) => {
          if(err.status==403){
            this.notify.showError('Password Already Set');
            this.router.navigate(['/'])
          }
          else{
          this.notify.showError('Password Link Expired');
          this.router.navigate(['/'])
          }
        }
      });
  }

  
}
