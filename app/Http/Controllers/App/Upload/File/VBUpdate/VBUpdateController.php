<?php

namespace app\Http\Controllers\App\Upload\File\VBUpdate;

use App\Helpers\App\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class VBUpdateController extends Controller
{
    // buat metod upload file android
    function form(Request $request){
        $ResponApi = json_decode(Service::getVBVersion($request));
        if($ResponApi->status == 0){
            return redirect()->back()->with('error', 'Tejadi kesalahan, silahkan coba lagi');
        }
        return view('layouts.upload.file.vbupdate.form',[
                'title_menu'    => 'Upload File',
                'data'          => $ResponApi->data,
            ]);
    }
    function store(Request $request){
        $request->validate([
            'version' => 'required|string',
            'divisi' => 'required|string',
            'file'      => 'required',
        ],[
            'version.required'    => 'Version tidak boleh kosong',
            'version.string'      => 'Version harus berupa string',
            'divisi.required'    => 'Divisi tidak boleh kosong',
            'divisi.string'      => 'Divisi harus berupa string',
            'file.required'    => 'File tidak boleh kosong'
        ]);

        try {
            if($request->divisi == 'HONDA'){
                $path = 'sumavb/honda';
                $url = 'https://suma-honda.id/sumavb/honda/';
            } elseif ($request->divisi == 'GENERAL'){
                $path = 'sumavb/general';
                $url = 'https://suma-honda.id/sumavb/general/';
            } elseif ($request->divisi == 'VALIDASI') {
                $path = 'sumavb/validasi';
                $url = 'https://suma-honda.id/sumavb/validasi/';
            }

            $request->merge([
                'path_file' => $url.$request->file('file')->getClientOriginalName(),
            ]);

            $request->file('file')->move($path, $request->file('file')->getClientOriginalName());

            $responseApi = json_decode(Service::SimpanVBVersion($request));
            if($responseApi->status == 0){
                return redirect()->back()->with('failed', 'Gagal upload file');
            }

            return redirect()->back()->with('success', 'Berhasil upload file');
        } catch (\Throwable $th) {
            return redirect()->back()->with('failed', 'Maaf, terjadi kesalahan');
        }
    }


    // destroy
    function destroy(Request $request){

        $validasi = Validator::make($request->all(), [
            'version'   => 'required|string',
            'divisi'    => 'required|string',
        ],[
            'version.required'    => 'Version tidak boleh kosong',
            'version.string'      => 'Version harus berupa string',
        ]);

        if($validasi->fails()){
            return redirect()->back()->with('failed', $validasi->errors()->first());
        }

        $responseApi = json_decode(Service::deleteVBVersion($request));
        if($responseApi->status == 0){
            return redirect()->back()->with('failed', 'Gagal hapus file');
        }

        return redirect()->back()->with('success', 'Berhasil hapus file');
    }
}
