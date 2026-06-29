<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'profile_picture' => $this->profile_picture
                ? route('profile-picture.view', ['path' => basename($this->profile_picture)])
                : null,

            'full_name' => trim(
                collect([
                    $this->first_name,
                    $this->middle_name,
                    $this->last_name,
                    $this->suffix,
                ])->filter()->implode(' ')
            ),

            'first_name' => $this->first_name,

            'middle_name' => $this->middle_name,

            'last_name' => $this->last_name,

            'suffix' => $this->suffix,

            'birthday' => $this->birthday,

            'mobile_number' => $this->mobile_number,

            'gender' => $this->gender,

            'username' => $this->username,

            'email' => $this->email,

            'role' => $this->role,

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),

            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
