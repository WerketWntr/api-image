<?php

namespace App\Http\Requests;

use GuzzleHttp\Psr7\UploadedFile;
use Illuminate\Foundation\Http\FormRequest;

class ResizeImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules =  [
            'images' => 'required',
//            'w' => ['required', 'regex://^\d+(\.\d+)?%?$/'],
//            'h' => ['regex://^\d+(\.\d+)?%?$/'],
            'album_id' => 'exists:\App\Models\Album,id'
        ];

        $image = $this->all()['images'] ?? false;
        if ($image && $image instanceof UploadedFile) {
            $rules['images'][] = 'images';
        } else {
            $rules['images'] = 'url';
        }

        return $rules;
    }
}
