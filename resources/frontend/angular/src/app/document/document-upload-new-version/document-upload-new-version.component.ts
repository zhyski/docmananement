import { HttpClient } from '@angular/common/http';
import { ChangeDetectorRef, Component, Inject, OnInit } from '@angular/core';
import {
  UntypedFormBuilder,
  UntypedFormGroup,
  Validators,
} from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { DocumentVersion } from '@core/domain-classes/documentVersion';
import { FileInfo } from '@core/domain-classes/file-info';
import { TranslationService } from '@core/services/translation.service';
import { environment } from '@environments/environment';
import { ToastrService } from 'ngx-toastr';
import { BaseComponent } from 'src/app/base.component';
import { DocumentService } from '../document.service';
import { DocumentInfo } from '@core/domain-classes/document-info';

@Component({
  selector: 'app-document-upload-new-version',
  templateUrl: './document-upload-new-version.component.html',
  styleUrls: ['./document-upload-new-version.component.scss'],
})
export class DocumentUploadNewVersionComponent
  extends BaseComponent
  implements OnInit
{
  documentForm: UntypedFormGroup;
  extension = '';
  isFileUpload = false;
  showProgress = false;
  progress = 0;
  fileInfo: FileInfo;
  fileData: any;
  constructor(
    private fb: UntypedFormBuilder,
    @Inject(MAT_DIALOG_DATA) public data: DocumentInfo,
    private httpClient: HttpClient,
    private cd: ChangeDetectorRef,
    private dialogRef: MatDialogRef<DocumentUploadNewVersionComponent>,
    private documentService: DocumentService,
    private toastrService: ToastrService,
    private translationService: TranslationService
  ) {
    super();
  }

  ngOnInit(): void {
    this.createDocumentForm();
  }

  createDocumentForm() {
    this.documentForm = this.fb.group({
      url: ['', [Validators.required]],
      extension: [''],
    });
  }

  upload(files) {
    if (files.length === 0) return;
    this.extension = files[0].name.split('.').pop();
    this.cd.markForCheck();
    this.fileData = files[0];
    this.fileUploadValidation(files[0].name);
    this.isFileUpload = true;
  }

  fileUploadValidation(fileName: string) {
    this.documentForm.patchValue({
      url: fileName,
    });
    this.documentForm.get('url').markAsTouched();
    this.documentForm.updateValueAndValidity();
  }

  SaveDocument() {
    if (this.documentForm.invalid) {
      this.documentForm.markAllAsTouched();
      return;
    }

    const documentversion: DocumentVersion = {
      documentId: this.data.id,
      url: this.fileData.fileName,
      fileData: this.fileData,
      extension: this.extension,
      location: this.data.location,
    };
    this.sub$.sink = this.documentService
      .saveNewVersionDocument(documentversion)
      .subscribe(() => {
        this.toastrService.success(
          this.translationService.getValue('DOCUMENT_SAVE_SUCCESSFULLY')
        );
        this.dialogRef.close(true);
      });
  }

  closeDialog() {
    this.dialogRef.close(false);
  }
}
