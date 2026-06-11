<?php

namespace App\Services\CDS;

use App\Models\MedicalService;

/**
 * Maps a medical service's result template to its constrained list of selectable
 * parameters (config/cds_result_parameters.php), and normalizes submitted lab
 * results into a flat key => value map keyed by those parameter keys.
 */
class ResultParameterCatalog
{
    /**
     * Parameter definitions ({key, label, unit}) for the given medical service's
     * result template, with unit filled in from the service when not fixed by the template.
     */
    public function forMedicalService(int $medicalServiceId): array
    {
        $service = MedicalService::with('resultTemplate')->find($medicalServiceId);

        if (!$service || !$service->resultTemplate) {
            return [];
        }

        return array_map(function (array $definition) use ($service) {
            if ($definition['unit'] === null) {
                $definition['unit'] = $service->unit;
            }
            return $definition;
        }, $this->definitionsForTemplateCode($service->resultTemplate->code));
    }

    /**
     * Raw parameter definitions for a result template code, before unit fallback.
     */
    public function definitionsForTemplateCode(string $templateCode): array
    {
        return config("cds_result_parameters.{$templateCode}", []);
    }

    /**
     * Normalize submitted template data into a flat key => value map using the
     * parameter keys defined for the given template code.
     *
     * - Row-based templates (full_blood_picture, urinalysis, single_numeric_lab) submit
     *   parameters[i][parameter_name] / parameters[i][value]; matched by label.
     * - Direct-field templates (vital_observations) submit one input per parameter key.
     */
    public function normalize(string $templateCode, array $templateData, ?MedicalService $service = null): array
    {
        $definitions = $this->definitionsForTemplateCode($templateCode);
        $normalized = [];

        if ($templateCode === 'single_numeric_lab') {
            $value = data_get($templateData, 'parameters.0.value');
            if ($value !== null && $value !== '') {
                $normalized['value'] = is_numeric($value) ? (float) $value : $value;
            }
            return $normalized;
        }

        if (!empty($templateData['parameters']) && is_array($templateData['parameters'])) {
            $labelToKey = [];
            foreach ($definitions as $definition) {
                $labelToKey[strtolower(trim($definition['label']))] = $definition['key'];
            }

            foreach ($templateData['parameters'] as $row) {
                $label = strtolower(trim($row['parameter_name'] ?? ''));
                $value = $row['value'] ?? null;

                if ($label === '' || $value === null || $value === '' || !isset($labelToKey[$label])) {
                    continue;
                }

                $key = $labelToKey[$label];
                $normalized[$key] = is_numeric($value) ? (float) $value : $value;
            }

            return $normalized;
        }

        // Direct-field templates (e.g. vital_observations): match catalog keys to top-level fields
        foreach ($definitions as $definition) {
            $key = $definition['key'];
            if (!array_key_exists($key, $templateData) || $templateData[$key] === '' || $templateData[$key] === null) {
                continue;
            }

            $value = $templateData[$key];
            $normalized[$key] = is_numeric($value) ? (float) $value : $value;
        }

        return $normalized;
    }
}
