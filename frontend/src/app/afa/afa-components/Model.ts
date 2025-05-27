export interface CompanyType{
    company_id : number,
    company_name : string,
    email : string,
    phone_number : string,
    vat_percent : number,
    domain : string,
    address: string,
    city: string,
    state: string,
    country: string,
    pincode: number,
    company_logo: Blob;
    logoSrc?: string;
    customer_id: number;
    customer_name: string;
}

export interface PeopleType {
    people_id: number
    name: string;
    email: string;
    gender: string;
    date_of_birth: Date;
    job_type: string;
    accept: boolean;
    status: string;
    address: string;
    company_id: number;
    city: string,
    state: string,
    country: string,
    pincode: string,
    umbrella_company_name: number,
    company_name: string,
    phone_number: string,
    vat_percent: number,
    domain: string,
    ni_no: number,
}

export interface AssignmentsType{
    company_id: number,
    assignment_id: number,
    assignmentsUniqueNumber: string,
    start_date: Date,
    end_date: Date,
    role: string,
    location: string,
    status: string,
    type: string,
    name: string,
    customer_name: string,
    company_name: string,
    customer_id: number,
    people_id: number,
}

export interface Organization 
    {
    organization_id?: number;
    organization_name: string;
    email: string;
    address: string;
    state: string;
    pincode: number;
    organization_logo:Blob;
    status?: '1'| '0'; 
    }

export interface Customer
    {
      'company_id':number,
      'customer_id':number,
      'organization_id':number,
      'customer_name':string,
      'email':number,
      'tax_no':number,
      'address':string,
      'state':string,
      'pincode':number
    }

export interface User
    {
      'name':string,
      'email':string,
      'userName':string,
      'organization_id':number,
      'organization_name':string,
      'id':number
    }
    
export interface OrgType
    {
       
      'organization_id' : number,
      'organization_name' : string,
      'email': string,
      'address': string,
      'state':string,
      'status' : number,
      'organization_logo' : Blob,
      'pincode' : number
    }

export interface GanttTask {
  id: string | number;
  title: string;
  start: Date;
  end: Date;
  children?: GanttTask[];
  completionRatio?: number;
  company_id: number;
  name?: string;
  customer_name?: string;
  people_id?: number;
  company_name?: string;
  parentId?: number;
  expanded?: boolean;
  fromId?: number; 
  toId?: number;   
}

  
