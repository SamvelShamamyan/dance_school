<?php

namespace App\Services;

use App\Models\StudentGroupChangeHistory;
use App\Models\Group;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StudentGroupHistoryService
{
    public function getHistoryData(Request $request, int $studentId): array
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim((string) $request->input('search.value', ''));

        $base = StudentGroupChangeHistory::where('student_id', $studentId);

        $recordsTotal    = (clone $base)->count();

        $rows = (clone $base)
            ->orderByDesc('id')
            ->skip($start)
            ->take($length)
            ->get();

        $groupIds = [];
        foreach ($rows as $r) {
            $oldId = data_get($r->data, 'old_data.group_id');
            $newId = data_get($r->data, 'new_data.group_id');
            if ($oldId) $groupIds[] = (int) $oldId;
            if ($newId) $groupIds[] = (int) $newId;
        }
        $names = Group::whereIn('id', array_unique($groupIds))
            ->pluck('name','id');

        $mapped = $rows->map(function ($r) use ($names) {
            $oldId   = data_get($r->data, 'old_data.group_id');
            $newId   = data_get($r->data, 'new_data.group_id');
            $oldDate = data_get($r->data, 'old_data.group_date');
            $newDate = data_get($r->data, 'new_data.group_date');

            $fromName = $oldId ? ($names[$oldId] ?? ("ID {$oldId}")) : '—';
            $toName   = $newId ? ($names[$newId] ?? ("ID {$newId}")) : '—';

            Carbon::setLocale('hy');
            $period = '—';
            if ($oldDate && $newDate) {
                $d1 = Carbon::parse($oldDate);
                $d2 = Carbon::parse($newDate);
                $period = $d1->diffForHumans($d2, [
                    'parts' => 3,       
                    'join'  => true,    
                    'short' => true,   
                    'syntax' => Carbon::DIFF_ABSOLUTE,
                ]);
            }

            $transition = "{$fromName} → {$toName}";
            $effective  = $newDate ? Carbon::parse($newDate)->format('Y-m-d') : '—';
            $changedAt  = optional($r->created_at)->format('Y-m-d H:i');

            return [
                'id'             => $r->id,
                'transition'     => $transition,
                'period'         => $period,
                // 'effective_date' => $effective,
                // 'changed_at'     => $changedAt,
                'is_last'        => $r->is_last ? 'Այո' : 'Ոչ',
            ];
        });

        if ($search !== '') {
            $mapped = $mapped->filter(function ($row) use ($search) {
                return mb_stripos($row['transition'], $search) !== false;
            })->values();
        }

        $recordsFiltered = $search === '' ? $recordsTotal : (clone $base)->count();

        return [
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $mapped->values()->toArray(),
        ];
    }
}



