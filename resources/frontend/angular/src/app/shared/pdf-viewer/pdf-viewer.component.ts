import { HttpEventType, HttpResponse } from '@angular/common/http';
import {
  Component,
  Input,
  OnChanges,
  SimpleChanges,
} from '@angular/core';
import { DocumentView } from '@core/domain-classes/document-view';
import { CommonService } from '@core/services/common.service';
import { BaseComponent } from 'src/app/base.component';

@Component({
  selector: 'app-pdf-viewer',
  templateUrl: './pdf-viewer.component.html',
  styleUrls: ['./pdf-viewer.component.scss'],
})
export class PdfViewerComponent extends BaseComponent implements OnChanges {
  @Input() document: DocumentView;
  loadingTime = 2000;
  constructor(private commonService: CommonService) {
    super();
  }
  
  documentUrl: Blob = null;
  isLoading = false;

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['document']) {
      this.getDocument();
    }
  }

  getDocument() {
    this.isLoading = true;
    this.sub$.sink = this.commonService
      .downloadDocument(this.document.documentId, this.document.isVersion)
      .subscribe(
        (event) => {
          if (event.type === HttpEventType.Response) {
            this.isLoading = false;
            this.downloadFile(event);
          }
        },
        () => {
          this.isLoading = false;
        }
      );
  }

  downloadFile(data: HttpResponse<Blob>) {
    this.documentUrl = new Blob([data.body], { type: data.body.type });
  }
}
