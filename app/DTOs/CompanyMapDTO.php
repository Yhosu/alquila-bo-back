<?php

namespace App\DTOs;

use App\Models\Company;

class CompanyMapDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $lat,
        public string $lng,
        public ?string $address,
        public ?string $image,
        public ?string $website
    ) {}

    public static function fromModel(Company $company): self
    {
        return new self(
            id: $company->id,
            name: $company->name,
            lat: $company->lat,
            lng: $company->lng,
            address: $company->address,
            image: $company->image,
            website: $company->website
        );
    }

    public static function fromCollection($companies): array
    {
        return $companies->map(fn($c) => self::fromModel($c))->toArray();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'address' => $this->address,
            'image' => $this->image,
            'website' => $this->website,
        ];
    }
}
