<?php

namespace App\Services;

use App\Models\OtherOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OtherOffersService
{
    public function getOtherOfferData(Request $request)
    {
        $draw   = (int) $request->input('draw');
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value');

        $totalStudentsSub = DB::table('other_offer_groups as oog')
            ->join('students as s', 's.group_id', '=', 'oog.group_id')
            ->selectRaw('oog.other_offer_id, COUNT(DISTINCT s.id) as total_students')
            ->groupBy('oog.other_offer_id');

        /**
         * paid_students: paid_status = 1
         */
        $paidStudentsSub = DB::table('other_offer_groups as oog')
            ->join('other_offer_paids as oop', 'oop.other_offer_group_id', '=', 'oog.id')
            ->where('oop.paid_status', 1)
            ->selectRaw('oog.other_offer_id, COUNT(DISTINCT oop.student_id) as paid_students')
            ->groupBy('oog.other_offer_id');

        $groupsCountSub = DB::table('other_offer_groups as oog')
            ->selectRaw('oog.other_offer_id, COUNT(DISTINCT oog.group_id) as groups_count')
            ->groupBy('oog.other_offer_id');

        // base query
        $query = OtherOffer::query()
            ->leftJoin('school_names as sn', 'other_offers.school_id', '=', 'sn.id')
            ->leftJoinSub($totalStudentsSub, 'ts', function ($join) {
                $join->on('ts.other_offer_id', '=', 'other_offers.id');
            })
            ->leftJoinSub($paidStudentsSub, 'ps', function ($join) {
                $join->on('ps.other_offer_id', '=', 'other_offers.id');
            })
            ->leftJoinSub($groupsCountSub, 'gc', function ($join) {
                $join->on('gc.other_offer_id', '=', 'other_offers.id');
            })
            ->selectRaw("
                other_offers.*,
                COALESCE(sn.name, '') as school_name,

                COALESCE(ts.total_students, 0) as total_students,
                COALESCE(ps.paid_students, 0) as paid_students,

                COALESCE(gc.groups_count, 0) as groups_count,

                (COALESCE(ts.total_students, 0) - COALESCE(ps.paid_students, 0)) as unpaid_students,
                (COALESCE(ps.paid_students, 0) * other_offers.payments) as collected_sum
            ");

        // role filter
        if (Auth::user()->hasRole('super-admin')) {
            $selectedSchoolId = $request->input('school_id');
            if ($selectedSchoolId !== null && $selectedSchoolId !== '') {
                $query->where('other_offers.school_id', $selectedSchoolId);
            } else {
                $query->whereNotNull('other_offers.school_id');
            }
        } else {
            $query->where('other_offers.school_id', Auth::user()->school_id);
        }

        $recordsTotal = (clone $query)->count('other_offers.id');

        // search
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('other_offers.name', 'like', "%{$search}%")
                    ->orWhere('sn.name', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $query)->count('other_offers.id');

        // ordering
        $orderColumnIndex = $request->input('order.0.column');
        $orderColumnName  = $request->input("columns.$orderColumnIndex.data");
        $orderDirection   = $request->input('order.0.dir', 'desc');

        $allowedOrder = [
            'id'              => 'other_offers.id',
            'school_name'     => 'sn.name',
            'name'            => 'other_offers.name',
            'payments'        => 'other_offers.payments',

            'groups_count'    => 'groups_count',
            'total_students'  => 'total_students',

            'paid_students'   => 'paid_students',
            'unpaid_students' => 'unpaid_students',
            'collected_sum'   => 'collected_sum',
        ];

        if ($orderColumnName && isset($allowedOrder[$orderColumnName])) {
            $query->orderBy($allowedOrder[$orderColumnName], $orderDirection);
        } else {
            $query->orderBy('other_offers.id', 'desc');
        }

        // paginate
        $data = $query->skip($start)->take($length)->get();

        // actions
        $data->transform(function ($item) {
            $item->action = '
                <button class="btn btn-info btn-edit-other-offers" data-id="' . $item->id . '" title="Խմբագրել"><i class="fas fa-edit"></i></button>
                <button class="btn btn-info btn-check-other-offers" data-id="' . $item->id . '" title="Ստուգել"><i class="fas fa-clipboard-check"></i></button>
                <button class="btn btn-danger btn-delete-other-offers" data-id="' . $item->id . '" title="Հեռացնել"><i class="fas fa-trash-alt"></i></button>
            ';
            return $item;
        });

        return [
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data->values()->toArray(),
        ];
    }
}
