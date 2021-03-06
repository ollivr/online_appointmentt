<?php

namespace App\Http\Controllers\Admin;

use App\Establishment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EstablishmentController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('isAdmin');
        $establishments=Establishment::all();
        return  view('admin.establishment.index')->with('establishments',$establishments);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('isAdmin');

        return view('admin.establishment.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('isAdmin');
//        dd($request->all());
        $this->validateRequest($request);

        $establishment=Establishment::create($request->all());
        return redirect('/establishment');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Establishment  $establishment
     * @return \Illuminate\Http\Response
     */
    public function show(Establishment $establishment)
    {
        $this->authorize('isAdmin');

        return view('admin.establishment.show')->with('establishment',$establishment);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Establishment  $establishment
     * @return \Illuminate\Http\Response
     */
    public function edit(Establishment $establishment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Establishment  $establishment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Establishment $establishment)
    {
        $this->authorize('isAdmin');
//        dd($request->all());
        $this->validateRequest($request);
        $establishment->update($request->all());
        return redirect('/establishment');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Establishment  $establishment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Establishment $establishment)
    {
        foreach ($establishment->doctors as $doctor){
            $specialty = $doctor->specialty()->first();
            $appointments= $doctor->appointments()->get();
            $doctor->establishment()->dissociate();
            if (count($appointments) != 0 ){
                foreach ($appointments as $appointment){
                    $appointment->patient()->dissociate();
                    $appointment->doctor()->dissociate();
                    $appointment->delete();
                }
            }
            $doctor->specialty()->dissociate();
            foreach ($doctor->days as $day){

                $day->doctor()->dissociate();
                $day->delete();
            }
            $doctor->delete();
            if (count($specialty->doctors) == 0 ){
                foreach ($specialty->establishment as $item){
                    $item->specialties()->detach($specialty->id);
                }
                $specialty->delete();
            }
            $establishment->specialties()->detach($specialty->id);
        }
        foreach ($establishment->patients as $patient){
            $establishment->patients()->detach($patient->id);
        }
        $establishment->delete();
        return  redirect('/establishment');
    }

    public function validateRequest($request)
    {
        $rules = [
            'Ename' => 'required','string', 'max:255',
            'Etel' => 'required',
            'Eadresse' => 'required',
            'Eemail' => 'required', 'string', 'email', 'max:255',
            'Etype' => 'required|in:Doctor office,Clinic,Hospital'
        ];
        $this->validate($request, $rules);
    }
}
