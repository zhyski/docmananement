<?php

namespace App\Repositories\Contracts;

use App\Repositories\Contracts\BaseRepositoryInterface;

interface CompanyProfileRepositoryInterface extends BaseRepositoryInterface
{
    public function getCompanyProfile();
    public function updateCompanyProfile($attribute);
    public function updateStorage($attribute);
    public function getStorage();
}
