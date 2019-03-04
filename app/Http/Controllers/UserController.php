<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use Validator;
use App\User; 

class UserController extends Controller
{
    public $successStatus = 200;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->ajax()){
            \DB::statement(\DB::raw('set @rownum=0'));
            $user=\App\Models\User::select(\DB::raw('@rownum := @rownum + 1 AS no'),
            'tbl_user.*')
            ->where('id_bu',\Auth::user()->ID_BU)
            ->get();

            return \Datatables::of($user)
                ->addColumn('detail',function($query){
                    return "<a href='".\URL::to('mam/users/'.$query->id)."' class='btn btn-info'><i class='icon-list'></i></a>";
                })
                ->addColumn('action',function($query){
                    $html="<div class='btn-group' data-toggle='buttons'>";
                    $html.="<a href='#' class='btn btn-sm btn-success role' kode='".$query->id."' title='Role'><i class='icon-gear'></i></a>";
                    $html.="<a href='#' class='btn btn-sm btn-danger hapus' kode='".$query->id."' title='Hapus'><i class='icon-trash'></i></a>";
                    $html.="</div>";

                    return $html;
                })
                ->rawColumns(['detail','action'])
                ->make(true);
        }

        return view('mam.user.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }
    public function details(){ 
    $user = Auth::user(); 
    return response($user,200); 
    } 

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function dashboard_user(Request $request){
        return view('mam.user.index')
            ->with('home','Dashboard')
            ->with('title','User');
    }
}
