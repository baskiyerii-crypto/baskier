<?php

namespace App\Http\Requests\Api\V1;

use App\Models\DesignApproval;
use Illuminate\Foundation\Http\FormRequest;

class DesignApprovalRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $designApproval = $this->route('designApproval');

        return $designApproval instanceof DesignApproval
            && $this->user()?->can('designRespond', $designApproval->order);
    }

    public function rules(): array
    {
        return [
            'customer_feedback' => ['required', 'string', 'max:2000'],
        ];
    }
}
