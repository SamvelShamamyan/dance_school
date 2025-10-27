<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StaffRequest\StaffStoreRequest;
use App\Http\Requests\StaffRequest\StaffUpdateRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Staff;
use App\Models\StaffFile;
use App\Models\SchoolName;
use App\Services\StaffService;
use Throwable;



class StaffController extends Controller
{
    protected $staffService;
    public function __construct(StaffService $staffService){
        $this->staffService = $staffService;
    }

    public function index(){
        $schools = [];
        if (Auth::user()->hasRole('super-admin')) {
            $schools = SchoolName::get();
        }
        return view('admin.staff.index', compact('schools'));
    }

    public function create(Request $request){
         $schools = [];
        if (Auth::user()->hasRole('super-admin')) {
            $schools = SchoolName::get();
        }
        return view('admin.staff.form', compact('schools'));
    }

    public function getStaffData(Request $request){
        $result = $this->staffService->getStaffData($request);
        return response()->json($result);
    }

    public function add(StaffStoreRequest $request){
        try {
     
            $schoolId = Auth::user()->school_id;
            $validated = $request->validated();
            $formattedBirthDate = Carbon::createFromFormat('d.m.Y', $validated['birth_date'])->format('Y-m-d');
            $formattedStaffDate = Carbon::createFromFormat('d.m.Y', $validated['staff_date'])->format('Y-m-d');

            $staff = Staff::create([
                'first_name'    => $validated['first_name'],
                'last_name'     => $validated['last_name'],
                'father_name'   => $validated['father_name'] ?? null,
                'email'         => $validated['email'] ?? null,
                'phone_1'       => $validated['phone_1'] ?? null,
                'phone_2'       => $validated['phone_2'] ?? null,
                'address'       => $validated['address'] ?? null,
                'soc_number'    => $validated['soc_number'] ?? null,
                'birth_date'    => $formattedBirthDate,
                'created_date'  => $formattedStaffDate,
            ]);

            if (Auth::user()->hasRole('super-admin')) {
                 $staff->schools()->sync($validated['school_ids']);
            }else{
                $staff->schools()->sync($schoolId);
            }

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store("staff_files/{$staff->id}", 'public');
                    StaffFile::create([
                        'staff_id' => $staff->id,
                        'path'     => $path,
                        'url'      => Storage::disk('public')->url($path),
                        'name'     => $file->getClientOriginalName(),
                        'size'     => $file->getSize(),
                    ]);
                }
            }

            return response()->json([
                'status' => 1,
                'message' => 'Պահպանված է',
                'redirect'=> route('admin.staff.index'),
                'id' => $staff->id,
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'status'  => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id){
        $staff = Staff::with('files')->findOrFail($id);
        $selectedSchoolIds = [];
        $schools = [];
        
        if (Auth::user()->hasRole('super-admin')) {
            $schools = SchoolName::get();
            $selectedSchoolIds = $staff->schools()->pluck('school_id')->toArray();
        }

        $files = $staff->files->map(function ($f) {
            $url = $f->url ?? Storage::disk('public')->url($f->path);
            $isImage = Str::endsWith(strtolower($f->name ?? $f->path), [
                '.jpg','.jpeg','.png','.gif','.webp', 'pdf'
            ]);

            return [
                'id'   => $f->id,
                'name' => $f->name ?? basename($f->path),
                'size' => (int) ($f->size ?? 0),
                'url'  => $url,
                'thumb'=> $isImage ? $url : null, 
            ];
            
        })->values();

        return view('admin.staff.form', [
            'staff' => $staff,
            'staffFilesJson' => $files->toJson(),
            'schools' => $schools,
            'selectedSchoolIds' => $selectedSchoolIds,
        ]);
    }


    public function update(StaffUpdateRequest $request, $id){
        DB::beginTransaction();

        try {

            $schoolId = Auth::user()->school_id;
            $validated = $request->validated();
            $staff = Staff::findOrFail($id);
            $formattedBirthDate = Carbon::createFromFormat('d.m.Y', $validated['birth_date'])->format('Y-m-d');
            $formattedStaffDate = Carbon::createFromFormat('d.m.Y', $validated['staff_date'])->format('Y-m-d');

            $staff->update([
                'first_name'    => $validated['first_name'],
                'last_name'     => $validated['last_name'],
                'father_name'   => $validated['father_name'] ?? null,
                'email'         => $validated['email'] ?? null,
                'phone_1'       => $validated['phone_1'] ?? null,
                'phone_2'       => $validated['phone_2'] ?? null,
                'address'       => $validated['address'] ?? null,
                'soc_number'    => $validated['soc_number'] ?? null,
                'birth_date'    => $formattedBirthDate,
                'created_date'  => $formattedStaffDate,
            ]);


            if (Auth::user()->hasRole('super-admin')) {
                 $staff->schools()->sync($validated['school_ids']);
            }else{
                $staff->schools()->sync($schoolId);
            }

            $removeIds = (array) $request->input('removed_files', []);
            if (!empty($removeIds)) {
                $toDelete = StaffFile::where('staff_id', $staff->id)
                    ->whereIn('id', $removeIds)
                    ->get();

                foreach ($toDelete as $f) {
                    if (!empty($f->path)) {
                        Storage::disk('public')->delete($f->path);
                    }
                }

                StaffFile::whereIn('id', $toDelete->pluck('id'))->delete();
            }

            $incoming = $request->file('files');
            if ($incoming) {
                $incoming = is_array($incoming) ? $incoming : [$incoming];

                foreach ($incoming as $file) {
                    if (!$file) continue;

                    $path = $file->store("staff_files/{$staff->id}", 'public');

                    StaffFile::create([
                        'staff_id' => $staff->id,
                        'path'     => $path,
                        'url'      => Storage::disk('public')->url($path),
                        'name'     => $file->getClientOriginalName(),
                        'size'     => $file->getSize(),
                    ]);
                }
            } 

            DB::commit();
            return response()->json([
                'status' => 1, 
                'message' => 'Թարմացվել է',
                'redirect' => route('admin.staff.edit', ['id' => $staff->id]),
            ]);

        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function delete($id){
        try {
            $staff = Staff::find($id);

            if (!$staff) {
                return response()->json([
                    'status' => -2,
                    'message' => 'Տվյալներ չեն գտնվել։'
                ], 404);
            }

            $staff->delete();

            return response()->json([
                'status' => 1,
                'message' => 'Գործողությունը կատարված է։'
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Սխալ է տեղի ունեցել։ Խնդրում ենք կրկին փորձել։',
                'error' => $e->getMessage(), 
            ], 500);
        }
    }

}
