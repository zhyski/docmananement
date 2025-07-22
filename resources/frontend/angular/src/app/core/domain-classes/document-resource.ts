import { ResourceParameter } from './resource-parameter';

export class DocumentResource extends ResourceParameter {
  id?: string = '';
  createdBy?: string = '';
  categoryId?: string = '';
  location?: string = '';
  createDate?: string;
  deletedDate?: string;
  deletedBy?: string= '';
}
