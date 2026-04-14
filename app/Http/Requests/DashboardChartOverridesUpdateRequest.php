<?php

namespace App\Http\Requests;

use App\Services\DashboardCharts\DashboardChartConfigResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DashboardChartOverridesUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'overrides' => ['required', 'array'],
        ];

        foreach (DashboardChartConfigResolver::slotKeys() as $slotKey) {
            $allowedChartTypes = array_keys(DashboardChartConfigResolver::chartTypeOptions($slotKey));

            $rules["overrides.$slotKey"] = ['required', 'array'];
            $rules["overrides.$slotKey.use_custom"] = ['nullable', 'boolean'];
            $rules["overrides.$slotKey.title"] = ['nullable', 'string', 'max:120'];
            $rules["overrides.$slotKey.subtitle"] = ['nullable', 'string', 'max:160'];
            $rules["overrides.$slotKey.chart_type"] = ['nullable', Rule::in($allowedChartTypes)];
            $rules["overrides.$slotKey.period_granularity"] = ['nullable', Rule::in(['day', 'month'])];
            $rules["overrides.$slotKey.period_length"] = ['nullable', 'integer', Rule::in([7, 14, 30, 90, 6, 12])];
            $rules["overrides.$slotKey.primary_metric"] = ['nullable', Rule::in(array_keys(DashboardChartConfigResolver::metricOptions()))];
            $rules["overrides.$slotKey.secondary_metric"] = ['nullable', Rule::in(array_keys(DashboardChartConfigResolver::metricOptions()))];
            $rules["overrides.$slotKey.accent_color"] = ['nullable', 'string', 'max:32'];
            $rules["overrides.$slotKey.show_points"] = ['nullable', 'boolean'];
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            foreach (DashboardChartConfigResolver::slotKeys() as $slotKey) {
                $overrides = (array) $this->input('overrides', []);
                $slot = (array) ($overrides[$slotKey] ?? []);
                $primaryMetric = (string) ($slot['primary_metric'] ?? '');
                $secondaryMetric = (string) ($slot['secondary_metric'] ?? '');
                $granularity = (string) ($slot['period_granularity'] ?? '');
                $length = (int) ($slot['period_length'] ?? 0);

                if (($slot['use_custom'] ?? false) && $secondaryMetric !== '' && $secondaryMetric === $primaryMetric) {
                    $validator->errors()->add("overrides.$slotKey.secondary_metric", 'Metrik sekunder harus berbeda dari metrik utama.');
                }

                if (($slot['use_custom'] ?? false) && ! in_array($length, $this->allowedLengths($granularity), true)) {
                    $validator->errors()->add("overrides.$slotKey.period_length", 'Kombinasi periode tidak didukung.');
                }
            }
        });
    }

    private function allowedLengths(string $granularity): array
    {
        return match ($granularity) {
            'day' => [7, 14, 30, 90],
            'month' => [6, 12],
            default => [],
        };
    }
}
