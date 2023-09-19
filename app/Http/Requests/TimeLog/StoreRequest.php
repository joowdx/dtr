<?php

namespace App\Http\Requests\TimeLog;

use App\Contracts\Import;
use App\Models\Timelog;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function __construct(
        private Import $import
    ) {
    }

    public function messages()
    {
        return [
            'scanner.required' => 'Please select which scanner to import device logs.',
            'file.required' => 'Please choose a file.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->has('file')) {
            return [
                'scanner' => 'required|exists:scanners,id',
                'file' => [
                    'required',
                    'file',
                    'mimes:csv,txt',
                ],
            ];
        }

        return [
            'scanner_id' => 'required|exists:scanners,id',
            'uid' => 'required|alpha_num',
            'time' => 'required|date',
            'state' => ['required', Rule::in(array_merge(Timelog::IN, Timelog::OUT))],
        ];
    }

    // protected function failedValidation(Validator $validator): never
    // {
    //     throw new HttpResponseException(
    //         response()->json([
    //             'errors' => $validator->errors(),
    //         ], 422)
    //     );
    // }
}
