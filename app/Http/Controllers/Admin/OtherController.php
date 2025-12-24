<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests\OtherOffer\OtherOfferStoreRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\SchoolName;
use App\Models\Group;
use App\Models\Student;
use App\Models\OtherOffer;
use App\Models\OtherOfferGroup;
use App\Models\OtherOfferPaid;
use App\Services\OtherOffersService;
use Throwable;

class OtherController extends Controller
{

    protected $OtherOffersService;

    public function __construct(OtherOffersService $OtherOffersService){
        $this->OtherOffersService = $OtherOffersService;
    }
    public function index(){
        $user = Auth::user();
        $schools = [];
        if ($user->hasRole(['super-admin', 'school-admin'])) {
            $schools = SchoolName::get();
        }
        return view('admin.other.index', compact('schools'));
    }


    public function getOtherOffersData(Request $request){
        $result = $this->OtherOffersService->getOtherOfferData($request);
        return response()->json($result);
    }

    public function create(){
        $user = Auth::user();
        $schools = [];
        $groups = [];
        if ($user->hasRole(['super-admin'])) {
            $schools = SchoolName::get();
        }

        if ($user->hasRole(['school-admin'])) {
            $groups = Group::where('school_id', $user->school_id)->get();
        }

        return view('admin.other.form', compact('schools', 'groups'));
    }


