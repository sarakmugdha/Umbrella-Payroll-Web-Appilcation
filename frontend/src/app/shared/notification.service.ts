import { Injectable } from '@angular/core';
import { NotificationService } from '@progress/kendo-angular-notification';

@Injectable({
  providedIn: 'root'
})
export class NotificationServiceWrapper {

  constructor(private notificationService:NotificationService) { }

  showSuccess(message:string){
    this.notificationService.show({
      content:message,
      hideAfter:2300, 
      position:{
        horizontal:"right",vertical:"top"
      },
      animation:{type:"fade",duration:400},
      type:{style:"success",icon:true}
    })
 }

 
 showError(message:string){
  this.notificationService.show({
    content:message,
    hideAfter:2300, 
    position:{
      horizontal:"right",vertical:"top"
    },
    animation:{type:"fade",duration:400},
    type:{style:"error",icon:true}
  })
}

}
