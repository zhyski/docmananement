import { LanguageFlag } from './language-flag';

export class CompanyProfile {
  id?: string;
  title: string;
  logoUrl?: string;
  bannerUrl?: string;
  imageData?: string;
  location = 'local';
  isS3Supported = false;
  languages?: LanguageFlag[];
}

export class S3Config {
  location: string;
  amazonS3key: string;
  amazonS3secret: string;
  amazonS3region: string;
  amazonS3bucket: string;
}
