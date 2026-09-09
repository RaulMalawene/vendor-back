<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function show(Request $request): CompanyResource
    {
        return new CompanyResource($request->user()->company);
    }

    public function update(UpdateCompanyRequest $request): CompanyResource
    {
        $company = $request->user()->company;
        $company->update($request->validated());

        return new CompanyResource($company->fresh());
    }
}