<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [

            "id"=>$this->id,
            "action"=>$this->action,
            "user_name"=>$this->user->name,
            "user_email"=>$this->user->email,
            "action_time"=>$this->created_at,


        ];
    }
}
