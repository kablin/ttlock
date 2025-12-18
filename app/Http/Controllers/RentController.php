<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Rent;
use App\Models\Lock;
use Illuminate\Support\Facades\Validator;
use App\Models\LockApiLog;


class RentController extends Controller
{





  public function objects2(Request $request)
  {
    $rent_page = $request->input('rent_page', 1);
    $lock_page = $request->input('lock_page', 1);


    $rents = auth()->user()->rents()->with('locks')->orderBy('id');
    if ($request->has('rent_search')) {
      $rents = $rents->where('name', 'ilike', '%' . $request->rent_search . '%');
    }
    $rents = $rents->paginate(6, ['*'], 'rent_page', $rent_page);





    $free_locks = auth()->user()->locks()->orderBy('id');
    if ($request->has('lock_search')) {
      $free_locks = $free_locks->where('lock_alias', 'ilike', '%' . $request->lock_search . '%');
    }

    $free_locks = $free_locks->paginate(12, ['*'], 'lock_page', $lock_page);


    $params = ['rents' => $rents, 'free_locks' => $free_locks, 'success' => session('success')];
    if ($request->has('rent_search'))  $params['rent_search'] = $request->rent_search;
    if ($request->has('lock_search'))  $params['lock_search'] = $request->lock_search;

    return Inertia::render('Objects2', $params);
  }




