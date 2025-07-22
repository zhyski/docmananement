import { Injectable } from '@angular/core';
import { MatDialogConfig, MatDialog } from '@angular/material/dialog';
import { Observable } from 'rxjs';
import { CommonDialogComponent } from './common-dialog.component';
import { TranslationService } from '@core/services/translation.service';
@Injectable()
export class CommonDialogService {
  dialogConfig: MatDialogConfig = {
    disableClose: false,
    maxWidth: '80vw',
    minWidth: '25vw',
    height: '',
    position: {
      top: '',
      bottom: '',
      left: '',
      right: '',
    },
  };
  constructor(
    public dialog: MatDialog,
    private translationService: TranslationService
  ) {
    this.translationService.lanDir$.subscribe(
      (direction) => (this.dialogConfig.direction = direction)
    );
  }

  deleteConformationDialog(
    primaryMessage: string,
    secondaryMessage?: string
  ): Observable<boolean> {
    const dialogRef = this.dialog.open(
      CommonDialogComponent,
      this.dialogConfig
    );
    dialogRef.componentInstance.primaryMessage = primaryMessage;
    dialogRef.componentInstance.secondaryMessage = secondaryMessage;
    return dialogRef.afterClosed();
  }
}
