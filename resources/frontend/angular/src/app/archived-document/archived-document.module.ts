import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ArchivedDocumentRoutingModule } from './archived-document-routing.module';
import { ArchivedDocumentListComponent } from './archived-document-list/archived-document-list.component';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { MatDialogModule } from '@angular/material/dialog';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatTableModule } from '@angular/material/table';
import { SharedModule } from '@shared/shared.module';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatPaginatorModule } from '@angular/material/paginator';
import { MatSelectModule } from '@angular/material/select';
import {
  OwlDateTimeModule,
  OwlNativeDateTimeModule,
} from 'ng-pick-datetime-ex';
import { MatSortModule } from '@angular/material/sort';
import { MatMenuModule } from '@angular/material/menu';
import { MatInputModule } from '@angular/material/input';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';

@NgModule({
  declarations: [ArchivedDocumentListComponent],
  imports: [
    CommonModule,
    ArchivedDocumentRoutingModule,
    SharedModule,
    FormsModule,
    ReactiveFormsModule,
    MatTableModule,
    MatProgressSpinnerModule,
    MatDialogModule,
    MatCheckboxModule,
    MatPaginatorModule,
    MatSelectModule,
    MatSortModule,
    MatMenuModule,
    MatInputModule,
    MatSlideToggleModule,
    MatPaginatorModule,
    MatIconModule,
    MatButtonModule,
    OwlDateTimeModule,
    OwlNativeDateTimeModule,
  ],
})
export class ArchivedDocumentModule {}
