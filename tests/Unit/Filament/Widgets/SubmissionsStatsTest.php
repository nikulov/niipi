<?php

namespace Tests\Unit\Filament\Widgets;

use App\Enums\FormSubmissionStatus;
use App\Filament\Widgets\SubmissionsStats;
use App\Models\Form;
use App\Models\FormSubmission;
use Filament\Schemas\Components\Section;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionsStatsDouble extends SubmissionsStats
{
    public function publicGetStats(): array
    {
        return $this->getStats();
    }
}

class SubmissionsStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_stats_counts_reflect_current_submissions(): void
    {
        $form = Form::create(['name' => 'contact', 'title' => 'Contact']);

        FormSubmission::create(['form_id' => $form->id, 'status' => FormSubmissionStatus::New->value, 'data' => []]);
        FormSubmission::create(['form_id' => $form->id, 'status' => FormSubmissionStatus::New->value, 'data' => []]);

        FormSubmission::create(['form_id' => $form->id, 'status' => FormSubmissionStatus::Processing->value, 'data' => []]);

        FormSubmission::create(['form_id' => $form->id, 'status' => FormSubmissionStatus::Failed->value, 'data' => []]);
        FormSubmission::create(['form_id' => $form->id, 'status' => FormSubmissionStatus::Failed->value, 'data' => []]);
        FormSubmission::create(['form_id' => $form->id, 'status' => FormSubmissionStatus::Failed->value, 'data' => []]);

        [$newToday, $processing, $failed] = $this->extractStats();

        $this->assertSame(6, $newToday->getValue(), 'new_today counts all submissions created today');
        $this->assertSame(1, $processing->getValue());
        $this->assertSame(3, $failed->getValue());
    }

    public function test_stats_are_zero_when_no_submissions(): void
    {
        [$newToday, $processing, $failed] = $this->extractStats();

        $this->assertSame(0, $newToday->getValue());
        $this->assertSame(0, $processing->getValue());
        $this->assertSame(0, $failed->getValue());
    }

    /**
     * @return array{0: Stat, 1: Stat, 2: Stat}
     */
    private function extractStats(): array
    {
        $widget = new SubmissionsStatsDouble();
        $stats = $widget->publicGetStats();

        $this->assertCount(1, $stats);
        $section = $stats[0];
        $this->assertInstanceOf(Section::class, $section);

        $children = $section->getDefaultChildComponents();
        $this->assertIsArray($children);
        $this->assertCount(3, $children);

        return [$children[0], $children[1], $children[2]];
    }
}
