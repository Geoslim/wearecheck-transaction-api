<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->{'id'},
            'type' => $this->{'type'},
            'amount' => $this->{'amount'},
            'status' => $this->{'status'},
            'reference' => $this->{'reference'},
            'created_at' => $this->{'created_at'},
            'updated_at' => $this->{'updated_at'}
        ];
    }
}
