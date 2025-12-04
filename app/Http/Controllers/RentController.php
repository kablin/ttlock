<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Rent;
use App\Models\Lock;
use Illuminate\Support\Facades\Validator;


class RentController extends Controller
{
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

    $free_locks = auth()->user()->locks()->free()->select(['id', 'lock_alias'])->orderBy('id')
      ->get();
    //  dd($rents);
    return Inertia::render('Objects', ['rents' => $rents, 'free_locks' => $free_locks, 'success' => session('success')]);
  }


  public function objects2(Request $request)
  {
    $rents = auth()->user()->rents()->with('locks')->orderBy('id')
      ->get();

    $free_locks = auth()->user()->locks()->free()->orderBy('id')
      ->get();
    return Inertia::render('Objects2', ['rents' => $rents, 'free_locks' => $free_locks, 'success' => session('success')]);
  }





/*

  public function refresh(Request $request)
  {
    $rents = auth()->user()->rents()->with('locks')->orderBy('id')
      ->get();
    $free_locks = auth()->user()->locks()->free()->select(['id', 'lock_alias'])->orderBy('id')
      ->get();


    return response()->json(['rents' => $rents, 'free_locks' => $free_locks]);
  }

*/









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

    Lock::where(['id' => $validated['lock_id'], 'user_id' => auth()->user()->id])->update(['rent_id' => $validated['rent_id']]);

    return to_route('objects')->with('success', '');
  }






  public function detach_lock(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'lock_id' => 'required|exists:locks,id',
    ]);

    $validated = $validator->safe()->only(['lock_id']);

    if ($validator->fails()) {
      return response()->json(['status' => false], 400);
    }

    Lock::where(['id' => $validated['lock_id'], 'user_id' => auth()->user()->id])->update(['rent_id' => null]);

    return to_route('objects')->with('success', '');

    //return response()->json(['status' => true]);
  }









  public function attach_lock2(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'rent_id' => 'required|exists:rents,id',
      'lock_id' => 'required|exists:locks,id',

    ]);

    $validated = $validator->safe()->only(['rent_id', 'lock_id']);

    if ($validator->fails()) {
      return response()->json(['status' => false], 400);
    }

    Lock::where(['id' => $validated['lock_id'], 'user_id' => auth()->user()->id])->update(['rent_id' => $validated['rent_id']]);



    //return Inertia::render('Objects2', ['rents' => $rents, 'free_locks' => $free_locks]);
    return to_route('objects2')->with('success', '');
  }






  public function detach_lock2(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'lock_id' => 'required|exists:locks,id',
    ]);

    $validated = $validator->safe()->only(['lock_id']);

    if ($validator->fails()) {
      return response()->json(['status' => false], 400);
    }

    Lock::where(['id' => $validated['lock_id'], 'user_id' => auth()->user()->id])->update(['rent_id' => null]);

    return to_route('objects2')->with('success', '');

    //return Inertia::render('Objects2', ['rents' => $rents, 'free_locks' => $free_locks]);
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
