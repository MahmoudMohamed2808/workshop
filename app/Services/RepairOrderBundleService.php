<?php

namespace App\Services;

use App\Models\Problem;
use App\Models\RepairOrder;

class RepairOrderBundleService
{
    public function __construct(protected CostCalculatorService $costs) {}

    /**
     * Apply a problem bundle to the order: create the pivot row (if missing),
     * merge suggested accessories, suggest technician, add suggested parts.
     *
     * @return array{problem_added: bool, accessories_added: int, parts_added: int, technician_suggested: bool}
     */
    public function applyProblem(RepairOrder $order, Problem $problem, mixed $cost = null, ?string $notes = null): array
    {
        $result = [
            'problem_added' => false,
            'accessories_added' => 0,
            'parts_added' => 0,
            'technician_suggested' => false,
        ];

        $resolvedCost = $cost ?? $problem->default_cost_max ?? $problem->default_cost_min ?? 0;

        $existing = $order->orderProblems()->where('problem_id', $problem->id)->first();

        if ($existing === null) {
            $order->orderProblems()->create([
                'problem_id' => $problem->id,
                'cost' => $resolvedCost,
                'notes' => $notes,
            ]);
            $result['problem_added'] = true;
        }

        // Merge suggested accessories into the order accessories list.
        $suggested = $problem->suggested_accessories ?? [];

        if (is_array($suggested) && $suggested !== []) {
            $current = $order->accessories ?? [];

            if (is_string($current)) {
                $decoded = json_decode($current, true);
                $current = is_array($decoded) ? $decoded : [];
            }

            $currentItems = array_map(
                fn (mixed $item): string => is_array($item) ? (string) ($item['item'] ?? '') : (string) $item,
                is_array($current) ? $current : []
            );

            foreach ($suggested as $accessory) {
                $label = is_array($accessory) ? (string) ($accessory['item'] ?? '') : (string) $accessory;

                if ($label === '' || in_array($label, $currentItems, true)) {
                    continue;
                }

                $current[] = ['item' => $label];
                $currentItems[] = $label;
                $result['accessories_added']++;
            }

            $order->accessories = array_values($current);
        }

        // Suggest technician when the order has none yet.
        if ($order->technician_id === null && $problem->suggested_technician_id !== null) {
            $order->technician_id = $problem->suggested_technician_id;
            $result['technician_suggested'] = true;
        }

        // Add suggested parts (skip duplicates).
        $problem->loadMissing('suggestedParts');

        foreach ($problem->suggestedParts as $part) {
            $exists = $order->orderParts()->where('spare_part_id', $part->id)->exists();

            if ($exists) {
                continue;
            }

            $order->orderParts()->create([
                'spare_part_id' => $part->id,
                'quantity' => max(1, (int) ($part->pivot->default_quantity ?? 1)),
                'unit_price' => $part->selling_price,
            ]);
            $result['parts_added']++;
        }

        $order->save();

        $this->costs->recalculateOrderTotal($order->refresh());

        return $result;
    }
}
