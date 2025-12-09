<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Rent;
use App\Models\Group;
use App\Models\Lock;
use Illuminate\Support\Facades\Validator;


class GroupController extends Controller
{
    public function index(Request $request)
    {
        $rents = auth()->user()->rents()->with('locks')->orderBy('id')
            ->get();

        $groups = auth()->user()->groups()->with('locks', 'rents')->orderBy('id')
            ->get();



        $free_locks = auth()->user()->locks()->orderBy('id')->get();

        return Inertia::render('Groups', ['rents' => $rents, 'groups_list' => $groups, 'free_locks' => $free_locks, 'success' => session('success')]);
    }




    public function create(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false], 400);
        }

        $validated = $validator->safe()->only(['name', 'description']);
        auth()->user()->groups()->create(['name' => $validated['name'], 'description' => $validated['description']]);
        return to_route('groups')->with('success', '');
    }






    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:groups,id',
            'name' => 'required',
            'description' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false], 400);
        }

        $validated = $validator->safe()->only(['name', 'description', 'group_id']);

        Group::where(['id' => $validated['group_id'], 'user_id' => auth()->user()->id])->update(['name' => $validated['name'], 'description' => $validated['description']]);

        return to_route('groups')->with('success', '');
    }





    public function delete(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:groups,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false], 400);
        }

        $validated = $validator->safe()->only(['group_id']);
        Group::find($validated['group_id'])->delete();
        return to_route('groups')->with('success', '');
    }








    public function attach_lock(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:groups,id',
            'lock_id' => 'required|exists:locks,id',

        ]);

        $validated = $validator->safe()->only(['group_id', 'lock_id']);

        if ($validator->fails()) {
            return response()->json(['status' => false], 400);
        }


        $group = Group::find($validated['group_id']);

        //        $lock = Lock::find($validated['lock_id']);

        //      $group->locks()->attach($validated['lock_id']);



        $group->locks()->syncWithoutDetaching([$validated['lock_id']]);



        return to_route('groups')->with('success', '');
    }







    public function dattach_lock(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:groups,id',
            'dgroup_id' => 'required|exists:groups,id',
            'lock_id' => 'required|exists:locks,id',

        ]);

        $validated = $validator->safe()->only(['group_id', 'lock_id', 'dgroup_id']);

        if ($validator->fails()) {
            return response()->json(['status' => false], 400);
        }


        $group = Group::find($validated['group_id']);
        $dgroup = Group::find($validated['dgroup_id']);
        $lock = Lock::find($validated['lock_id']);

        $group->locks()->syncWithoutDetaching([$validated['lock_id']]);
        $dgroup->locks()->detach($lock);
        return to_route('groups')->with('success', '');
    }






    public function detach_lock(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'lock_id' => 'required|exists:locks,id',
            'group_id' => 'required|exists:groups,id',
        ]);

        $validated = $validator->safe()->only(['lock_id', 'group_id']);

        if ($validator->fails()) {
            return response()->json(['status' => false], 400);
        }

        $group = Group::find($validated['group_id']);
        $lock = Lock::find($validated['lock_id']);

        $group->locks()->detach($lock);

        return to_route('groups')->with('success', '');
    }
}
