<?php

namespace App\DataTables;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class BaseTable
{
    protected function tokoId(Request $request): int
    {
        return (int) $request->user()?->toko?->id;
    }

    protected function searchValue(Request $request): string
    {
        return trim((string) data_get($request->input(), 'search.value', ''));
    }

    protected function requestedColumnName(Request $request): ?string
    {
        $columnIndex = data_get($request->input(), 'order.0.column');

        if ($columnIndex === null) {
            return null;
        }

        return data_get($request->input(), "columns.{$columnIndex}.name");
    }

    protected function requestedDirection(Request $request, string $default = 'desc'): string
    {
        $direction = strtolower((string) data_get($request->input(), 'order.0.dir', $default));

        return $direction === 'asc' ? 'asc' : 'desc';
    }

    /**
     * @param  array<string, string|\Closure>  $orderMap
     */
    protected function applyOrdering(
        Builder $query,
        Request $request,
        array $orderMap,
        string $defaultColumn,
        string $defaultDirection = 'desc'
    ): void {
        $column = $this->requestedColumnName($request);
        $direction = $this->requestedDirection($request, $defaultDirection);
        $orderDefinition = $orderMap[$column] ?? $orderMap[$defaultColumn] ?? $defaultColumn;

        if ($orderDefinition instanceof \Closure) {
            $orderDefinition($query, $direction, $request);

            return;
        }

        $query->orderBy($orderDefinition, $direction);
    }

    protected function formatCurrency(int|float|null $amount): string
    {
        return 'Rp '.number_format((int) round($amount ?? 0), 0, ',', '.');
    }

    protected function formatDate(null|string|CarbonInterface $date, string $fallback = '-'): string
    {
        if (! $date) {
            return $fallback;
        }

        if ($date instanceof CarbonInterface) {
            return $date->translatedFormat('d M Y');
        }

        return now()->parse($date)->translatedFormat('d M Y');
    }

    protected function stackedText(string $title, ?string $subtitle = null): string
    {
        $subtitleHtml = $subtitle !== null && $subtitle !== ''
            ? '<div class="text-xs text-[var(--text-muted)]">'.e($subtitle).'</div>'
            : '';

        return '<div class="space-y-1">'
            .'<div class="font-medium text-[var(--text-strong)]">'.e($title).'</div>'
            .$subtitleHtml
            .'</div>';
    }

    protected function strongText(string $value): string
    {
        return '<span class="font-semibold text-[var(--text-strong)]">'.e($value).'</span>';
    }

    protected function badge(string $label, string $variant = 'default'): string
    {
        $class = match ($variant) {
            'success', 'paid' => 'nyuci-badge-success',
            'pending', 'unpaid' => 'nyuci-badge-pending',
            'danger' => 'nyuci-badge-danger',
            default => 'nyuci-badge-default',
        };

        return '<span class="nyuci-badge '.$class.'">'.e($label).'</span>';
    }

    protected function actionLink(
        string $url,
        string $label,
        string $variant = 'secondary',
        bool $newTab = false
    ): string {
        $class = match ($variant) {
            'primary' => 'nyuci-table-link-primary',
            'danger' => 'nyuci-table-link-danger',
            default => 'nyuci-table-link-secondary',
        };

        $attributes = $newTab ? ' target="_blank" rel="noopener"' : '';

        return '<a href="'.e($url).'" class="nyuci-action-item '.$class.'"'.$attributes.'>'
            .$this->actionIcon($label)
            .'<span class="nyuci-action-item-label">'.e($label).'</span>'
            .'</a>';
    }

    protected function actionPreview(string $url, string $label = 'Detail'): string
    {
        return '<button type="button" class="nyuci-action-item nyuci-table-link-secondary" data-detail-url="'.e($url).'">'
            .$this->actionIcon($label)
            .'<span class="nyuci-action-item-label">'.e($label).'</span>'
            .'</button>';
    }

    protected function actionForm(
        string $url,
        string $label,
        string $method = 'DELETE',
        string $variant = 'danger',
        ?string $confirmMessage = null
    ): string {
        $class = match ($variant) {
            'primary' => 'nyuci-table-link-primary',
            'danger' => 'nyuci-table-link-danger',
            default => 'nyuci-table-link-secondary',
        };

        $confirmAttribute = $confirmMessage
            ? ' onclick="return confirm('.e(json_encode($confirmMessage, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP)).')"'
            : '';

        return '<form method="POST" action="'.e($url).'" class="nyuci-action-form">'
            .csrf_field()
            .method_field($method)
            .'<button type="submit" class="nyuci-action-item '.$class.'"'.$confirmAttribute.'>'
            .$this->actionIcon($label)
            .'<span class="nyuci-action-item-label">'.e($label).'</span>'
            .'</button>'
            .'</form>';
    }

    /**
     * @param  array<int, string|null>  $actions
     */
    protected function actionGroup(array $actions): string
    {
        $items = implode('', array_filter($actions));

        return '<div class="nyuci-table-actions">'
            .'<details class="nyuci-action-menu">'
            .'<summary class="nyuci-action-menu-trigger" aria-label="Buka aksi">'
            .'<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">'
            .'<circle cx="6" cy="12" r="1.8" />'
            .'<circle cx="12" cy="12" r="1.8" />'
            .'<circle cx="18" cy="12" r="1.8" />'
            .'</svg>'
            .'</summary>'
            .'<div class="nyuci-action-menu-panel">'.$items.'</div>'
            .'</details>'
            .'</div>';
    }

    protected function actionIcon(string $label): string
    {
        $normalized = strtolower($label);

        if (str_contains($normalized, 'hapus')) {
            return $this->iconSvg(
                '<path d="M3 6h18" />'
                .'<path d="M8 6V4h8v2" />'
                .'<path d="M19 6l-1 13H6L5 6" />'
                .'<path d="M10 10v6" />'
                .'<path d="M14 10v6" />'
            );
        }

        if (str_contains($normalized, 'checkout')) {
            return $this->iconSvg(
                '<path d="M14 5h5v5" />'
                .'<path d="M10 14 19 5" />'
                .'<path d="M19 13v4a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h4" />'
            );
        }

        if (str_contains($normalized, 'lunas') || str_contains($normalized, 'selesaikan pembayaran')) {
            return $this->iconSvg(
                '<path d="M20 12a8 8 0 1 1-4-6.93" />'
                .'<path d="m9 12 2 2 5-5" />'
            );
        }

        if (str_contains($normalized, 'bayar sekarang') || str_contains($normalized, 'buat pembayaran')) {
            return $this->iconSvg(
                '<rect x="3" y="6" width="18" height="12" rx="2" />'
                .'<path d="M3 10h18" />'
                .'<path d="M7 15h4" />'
            );
        }

        if (str_contains($normalized, 'edit')) {
            return $this->iconSvg(
                '<path d="M12 20h9" />'
                .'<path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4Z" />'
            );
        }

        if (str_contains($normalized, 'detail')) {
            return $this->iconSvg(
                '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z" />'
                .'<circle cx="12" cy="12" r="2.5" />'
            );
        }

        return $this->iconSvg(
            '<path d="M12 5v14" />'
            .'<path d="M5 12h14" />'
        );
    }

    protected function iconSvg(string $paths): string
    {
        return '<svg class="nyuci-action-item-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
            .$paths
            .'</svg>';
    }
}
