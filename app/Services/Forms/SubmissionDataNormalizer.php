<?php

namespace App\Services\Forms;

use App\Models\Form;

final class SubmissionDataNormalizer
{
    public function normalize(Form $form, array $validated): array
    {
        $data = $validated['data'] ?? [];

        $out = [];

        foreach ($form->fields as $field) {
            if (! $field->is_enabled) {
                continue;
            }

            if ($field->type === 'file') {
                continue;
            }

            $name = $field->name;
            $value = $data[$name] ?? null;

            if ($field->type === 'checkbox') {
                $out[$name] = (bool) $value;

                continue;
            }

            if ($value === null) {
                continue;
            }

            if (is_string($value) && trim($value) === '') {
                continue;
            }

            // the mask is for humans; storage keeps the number as +7XXXXXXXXXX
            if ($field->type === 'phone') {
                $digits = preg_replace('/\D/', '', (string) $value);
                $out[$name] = '+7'.substr($digits, 1);

                continue;
            }

            $out[$name] = $value;
        }

        return $out;
    }
}
