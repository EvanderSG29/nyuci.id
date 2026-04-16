<?php

namespace App\Http\Requests;

use App\Services\DashboardCharts\DashboardChartConfigResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DashboardChartDefaultsUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'charts' => ['required', 'array'],
        ];

        foreach (DashboardChartConfigResolver::slotKeys() as $slotKey) {
            $allowedChartTypes = array_keys(DashboardChartConfigResolver::chartTypeOptions($slotKey));

            $rules["charts.$slotKey"] = ['required', 'array'];
            $rules["charts.$slotKey.title"] = ['required', 'string', 'max:120'];
            $rules["charts.$slotKey.subtitle"] = ['nullable', 'string', 'max:160'];
            $rules["charts.$slotKey.chart_type"] = ['required', Rule::in($allowedChartTypes)];
            $rules["charts.$slotKey.period_granularity"] = ['required', Rule::in(['day', 'month'])];
            $rules["charts.$slotKey.period_length"] = ['required', 'integer', Rule::in([7, 14, 30, 90, 6, 12])];
            $rules["charts.$slotKey.primary_metric"] = ['required', Rule::in(array_keys(DashboardChartConfigResolver::metricOptions()))];
            $rules["charts.$slotKey.secondary_metric"] = ['nullable', Rule::in(array_keys(DashboardChartConfigResolver::metricOptions()))];
            $rules["charts.$slotKey.accent_color"] = ['nullable', 'string', 'max:32'];
            $rules["charts.$slotKey.show_points"] = ['nullable', 'boolean'];
            $rules["charts.$slotKey.show_previous_comparison"] = ['nullable', 'boolean'];
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            foreach (DashboardChartConfigResolver::slotKeys() as $slotKey) {
                $charts = (array) $this->input('charts', []);
                $slot = (array) ($charts[$slotKey] ?? []);
                $primaryMetric = (string) ($slot['primary_metric'] ?? '');
                $secondaryMetric = (string) ($slot['secondary_metric'] ?? '');
                $granularity = (string) ($slot['period_granularity'] ?? '');
                $length = (int) ($slot['period_length'] ?? 0);

                if ($secondaryMetric !== '' && $secondaryMetric === $primaryMetric) {
                    $validator->errors()->add("charts.$slotKey.secondary_metric", 'Metrik sekunder harus berbeda dari metrik utama.');
                }

                if (! in_array($length, $this->allowedLengths($granularity), true)) {
                    $validator->errors()->add("charts.$slotKey.period_length", 'Kombinasi periode tidak didukung.');
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
