import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { HttpBackend, HttpClient,HttpHeaders } from '@angular/common/http';
import { Router } from '@angular/router';
import { environment } from '../../environments/environment.development';

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  private token:string|null=null;
  private apiURL=environment.baseUrl;
  private httpClient:HttpClient;

  constructor(private backend:HttpBackend,private router:Router) {
    this.httpClient=new HttpClient(backend);
  }
  
  register(userDetails:any):Observable<any>{
    console.log(userDetails.value);
    console.log("auth service");
    return this.httpClient.post(`${this.apiURL}/register`,userDetails.value);
  }
  login(form:any){
    console.log('login interface');
    return this.httpClient.post(`${this.apiURL}/login`,form.value);
  }
  pwdSetup(form:any,url:any){
    console.log(form);
    return this.httpClient.post(url, form)
  }

  forgotPassword(form:any){
    console.log(form);
    return this.httpClient.post(`${this.apiURL}/forgot-pwd-setup`,form);





    
  }
  getToken(){
    return localStorage.getItem('auth_token');
  }
  storeToken(token:string):void{  
        localStorage.setItem('auth_token',token)
  }

}
