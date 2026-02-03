<?php

namespace Tests\Unit\Services\Forms;

use App\Models\Form;
use App\Models\FormField;
use App\Services\Forms\SubmissionDataNormalizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionDataNormalizerTest extends TestCase
{
    use RefreshDatabase;

    public function test_normalize_filters_and_casts_values(): void
    {
        $form = Form::create([
            'name' => 'contact',
            'title' => 'Contact',
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'text',
            'name' => 'name',
            'label' => 'Name',
            'is_enabled' => true,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'checkbox',
            'name' => 'agree',
            'label' => 'Agree',
            'is_enabled' => true,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'file',
            'name' => 'resume',
            'label' => 'Resume',
            'is_enabled' => true,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'type' => 'text',
            'name' => 'hidden',
            'label' => 'Hidden',
            'is_enabled' => false,
        ]);

        $normalizer = new SubmissionDataNormalizer();

        $validated = [
            'data' => [
                'name' => 'Alice',
                'agree' => '1',
                'resume' => 'should_be_ignored',
                'hidden' => 'secret',
                'empty' => '   ',
            ],
        ];

        $normalized = $normalizer->normalize($form, $validated);

        ksort($normalized);

        $this->assertSame([
            'agree' => true,
            'name' => 'Alice',
        ], $normalized);
    }
}
