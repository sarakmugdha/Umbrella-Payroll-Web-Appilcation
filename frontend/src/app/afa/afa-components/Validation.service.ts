import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ValidationService {

  constructor() { }

  private sidebarCollapsed = new BehaviorSubject<any>(null);
  isSidebarCollapsed = this.sidebarCollapsed.asObservable();

  changeSidebardata(data: any){
    this.sidebarCollapsed.next(data);
  }

  public restrictSymbols(event: any) {
    const regex = /[^a-zA-Z. ]/g;
    const input = event.target as HTMLInputElement;
    input.value = input.value.replace(regex, '');
  }

  public restrictToNumbers(event: any) {
    let regex = /[^0-9]/g;
    event.target.value = event.target.value.replace(regex, '');
  }

  public restrictEmailSymbols(event: any) {
    let regex = /[^a-zA-Z0-9@._-]/g;
    event.target.value = event.target.value.replace(regex, '');
  }

  public restrictVAT(event: any) {
    let regex = /[^0-9.0-9]/g;
    event.target.value = event.target.value.replace(regex, '');
  }

  public restrictAddressInput(event: any) {
    let regex = /[^a-zA-Z0-9\s,./#-]/g;
    event.target.value = event.target.value.replace(regex, '');
  }

  public restrictNINO(event: any){
    let regex = /[^a-zA-Z0-9\s]/g;
    event.target.value = event.target.value.replace(regex, '');
  }

}
