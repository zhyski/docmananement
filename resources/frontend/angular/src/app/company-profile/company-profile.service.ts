import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { CompanyProfile, S3Config } from '@core/domain-classes/company-profile';
import { CommonError } from '@core/error-handler/common-error';
import { CommonHttpErrorService } from '@core/error-handler/common-http-error.service';
import { Observable } from 'rxjs';
import { catchError } from 'rxjs/operators';

@Injectable({
  providedIn: 'root',
})
export class CompanyProfileService {
  constructor(
    private http: HttpClient,
    private commonHttpErrorService: CommonHttpErrorService
  ) {}

  getCompanyProfile(): Observable<CompanyProfile | CommonError> {
    const url = `companyProfile`;
    return this.http
      .get<CompanyProfile>(url)
      .pipe(catchError(this.commonHttpErrorService.handleError));
  }

  updateCompanyProfile(
    companyProfile
  ): Observable<CompanyProfile | CommonError> {
    const url = `companyProfile`;
    return this.http
      .post<CompanyProfile>(url, companyProfile)
      .pipe(catchError(this.commonHttpErrorService.handleError));
  }

  updateLocalStorage(
    companyProfile: S3Config
  ): Observable<CompanyProfile | CommonError> {
    const url = `storage`;
    return this.http
      .post<CompanyProfile>(url, companyProfile)
      .pipe(catchError(this.commonHttpErrorService.handleError));
  }

  getS3Config(): Observable<S3Config | CommonError> {
    const url = `storage`;
    return this.http
      .get<S3Config>(url)
      .pipe(catchError(this.commonHttpErrorService.handleError));
  }
}
