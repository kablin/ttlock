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

        $rent_page = $request->input('rent_page', 1);
        $lock_page = $request->input('lock_page', 1);
        $group_page = $request->input('group_page', 1);


        $rents = auth()->user()->rents()->with('locks')->orderBy('id');
        if ($request->has('rent_search')) {
            $rents = $rents->where('name', 'ilike', '%' . $request->rent_search . '%');
        }
        $rents = $rents->paginate(12, ['*'], 'rent_page', $rent_page);


        $free_locks = auth()->user()->locks()->orderBy('id');
        if ($request->has('lock_search')) {
            $free_locks = $free_locks->where('lock_alias', 'ilike', '%' . $request->lock_search . '%');
        }
        $free_locks = $free_locks->paginate(12, ['*'], 'lock_page', $lock_page);



        $groups = auth()->user()->groups()->with('locks', 'rents')->orderBy('id');
        if ($request->has('group_search')) {
            $groups = $groups->where('name', 'ilike', '%' . $request->group_search . '%');
        }
        $groups = $groups->paginate(12, ['*'], 'group_page', $group_page);


        $params = ['rents' => $rents, 'free_locks' => $free_locks, 'groups_list' => $groups, 'success' => session('success')];
        if ($request->has('rent_search'))  $params['rent_search'] = $request->rent_search;
        if ($request->has('lock_search'))  $params['lock_search'] = $request->lock_search;
        if ($request->has('group_search'))  $params['group_search'] = $request->group_search;



        return Inertia::render('Groups', $params);
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

            'rent_page' => 'required',
            'lock_page' => 'required',
            'group_page' => 'required',
            'lock_search' => 'nullable',
            'rent_search' => 'nullable',
            'group_search' => 'nullable'

        ]);

        $validated = $validator->safe()->only(['group_id', 'lock_id', 'rent_page', 'lock_page', 'lock_search', 'rent_search', 'group_page', 'group_search']);

        if ($validator->fails()) {
            return response()->json(['status' => false], 400);
        }


        $group = Group::find($validated['group_id']);

        //        $lock = Lock::find($validated['lock_id']);

        //      $group->locks()->attach($validated['lock_id']);



        $group->locks()->syncWithoutDetaching([$validated['lock_id']]);

        $params = [
            'lock_search' => $validated['lock_search'] ?? '',
            'rent_search' => $validated['rent_search'] ?? '',
            'group_search' => $validated['group_search'] ?? '',
            'rent_page' => $validated['rent_page'],
            'lock_page' => $validated['lock_page'],
            'group_page' => $validated['group_page']
        ];


        return to_route('groups', $params)->with('success', '');
    }







    public function dattach_lock(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:groups,id',
            'dgroup_id' => 'required|exists:groups,id',
            'lock_id' => 'required|exists:locks,id',

            'rent_page' => 'required',
            'lock_page' => 'required',
            'group_page' => 'required',
            'lock_search' => 'nullable',
            'rent_search' => 'nullable',
            'group_search' => 'nullable'


        ]);

        $validated = $validator->safe()->only(['group_id', 'lock_id', 'dgroup_id', 'rent_page', 'lock_page', 'lock_search', 'rent_search', 'group_page', 'group_search']);

        if ($validator->fails()) {
            return response()->json(['status' => false], 400);
        }


        $group = Group::find($validated['group_id']);
        $dgroup = Group::find($validated['dgroup_id']);
        $lock = Lock::find($validated['lock_id']);

        $group->locks()->syncWithoutDetaching([$validated['lock_id']]);
        $dgroup->locks()->detach($lock);

        $params = [
            'lock_search' => $validated['lock_search'] ?? '',
            'rent_search' => $validated['rent_search'] ?? '',
            'group_search' => $validated['group_search'] ?? '',
            'rent_page' => $validated['rent_page'],
            'lock_page' => $validated['lock_page'],
            'group_page' => $validated['group_page']
        ];


        return to_route('groups', $params)->with('success', '');
    }






    public function detach_lock(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'lock_id' => 'required|exists:locks,id',
            'group_id' => 'required|exists:groups,id',

            'rent_page' => 'required',
            'lock_page' => 'required',
            'group_page' => 'required',
            'lock_search' => 'nullable',
            'rent_search' => 'nullable',
            'group_search' => 'nullable'

        ]);

        $validated = $validator->safe()->only(['lock_id', 'group_id', 'rent_page', 'lock_page', 'lock_search', 'rent_search', 'group_page', 'group_search']);

        if ($validator->fails()) {
            return response()->json(['status' => false], 400);
        }

        $group = Group::find($validated['group_id']);
        $lock = Lock::find($validated['lock_id']);

        $group->locks()->detach($lock);

        $params = [
            'lock_search' => $validated['lock_search'] ?? '',
            'rent_search' => $validated['rent_search'] ?? '',
            'group_search' => $validated['group_search'] ?? '',
            'rent_page' => $validated['rent_page'],
            'lock_page' => $validated['lock_page'],
            'group_page' => $validated['group_page']
        ];


        return to_route('groups', $params)->with('success', '');
    }




    public function page(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'rent_page' => 'required',
            'lock_page' => 'required',
            'group_page' => 'required',
            'lock_search' => 'nullable',
            'rent_search' => 'nullable',
            'group_search' => 'nullable'

        ]);



        $validated = $validator->safe()->only(['rent_page', 'lock_page', 'lock_search', 'rent_search', 'group_page', 'group_search']);

        if ($validator->fails()) {
            return response()->json(['status' => false], 400);
        }

        $params = [
            'lock_search' => $validated['lock_search'] ?? '',
            'rent_search' => $validated['rent_search'] ?? '',
            'group_search' => $validated['group_search'] ?? '',
            'rent_page' => $validated['rent_page'],
            'lock_page' => $validated['lock_page'],
            'group_page' => $validated['group_page']
        ];

        return to_route('groups',$params)->with('success', '');
    }
}