  public function page(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'rent_page' => 'required',
      'lock_page' => 'required',
      'lock_search' => 'nullable',
      'rent_search' => 'nullable'

    ]);



    $validated = $validator->safe()->only(['rent_page', 'lock_page', 'lock_search', 'rent_search']);

    if ($validator->fails()) {
      return response()->json(['status' => false], 400);
    }


    return to_route('objects2', ['rent_page' => $validated['rent_page'], 'lock_page' => $validated['lock_page'], 'lock_search' => $validated['lock_search'] ?? '', 'rent_search' => $validated['rent_search'] ?? ''])->with('success', '');
  }







  public function attach_lock2(Request $request)
  {

    LockApiLog::create([
      'is_ttlock_result' => false,
      'api_method' => 'attachLockToRent',
      'user_id' => auth()->user()->id,
      'ip' => json_encode($request->ip()),
      'params' => json_encode($request->all()),
    ]);




    $validator = Validator::make($request->all(), [
      'rent_id' => 'required|exists:rents,id',
      'lock_id' => 'required|exists:locks,id',
      'rent_page' => 'required',
      'lock_page' => 'required',
      'lock_search' => 'nullable',
      'rent_search' => 'nullable'

    ]);

    $validated = $validator->safe()->only(['rent_id', 'lock_id', 'rent_page', 'lock_page', 'lock_search', 'rent_search']);

    if ($validator->fails()) {
      return response()->json(['status' => false], 400);
    }


    $rent = Rent::find($validated['rent_id']);


    $rent->locks()->syncWithoutDetaching([$validated['lock_id']]);


    return to_route('objects2', ['lock_search' => $validated['lock_search'] ?? '', 'rent_search' => $validated['rent_search'] ?? '', 'rent_page' => $validated['rent_page'], 'lock_page' => $validated['lock_page']])->with('success', '');
  }






  public function dattach_lock2(Request $request)
  {
    LockApiLog::create([
      'is_ttlock_result' => false,
      'api_method' => 'DattachLockToRent',
      'user_id' => auth()->user()->id,
      'ip' => json_encode($request->ip()),
      'params' => json_encode($request->all()),
    ]);



    $validator = Validator::make($request->all(), [
      'rent_id' => 'required|exists:rents,id',
      'drent_id' => 'required|exists:rents,id',
      'lock_id' => 'required|exists:locks,id',
      'rent_page' => 'required',
      'lock_page' => 'required',
      'lock_search' => 'nullable',
      'rent_search' => 'nullable'

    ]);

    $validated = $validator->safe()->only(['rent_id', 'lock_id', 'drent_id', 'rent_page', 'lock_page', 'lock_search', 'rent_search']);

    if ($validator->fails()) {
      return response()->json(['status' => false], 400);
    }



    $rent = Rent::find($validated['rent_id']);
    $drent = Rent::find($validated['drent_id']);
    $lock = Lock::find($validated['lock_id']);

    $rent->locks()->syncWithoutDetaching([$validated['lock_id']]);
    $drent->locks()->detach($lock);

    return to_route('objects2', ['lock_search' => $validated['lock_search'] ?? '', 'rent_search' => $validated['rent_search'] ?? '', 'rent_page' => $validated['rent_page'], 'lock_page' => $validated['lock_page']])->with('success', '');
  }






  public function detach_lock2(Request $request)
  {

    LockApiLog::create([
      'is_ttlock_result' => false,
      'api_method' => 'detachLockToRent',
      'user_id' => auth()->user()->id,
      'ip' => json_encode($request->ip()),
      'params' => json_encode($request->all()),
    ]);


    $validator = Validator::make($request->all(), [
      'lock_id' => 'required|exists:locks,id',
      'rent_id' => 'required|exists:rents,id',
      'rent_page' => 'required',
      'lock_page' => 'required',
      'lock_search' => 'nullable',
      'rent_search' => 'nullable'
    ]);

    $validated = $validator->safe()->only(['lock_id', 'rent_id', 'rent_page', 'lock_page', 'lock_search', 'rent_search']);

    if ($validator->fails()) {
      return response()->json(['status' => false], 400);
    }

    $rent = Rent::find($validated['rent_id']);
    $lock = Lock::find($validated['lock_id']);

    $rent->locks()->detach($lock);

    return to_route('objects2', ['lock_search' => $validated['lock_search'] ?? '', 'rent_search' => $validated['rent_search'] ?? '', 'rent_page' => $validated['rent_page'], 'lock_page' => $validated['lock_page']])->with('success', '');
  }























  public function index(Request $request)
  {
    $rents = auth()->user()->parent_rents()->with('children')
      ->get();

    //  dd($rents);
    return Inertia::render('RentsObjects', ['rents' => $rents,]);
  }




  public function objects(Request $request)
  {
    $rents = auth()->user()->rents()->with('locks')->orderBy('id')
      ->get();

    //$free_locks = auth()->user()->locks()->free()->select(['id', 'lock_alias'])->orderBy('id')
    $free_locks = auth()->user()->locks()->select(['id', 'lock_alias'])->orderBy('id')

      ->get();
    //  dd($rents);
    return Inertia::render('Objects', ['rents' => $rents, 'free_locks' => $free_locks, 'success' => session('success')]);
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
    auth()->user()->rents()->create(['name' => $validated['name'], 'description' => $validated['description']]);
    return to_route('objects')->with('success', '');
  }






  public function update(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'rent_id' => 'required|exists:rents,id',
      'name' => 'required',
      'description' => 'nullable',
    ]);

    if ($validator->fails()) {
      return response()->json(['status' => false], 400);
    }

    $validated = $validator->safe()->only(['name', 'description', 'rent_id']);

    Rent::where(['id' => $validated['rent_id'], 'user_id' => auth()->user()->id])->update(['name' => $validated['name'], 'description' => $validated['description']]);

    return to_route('objects')->with('success', '');
  }





  public function delete(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'rent_id' => 'required|exists:rents,id',
    ]);

    if ($validator->fails()) {
      return response()->json(['status' => false], 400);
    }

    $validated = $validator->safe()->only(['rent_id']);
    Rent::find($validated['rent_id'])->delete();
    return to_route('objects')->with('success', '');
  }







  public function attach_lock(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'rent_id' => 'required|exists:rents,id',
      'lock_id' => 'required|exists:locks,id',

    ]);

    $validated = $validator->safe()->only(['rent_id', 'lock_id']);

    if ($validator->fails()) {
      return response()->json(['status' => false], 400);
    }


    $rent = Rent::find($validated['rent_id']);
    //$lock = Lock::find($validated['lock_id']);

    $rent->locks()->syncWithoutDetaching([$validated['lock_id']]);


    // Lock::where(['id' => $validated['lock_id'], 'user_id' => auth()->user()->id])->update(['rent_id' => $validated['rent_id']]);

    return to_route('objects')->with('success', '');
  }






  public function detach_lock(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'lock_id' => 'required|exists:locks,id',
      'rent_id' => 'required|exists:rents,id',
    ]);

    $validated = $validator->safe()->only(['rent_id', 'lock_id']);

    if ($validator->fails()) {
      return response()->json(['status' => false], 400);
    }

    // Lock::where(['id' => $validated['lock_id'], 'user_id' => auth()->user()->id])->update(['rent_id' => null]);


    $rent = Rent::find($validated['rent_id']);
    $lock = Lock::find($validated['lock_id']);

    $rent->locks()->detach($lock);

    return to_route('objects')->with('success', '');

    //return response()->json(['status' => true]);
  }






  public function create2(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'name' => 'required',
      'description' => 'nullable',
    ]);

    if ($validator->fails()) {
      return response()->json(['status' => false], 400);
    }

    $validated = $validator->safe()->only(['name', 'description']);
    auth()->user()->rents()->create(['name' => $validated['name'], 'description' => $validated['description']]);


    return to_route('objects2')->with('success', '');


    //return Inertia::render('Objects2', ['rents' => $rents, 'free_locks' => $free_locks]);
  }






  public function update2(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'rent_id' => 'required|exists:rents,id',
      'name' => 'required',
      'description' => 'nullable',
    ]);

    if ($validator->fails()) {
      return response()->json(['status' => false], 400);
    }

    $validated = $validator->safe()->only(['name', 'description', 'rent_id']);

    Rent::where(['id' => $validated['rent_id'], 'user_id' => auth()->user()->id])->update(['name' => $validated['name'], 'description' => $validated['description']]);


    return to_route('objects2')->with('success', '');
    //return Inertia::render('Objects2', ['rents' => $rents, 'free_locks' => $free_locks]);
  }






  public function delete2(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'rent_id' => 'required|exists:rents,id',
    ]);

    if ($validator->fails()) {
      return response()->json(['status' => false], 400);
    }

    $validated = $validator->safe()->only(['rent_id']);
    Rent::find($validated['rent_id'])->delete();

    $rents = auth()->user()->rents()->with('locks')->orderBy('id')
      ->get();
    $free_locks = auth()->user()->locks()->free()->select(['id', 'lock_alias'])->orderBy('id')
      ->get();

    return to_route('objects2')->with('success', '');


    //return Inertia::render('Objects2', ['rents' => $rents, 'free_locks' => $free_locks]);
  }
}