    public function add(OtherOfferStoreRequest $request){
        try{

            $currentDate = Carbon::now();

            $validated = $request->validated();
            $groupIds = $validated['group_ids'];
            $schoolId = Auth::user()->school_id;
            if (Auth::user()->hasRole('super-admin')) {
                $schoolId = $request->school_id;
            }


            // dd($schoolId);

           $offer = OtherOffer::create([
                'name'      => $validated['name'],
                'payments'  => $validated['payments'],
                'school_id' => $schoolId,
            ]); 
            
            
            $lastId = $offer->id;

            $rows = collect($groupIds)->map(function ($groupId) use ($lastId, $currentDate) {
                return [
                    'other_offer_id'    => (int) $lastId,
                    'group_id'          => (int) $groupId,
                    'created_at'        => $currentDate,
                    'updated_at'        => $currentDate,
                ];
            })->toArray();

            OtherOfferGroup::insert($rows);

            return response()->json([
                'status' => 1, 
                'message' => 'Գործողությունը կատարված է',
            ]);  


        } catch(Throwable $e){
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage(), 
            ], 500);
        }  
    }

    public function edit(int $id){
        try {
            $user = Auth::user();

            $rows = OtherOffer::query()
                ->leftJoin('other_offer_groups', 'other_offer_groups.other_offer_id', '=', 'other_offers.id')
                ->where('other_offers.id', $id)
                ->select('other_offers.*', 'other_offer_groups.group_id')
                ->get();

            $offer = $rows->first(); 
            if (!$offer) {
                abort(404);
            }

            $selectedGroupIds = $rows->pluck('group_id')->filter()->unique()->values()->all();

            $schools = [];
            $groups  = [];

            if ($user->hasRole('super-admin')) {
                $schools = SchoolName::get();

                $groups = Group::where('school_id', $offer->school_id)->get();
            }

            if ($user->hasRole('school-admin')) {
                $groups = Group::where('school_id', $user->school_id)->get();
            }

            return view('admin.other.form', compact('schools', 'groups', 'offer', 'selectedGroupIds'));

        } catch (Throwable $e) {
            return response()->json([
                'status'  => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
            ], 500);
        }
    }


    public function update(OtherOfferStoreRequest $request, int $id){
        try {
            $currentDate = Carbon::now();
            $validated = $request->validated();

            // group_id
            $group_ids = array_map('intval', $validated['group_ids'] ?? []);

            $otherOffer = OtherOffer::findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | 1. otherOffer
            |--------------------------------------------------------------------------
            */
            $dbGroupIds = OtherOfferGroup::where('other_offer_id', $id)
                ->pluck('group_id')
                ->map(fn ($gid) => (int) $gid)
                ->values()
                ->all();

            /*
            |--------------------------------------------------------------------------
            | 2. “What to add / what to remove”
            |--------------------------------------------------------------------------
            */
            $groupsToAdd = array_values(array_diff($group_ids, $dbGroupIds));
            $groupsToDelete = array_values(array_diff($dbGroupIds, $group_ids));

            /*
            |--------------------------------------------------------------------------
            | 3. Deletion of groups (partial, if there are deals)
            |--------------------------------------------------------------------------
            */
            $blockedGroupIds = [];

            if (!empty($groupsToDelete)) {

                // All pivot rows (id + group_id) of this specific offer
                $pivotRows = OtherOfferGroup::where('other_offer_id', $id)
                    ->whereIn('group_id', $groupsToDelete)
                    ->get(['id', 'group_id']);

                $pivotIds = $pivotRows->pluck('id')->all();

                // Pivot rows that have deals
                $blockedPivotIds = OtherOfferPaid::whereIn('other_offer_group_id', $pivotIds)
                    ->pluck('other_offer_group_id')
                    ->unique()
                    ->all();

                // group_id values that cannot be deleted
                $blockedGroupIds = $pivotRows
                    ->whereIn('id', $blockedPivotIds)
                    ->pluck('group_id')
                    ->unique()
                    ->map(fn ($gid) => (int) $gid)
                    ->values()
                    ->all();

                // group_id values that can be deleted
                $allowedToDelete = array_values(array_diff($groupsToDelete, $blockedGroupIds));

                if (!empty($allowedToDelete)) {
                    OtherOfferGroup::where('other_offer_id', $id)
                        ->whereIn('group_id', $allowedToDelete)
                        ->delete();
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 4. add new group
            |--------------------------------------------------------------------------
            */
            if (!empty($groupsToAdd)) {
                $insert = [];
                foreach ($groupsToAdd as $gid) {
                    $insert[] = [
                        'other_offer_id' => $id,
                        'group_id'       => $gid,
                        'created_at' => $currentDate,
                        'updated_at' => $currentDate,
                    ];
                }
                OtherOfferGroup::insert($insert);
            }

            /*
            |--------------------------------------------------------------------------
            | 5. update offer
            |--------------------------------------------------------------------------
            */
            $otherOffer->update([
                'name'     => $validated['name'],
                'payments' => $validated['payments'],
            ]);

            /*
            |--------------------------------------------------------------------------
            | 6. response
            |--------------------------------------------------------------------------
            */
            if (!empty($blockedGroupIds)) {
                return response()->json([
                    'status' => 2,
                    'message' => 'Որոշ խմբեր չեն ջնջվել, քանի որ նրանցով կա ստեղծված գործարք',
                    'blocked_group_ids' => $blockedGroupIds,
                ]);
            }

            return response()->json([
                'status'  => 1,
                'message' => 'Գործողությունը կատարված է',
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'status'  => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function checkOtherOffers(int $id){

        $otherOffer = OtherOffer::findOrFail($id);
        $schoolId = $otherOffer->school_id;

        $otherOfferGroupIds = OtherOfferGroup::where('other_offer_id', $id)->pluck('id');

        $otherOfferGroupMap = OtherOfferGroup::where('other_offer_id', $id)
            ->pluck('id', 'group_id'); // group_id => other_offer_group_id

        $otherOfferGroupInnerGroupIds = $otherOfferGroupMap->keys();

        $otherOfferPaid = OtherOfferPaid::whereIn('other_offer_group_id', $otherOfferGroupIds)
            ->get(['other_offer_group_id', 'student_id', 'paid_status']);

        $paidMap = [];
        foreach ($otherOfferPaid as $row) {
            $paidMap[$row->other_offer_group_id][$row->student_id] = (int) $row->paid_status;
        }

        $studentsList = Student::whereIn('students.group_id', $otherOfferGroupInnerGroupIds)
            ->where('students.school_id', $schoolId)
            ->leftJoin('groups', 'students.group_id', '=', 'groups.id')
            ->select(
                'students.id',
                'students.school_id',
                'students.group_id',
                DB::raw("CONCAT(students.first_name, ' ', students.last_name) AS full_name"),
                'groups.name as group_name'
            )
            ->orderBy('students.group_id')
            ->orderBy('students.last_name')
            ->get()
            ->groupBy('group_id');

        return view('admin.other.check', compact('studentsList', 'otherOfferGroupMap', 'paidMap', 'otherOffer'));
    }

    public function savePaid(Request $request){
        try {
            $validated = $request->validate([
                'other_offer_group_id' => ['required', 'integer'],
                'paid' => ['required', 'array'],
                'paid.*' => ['nullable', 'in:0,1'],
            ]);

            $otherOfferGroupId = (int)$validated['other_offer_group_id'];
            $now = now();

            DB::transaction(function () use ($validated, $otherOfferGroupId, $now) {
                $rows = [];

                foreach ($validated['paid'] as $studentId => $value) {
                    $rows[] = [
                        'other_offer_group_id' => $otherOfferGroupId,
                        'student_id' => (int)$studentId,
                        'paid_status' => (int)$value,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                OtherOfferPaid::upsert(
                    $rows,
                    ['other_offer_group_id', 'student_id'],
                    ['paid_status', 'updated_at']
                );
            });

            return response()->json(['status' => 1, 'message' => 'Գործողությունը կատարված է']);

        } catch (Throwable $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function delete(int $id){
        try{

            $otherOffer = OtherOffer::findOrFail($id);
            $otherOfferGroupId = OtherOfferGroup::where('other_offer_id', $id)->first();
            $otherOfferPaid = OtherOfferPaid::where('other_offer_group_id', $otherOfferGroupId->id)->first();

            if($otherOfferPaid){
                return response()->json([
                    'status' => 2,
                    'message' => 'Գործարքը հնարավոր չէ ջնջել, կան կատարված վճարներ',
                ]);
            }

            $otherOffer->delete();

            return response()->json(['status' => 1, 'message' => 'Գործողությունը կատարված է']);

        }catch (Throwable $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
