import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Component, NgModule } from '@angular/core';
import { FormsModule, NgForm } from '@angular/forms';
import { KENDO_BARCODES } from "@progress/kendo-angular-barcodes";
import { KENDO_INPUTS } from '@progress/kendo-angular-inputs';
import { KENDO_LABELS } from "@progress/kendo-angular-label";
import { environment } from '../../../environments/environment.development';
import { ActivatedRoute, Router } from '@angular/router';
import { NgIf } from '@angular/common';
import { NotificationService } from '@progress/kendo-angular-notification';
import { NotificationServiceWrapper } from '../../shared/notification.service';
@Component({
  selector: 'app-mfa',
  imports: [KENDO_BARCODES, FormsModule, KENDO_INPUTS, KENDO_LABELS, NgIf],
  templateUrl: './mfa.component.html',
  styleUrl: '../Auth.css'
})
export class MfaComponent {
  constructor(protected http: HttpClient, protected route: ActivatedRoute, protected router: Router, private notify: NotificationServiceWrapper) { }
  baseUrl: string = environment.baseUrl;
  qrUrl!: any;

  mfaStatus: any = 0;
  ngOnInit() {

    this.http.get(`${this.baseUrl}/generateQrCode`).subscribe(
      (response: any) => {
        console.log('from generateQr', response);
        if (response.status == 1) {
          this.qrUrl = response.qrUrl;
          this.mfaStatus = 1;
          console.log(this.mfaStatus);
        }
      }
    )
  }
  otpHandler(args: any) {
    const enteredOtp = args.form.value.otp;
    const headers = new HttpHeaders({
      'Content-Type': 'application/json'
    });
    const request = { 'otp': enteredOtp };
    this.http.post(`${this.baseUrl}/verifyMfa`, request, { headers }).subscribe(
      (response: any) => {
        if (response['status'] == 0) {
          this.notify.showError( "Invalid OTP");
        }
        else {

          if (response['role'] == 1) {
            this.router.navigate(['/super-admin/dashboard']);

          }
          else{
            this.router.navigate(['/afa/dashboard']);
          }


      }
      }
    )
  }

}
