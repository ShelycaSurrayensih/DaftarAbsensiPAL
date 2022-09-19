<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Rundown;
use Carbon\Carbon;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
USE Pdf;

class AbsensiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $visitor_lists = Absensi::orderBy('created_at', 'DESC')->get();
        $data = Absensi::latest()->paginate(10);

        $datetime = Carbon::now();
        $current_date = Absensi::whereDate('created_at', Carbon::today())->get(['nama', 'created_at']);
        $current_week = Absensi::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->get();
        $current_month = Absensi::whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->get(['nama', 'created_at']);
        $rundown = Rundown::all();
        $absensi = Absensi::all();
        $absensiDetail = Rundown::where('idRundowns', $id)->first();
        return view('absensi.absensi', compact('absensi', 'absensiDetail', 'rundown','visitor_lists', 'data', 'current_date', 'current_week', 'current_month', 'datetime', 'id'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
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
        $request->validate([
            'idRundowns'=>'required',
            'nama'=>'required',
            'jabatan'=>'required',
            'instansi'=>'required',
            'telp'=>'required',
            'idRundowns'=>'required',
            'tandatangan'=>'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if ($request->hasFile('tandatangan')) {
            $image_name = $request->file('tandatangan')->store('images', 'public');
        }
        // $input = $request->all();
        // $data = Absensi::create($input);

        // $rundown = Rundown::where($request->get('idRundowns'));

        $absensis = new Absensi;
        $absensis->nama = $request->get('nama');
        $absensis->jabatan = $request->get('jabatan');
        $absensis->instansi = $request->get('instansi');
        $absensis->telp = $request->get('telp');
        $absensis->idRundowns = $request->get('idRundowns');
        $absensis->tandatangan = $image_name;
        //fungsi eloquent untuk menambah data dengan relasi belongsTo
        // $absensis->rundown()->associate($input);
        $absensis->save();
        Alert::success('Succes','Data Absensi Berhasil Ditambahkan');
        return redirect()->route('absensi.absensi');
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
        $absensis = Absensi::all();
        return view('absensi.edit', compact('absensis'));
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
        Absensi::find($id)->update([
            'Nama'=>$request->nama,
            'Jabatan'=>$request->jabatan,
            'Instansi'=>$request->instansi,
            'No Hp'=>$request->telp,
            // 'gambar'=>$request->gambar,
        ]);
        $input = $request->all();
        if ($image = $request->file('tandatangan')) {
            $destinationPath = 'images/';
            $profileImage = date('YmdHis').".".$image->getClientOriginalExtension();
            $image->move($destinationPath, $profileImage);
            $input['tandatangan'] = "$profileImage";
        } else {
            unset($input['tandatangan']);
        }
        Absensi::find($id)->update($input);
        Alert::success('Success', 'Data Absensi Berhasil Diupdate');
        return redirect()->route('absensi.absensi');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tandatangan = Absensi::find($id);
        unlink("images/".$tandatangan->tandatangan);
        Absensi::find($id)->delete();
        Alert::success('Success','Data Absensi berhasil dihapus');
        return redirect()->route('absensi.absensi');
    }
    // -- PDF Detail --
    public function pdf($id)
    {
        // $absensi = Absensi::orderBy('tanggal')->orderBy('waktuMulai')->get();
        $absensiDetail = Rundown::where('idRundowns', $id)->first();
        $absensiKonten = Absensi::where('idRundowns', $id)->groupBy('tanggal')->get();
        $absensiFirst = Absensi::groupBy('tanggal')->first();
        $rundown = Rundown::all();
        $pdf = PDF::loadview('index.dataabsensi', compact( 'absensiDetail', 'rundown', 'absensiKonten', 'absensiFirst'));
        return $pdf->stream();
    }
}
