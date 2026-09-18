<?php

namespace App\Services;

use App\Models\DocumentRequirementTemplate;
use App\Models\Vendor;
use App\Support\UiLabels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class DocumentRequirementService
{
    public function audienceFor(Vendor $vendor): string
    {
        if ($vendor->isMunicipalityOwner()) {
            return 'municipality';
        }
        if ($vendor->isOutdoorAgency()) {
            return 'outdoor_agency';
        }
        if ($vendor->isOutdoorOwner() || $vendor->prefersOutdoorPanel()) {
            return 'outdoor_owner';
        }
        if ($vendor->hasFreelancerTrack() && ! $vendor->hasPhysicalTrack()) {
            return 'freelancer';
        }

        return 'physical';
    }

    /**
     * @param  list<string>  $tracks
     */
    public function audienceFromRegistration(array $tracks, ?string $outdoorRole, ?string $ownerKind): string
    {
        $isOutdoor = in_array('outdoor', $tracks, true);
        if ($isOutdoor && $outdoorRole === Vendor::OUTDOOR_ROLE_OWNER && $ownerKind === Vendor::OWNER_KIND_MUNICIPALITY) {
            return 'municipality';
        }
        if ($isOutdoor && $outdoorRole === Vendor::OUTDOOR_ROLE_AGENCY) {
            return 'outdoor_agency';
        }
        if ($isOutdoor) {
            return 'outdoor_owner';
        }
        if (in_array('freelancer', $tracks, true)
            && ! in_array('physical_products', $tracks, true)
            && ! in_array('physical_quote', $tracks, true)) {
            return 'freelancer';
        }

        return 'physical';
    }

    /**
     * @param  array<string, mixed>  $rules
     * @param  list<string>  $tracks
     * @return array<string, mixed>
     */
    public function applyRegistrationFileRules(array $rules, array $tracks, ?string $outdoorRole, ?string $ownerKind): array
    {
        $templates = $this->templatesForAudience($this->audienceFromRegistration($tracks, $outdoorRole, $ownerKind));
        $freelancerTypes = ['diploma', 'certificate', 'portfolio_accreditation', 'course'];
        foreach ($templates as $tpl) {
            if (! $tpl->required || ! $tpl->requires_file) {
                continue;
            }
            if (in_array($tpl->document_type, $freelancerTypes, true)) {
                $rules['freelancer_docs'] = ['required', 'array', 'min:1'];

                continue;
            }
            $rules[$tpl->document_type] = ['required', 'file', 'max:12288', 'mimes:pdf,jpg,jpeg,png,webp'];
        }

        return $rules;
    }

    /**
     * @return list<string>
     */
    public function allowedTypesFor(Vendor $vendor): array
    {
        $base = [
            'tax_plate', 'company_registration', 'certificate', 'diploma',
            'portfolio_accreditation', 'course', 'outdoor_permit',
            'municipality_authority', 'trade_registry', 'other',
        ];

        return array_values(array_unique(array_merge(
            $base,
            $this->templatesFor($vendor)->pluck('document_type')->all()
        )));
    }

    /**
     * @return Collection<int, DocumentRequirementTemplate>
     */
    public function templatesFor(Vendor $vendor): Collection
    {
        return $this->templatesForAudience($this->audienceFor($vendor));
    }

    /**
     * @return Collection<int, DocumentRequirementTemplate>
     */
    public function templatesForAudience(string $audience): Collection
    {
        if (! Schema::hasTable('document_requirement_templates')) {
            return collect();
        }

        return DocumentRequirementTemplate::query()
            ->where('audience', $audience)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @return array{audience: string, missing: list<array{type: string, label: string}>, uploaded: int, required: int, cta: string, complete: bool}
     */
    public function statusFor(Vendor $vendor): array
    {
        $audience = $this->audienceFor($vendor);
        $templates = $this->templatesForAudience($audience);
        $required = $templates->where('required', true);
        $uploadedTypes = $vendor->documents()->pluck('document_type')->unique()->all();
        $missing = [];
        foreach ($required as $row) {
            if (! in_array($row->document_type, $uploadedTypes, true)) {
                $missing[] = [
                    'type' => $row->document_type,
                    'label' => $row->label ?: UiLabels::documentType($row->document_type),
                ];
            }
        }
        $cta = 'none';
        if ($required->isNotEmpty() && $vendor->documents()->count() === 0) {
            $cta = 'start';
        } elseif ($missing !== []) {
            $cta = 'complete';
        }

        return [
            'audience' => $audience,
            'missing' => $missing,
            'uploaded' => count($uploadedTypes),
            'required' => $required->count(),
            'cta' => $cta,
            'complete' => $missing === [],
        ];
    }
}
