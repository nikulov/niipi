<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ModelDuplicator
{
    private const TITLE_WORD = 'копия';

    private const SLUG_WORD = 'copy';

    public function duplicate(Model $model): Model
    {
        return DB::transaction(function () use ($model) {
            $titleCol = $model->duplicateTitleColumn();
            $slugCol = $model->duplicateSlugColumn();

            [$baseTitle] = $this->parseTitleSuffix((string) $model->{$titleCol});
            $baseSlug = $slugCol !== null
                ? $this->parseSlugSuffix((string) $model->{$slugCol})[0]
                : null;

            $nextN = $this->nextCopyNumber($model, $titleCol, $baseTitle, $slugCol, $baseSlug);

            $except = $slugCol !== null ? [$slugCol] : [];
            $except = array_merge($except, $this->nonTableAttributes($model));
            $copy = $model->replicate($except);

            $copy->{$titleCol} = $this->makeTitle($baseTitle, $nextN);
            if ($slugCol !== null) {
                $copy->{$slugCol} = $this->makeSlug($baseSlug, $nextN);
            }

            $model->prepareDuplicate($copy);
            $copy->save();

            $model->copyRelationsTo($copy);

            return $copy;
        });
    }

    private function makeTitle(string $base, int $n): string
    {
        return $n === 1
            ? $base.' ('.self::TITLE_WORD.')'
            : $base.' ('.self::TITLE_WORD.' '.$n.')';
    }

    private function makeSlug(string $base, int $n): string
    {
        return $n === 1
            ? $base.'-'.self::SLUG_WORD
            : $base.'-'.self::SLUG_WORD.'-'.$n;
    }

    /**
     * @return array{0: string, 1: int}
     */
    private function parseTitleSuffix(string $title): array
    {
        $word = preg_quote(self::TITLE_WORD, '/');
        if (preg_match('/^(.+?)\s*\('.$word.'(?:\s+(\d+))?\)$/u', $title, $m)) {
            return [$m[1], isset($m[2]) ? (int) $m[2] : 1];
        }

        return [$title, 0];
    }

    /**
     * @return array{0: string, 1: int}
     */
    private function parseSlugSuffix(string $slug): array
    {
        $word = preg_quote(self::SLUG_WORD, '/');
        if (preg_match('/^(.+?)-'.$word.'(?:-(\d+))?$/', $slug, $m)) {
            return [$m[1], isset($m[2]) ? (int) $m[2] : 1];
        }

        return [$slug, 0];
    }

    /**
     * @return array<int, string>
     */
    private function nonTableAttributes(Model $model): array
    {
        $columns = $model->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing($model->getTable());

        return array_values(array_diff(
            array_keys($model->getAttributes()),
            $columns,
        ));
    }

    private function nextCopyNumber(
        Model $model,
        string $titleCol,
        string $baseTitle,
        ?string $slugCol,
        ?string $baseSlug,
    ): int {
        $titleSingle = $baseTitle.' ('.self::TITLE_WORD.')';
        $titleMulti = $baseTitle.' ('.self::TITLE_WORD.' %)';

        $titleMax = $model::query()
            ->where(function ($q) use ($titleCol, $titleSingle, $titleMulti) {
                $q->where($titleCol, $titleSingle)
                    ->orWhere($titleCol, 'like', $titleMulti);
            })
            ->pluck($titleCol)
            ->map(fn (string $t) => $this->parseTitleSuffix($t)[1])
            ->max() ?? 0;

        $slugMax = 0;
        if ($slugCol !== null && $baseSlug !== null) {
            $slugSingle = $baseSlug.'-'.self::SLUG_WORD;
            $slugMulti = $baseSlug.'-'.self::SLUG_WORD.'-%';

            $slugMax = $model::query()
                ->where(function ($q) use ($slugCol, $slugSingle, $slugMulti) {
                    $q->where($slugCol, $slugSingle)
                        ->orWhere($slugCol, 'like', $slugMulti);
                })
                ->pluck($slugCol)
                ->map(fn (string $s) => $this->parseSlugSuffix($s)[1])
                ->max() ?? 0;
        }

        return max($titleMax, $slugMax) + 1;
    }
}
