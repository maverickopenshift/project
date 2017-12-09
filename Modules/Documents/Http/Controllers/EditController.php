<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Response;

use Modules\Documents\Entities\DocType;
use Modules\Documents\Entities\Documents;
use Modules\Documents\Entities\DocBoq;
use Modules\Documents\Entities\DocMeta;
use Modules\Documents\Entities\DocPic;
use Modules\Documents\Entities\DocAsuransi;
use Modules\Documents\Http\Controllers\SuratPengikatanEditController as SuratPengikatanEdit;
use Modules\Documents\Http\Controllers\MouEditController as MouEdit;
use Modules\Documents\Http\Controllers\AmandemenSpEditController as AmandemenSpEdit;
use Modules\Documents\Http\Controllers\SpEditController as SpEdit;
use Modules\Documents\Http\Controllers\AmandemenKontrakEditController as AmademenKontrakEdit;


use App\Helpers\Helpers;
use Validator;
use DB;
use Auth;

class EditController extends Controller
{
  protected $documents;
  protected $SuratPengikatanEdit;
  protected $MouEdit;
  protected $amandemenSpEdit;
  protected $spEdit;
  protected $amademenKontrakEdit;

  public function __construct(Documents $documents,SuratPengikatanEdit $SuratPengikatanEdit,MouEdit $MouEdit,AmandemenSpEdit $amandemenSpEdit,SpEdit $spEdit, AmademenKontrakEdit $amademenKontrakEdit)
  {
      $this->documents = $documents;
      $this->SuratPengikatanEdit = $SuratPengikatanEdit;
      $this->MouEdit = $MouEdit;
      $this->amandemenSpEdit = $amandemenSpEdit;
      $this->spEdit = $spEdit;
      $this->amademenKontrakEdit = $amademenKontrakEdit;
  }
  public function index(Request $request)
  {
    //dd();
    $id = $request->id;
    $type = $request->type;
    $doc = $this->documents->where('documents.id','=',$id);
    $dt = $doc->with('jenis','supplier','pic','boq','lampiran_ttd','latar_belakang','pasal','asuransi','sow_boq','scope_perubahan','users','latar_belakang_surat_pengikatan','latar_belakang_mou')->first();
    if(!$dt || !$this->documents->check_permission_doc($id,$type)){
      abort(404);
    }
    $pic = [];
    $boq = [];
    $lt = [];
    $pasal = [];
    $lampiran = [];
    if($type=='amandemen_sp'){
      $dt->parent_sp = $dt->doc_parent_id;
      $dt->parent_sp_text = $this->documents->select('doc_no')->where('id','=',$dt->doc_parent_id)->first()->doc_no;
    }
    if($type=='sp'){
      $dt->parent_kontrak = $dt->doc_parent_id;
      $dt->parent_kontrak_text = $this->documents->select('doc_no')->where('id','=',$dt->doc_parent_id)->first()->doc_no;
    }
    if(in_array($type,['amandemen_kontrak','adendum','side_letter'])){
      $dt->parent_kontrak = $dt->doc_parent_id;
      $dt->parent_kontrak_text = $this->documents->select('doc_no')->where('id','=',$dt->doc_parent_id)->first()->doc_no;
    }
    if(in_array($type,['khs','turnkey'])){
      if(count($dt->latar_belakang_surat_pengikatan)>0){
        foreach($dt->latar_belakang_surat_pengikatan as $key => $val){
          $dt->f_no_surat_pengikatan = $val->meta_desc;
          $dt->text_surat_pengikatan = $this->documents->select('doc_no')->where('id','=',$val->meta_desc)->first()->doc_no;
        }
      }

      if(count($dt->latar_belakang_mou)>0){
        foreach($dt->latar_belakang_mou as $key => $val){
          $dt->f_no_mou = $val->meta_desc;
          $dt->text_mou = $this->documents->select('doc_no')->where('id','=',$val->meta_desc)->first()->doc_no;
        }
      }      
    }
    if(count($dt->scope_perubahan)>0){
      foreach($dt->scope_perubahan as $key => $val){
        $scop['name'][$key]  = $val->meta_name;
        $scop['awal'][$key]  = $val->meta_title;
        $scop['akhir'][$key] = $val->meta_desc;
        $scop['file'][$key]  = $val->meta_file;
      }
      if($type=='amandemen_sp'){
        $dt->scope_name     = $scop['name'];
        $dt->scope_awal     = $scop['awal'];
        $dt->scope_akhir    = $scop['akhir'];
      }
      else{
        $dt->scope_pasal     = $scop['name'];
        $dt->scope_judul     = $scop['awal'];
        $dt->scope_isi    = $scop['akhir'];
      }

      $dt->scope_file     = $scop['file'];
      $dt->scope_file_old = $scop['file'];
    }
    if(count($dt->pic)>0){
      foreach($dt->pic as $key => $val){
        $pic['pic_posisi'][$key]  = $val->posisi;
        $pic['pic_nama'][$key]    = $val->nama;
        $pic['pic_jabatan'][$key] = $val->jabatan;
        $pic['pic_email'][$key]   = $val->email;
        $pic['pic_telp'][$key]    = $val->telp;
        $pic['pic_id'][$key]      = $val->pegawai_id;
      }
      $dt->pic_posisi = $pic['pic_posisi'];
      $dt->pic_nama   = $pic['pic_nama'];
      $dt->pic_email  = $pic['pic_email'];
      $dt->pic_jabatan= $pic['pic_jabatan'];
      $dt->pic_telp   = $pic['pic_telp'];
      $dt->pic_id     = $pic['pic_id'];
    }
    if(count($dt->boq)>0){
      foreach($dt->boq as $key => $val){
        $boq['hs_kode_item'][$key]  = $val->kode_item;
        $boq['hs_item'][$key]       = $val->item;
        $boq['hs_satuan'][$key]     = $val->satuan;
        $boq['hs_mtu'][$key]        = $val->mtu;
        $boq['hs_harga'][$key]      = $val->harga;
        $boq['hs_qty'][$key]        = $val->qty;
        $boq['hs_keterangan'][$key] = $val->desc;
      }
      $dt->hs_kode_item = $boq['hs_kode_item'];
      $dt->hs_item   = $boq['hs_item'];
      $dt->hs_satuan  = $boq['hs_satuan'];
      $dt->hs_mtu= $boq['hs_mtu'];
      $dt->hs_harga   = $boq['hs_harga'];
      $dt->hs_qty     = $boq['hs_qty'];
      $dt->hs_keterangan     = $boq['hs_keterangan'];
    }
    if(count($dt->asuransi)>0){
      foreach($dt->asuransi as $key => $val){
        $jas['doc_jaminan'][$key]           = $val->doc_jaminan;
        $jas['doc_asuransi'][$key]          = $val->doc_jaminan_name;
        $jas['doc_jaminan_nilai'][$key]     = $val->doc_jaminan_nilai;
        $jas['doc_jaminan_startdate'][$key] = $val->doc_jaminan_startdate;
        $jas['doc_jaminan_enddate'][$key]   = $val->doc_jaminan_enddate;
        $jas['doc_jaminan_desc'][$key]      = $val->doc_jaminan_desc;
        $jas['doc_jaminan_file'][$key]      = $val->doc_jaminan_file;
      }
      $dt->doc_jaminan           = $jas['doc_jaminan'];
      $dt->doc_asuransi          = $jas['doc_asuransi'];
      $dt->doc_jaminan_nilai     = $jas['doc_jaminan_nilai'];
      $dt->doc_jaminan_startdate = $jas['doc_jaminan_startdate'];
      $dt->doc_jaminan_enddate   = $jas['doc_jaminan_enddate'];
      $dt->doc_jaminan_desc      = $jas['doc_jaminan_desc'];
      $dt->doc_jaminan_file      = $jas['doc_jaminan_file'];
      $dt->doc_jaminan_file_old  = $jas['doc_jaminan_file'];
    }
    if(count($dt->latar_belakang)>0){
      foreach($dt->latar_belakang as $key => $val){
        $lt['name'][$key]  = $val->meta_name;
        $lt['desc'][$key]       = $val->meta_desc;
        $lt['file'][$key]     = $val->meta_file;
      }
      $dt->lt_file  = $lt['file'];
      $dt->lt_file_old  = $lt['file'];
      $dt->lt_desc  = $lt['desc'];
      $dt->lt_name  = $lt['name'];
    }

    if(count($dt->pasal)>0){
      foreach($dt->pasal as $key => $val){
        $ps['name'][$key]  = $val->meta_name;
        $ps['desc'][$key]       = $val->meta_desc;
        $ps['title'][$key]     = $val->meta_title;
      }
      $dt->ps_judul      = $ps['name'];
      $dt->ps_isi        = $ps['desc'];
      $dt->ps_judul_new  = $ps['title'];
    }
    if(count($dt->lampiran_ttd)>0){
      foreach($dt->lampiran_ttd as $key => $val){
        $lampiran['file'][$key]  = $val->meta_file;
      }
      $dt->doc_lampiran  = $lampiran['file'];
      $dt->doc_lampiran_old  = $lampiran['file'];
    }
    $dt->doc_po = $dt->doc_po_no;
    $dt->supplier_text = $dt->supplier->bdn_usaha.'.'.$dt->supplier->nm_vendor;
    $dt->konseptor_text = $dt->users->name.' - '.$dt->users->username;
    $data['page_title'] = 'Edit Dokumen';
    $data['doc_type'] = $dt->jenis->type;
    $data['doc'] = $dt;
    $data['pegawai_pihak1'] = \DB::table('pegawai')->where('n_nik',$dt->doc_pihak1_nama)->first();
    $data['pegawai_konseptor'] = \DB::table('users_pegawai as a')
                                ->join('pegawai as b','a.nik','=','b.n_nik')
                                ->where('a.users_id',$dt->user_id)->first();
     // dd($dt->toArray());
    $data['pegawai'] = \App\User::get_user_pegawai();
    $data['action_type'] = 'edit';
    $data['action_url'] = route('doc.storeedit',['type'=>$dt->jenis->type->name,'id'=>$dt->id]);
    $data['data'] = [];
    $data['id'] = $dt->id;
    $data['pegawai_pihak1'] = \DB::table('pegawai')->where('n_nik',$dt->doc_pihak1_nama)->first();
    $data['pegawai_konseptor'] = \DB::table('users_pegawai as a')
                                  ->join('pegawai as b','a.nik','=','b.n_nik')
                                  ->where('a.users_id',$dt->user_id)->first();
    $data['doc_parent'] = \DB::table('documents')->where('id',$dt->doc_parent_id)->first();

    return view('documents::form-edit')->with($data);
  }
  public function store(Request $request)
  {
    
    $id = $request->id;
    $type = $request->type;
    $status = Documents::where('id',$id)->first()->doc_signing;

    if(!$this->documents->check_permission_doc($id ,$type)){
      abort(404);
    }
    if($type=='amandemen_sp'){
      return $this->amandemenSpEdit->store($request);
    }
    if($type=='sp'){
      return $this->spEdit->store($request);
    }
    if($type=='surat_pengikatan'){
      return $this->SuratPengikatanEdit->store($request);
    }
    if($type=='mou'){
      return $this->MouEdit->store($request);
    }
    if(in_array($type,['amandemen_kontrak','adendum','side_letter'])){
      return $this->amademenKontrakEdit->store($request);
    }

    $doc_value = $request->doc_value;
    $request->merge(['doc_value' => Helpers::input_rupiah($request->doc_value)]);

    $m_hs_harga=[];
    $m_hs_qty=[];
    if(isset($request['hs_harga']) && count($request['hs_harga'])>0){
      foreach($request['hs_harga'] as $key => $val){
        $hs_harga[] = $val;
        $m_hs_harga[$key] = Helpers::input_rupiah($val);
      }
    }

    if(count($m_hs_harga)>0){
      $request->merge(['hs_harga'=>$m_hs_harga]);
    }

    if(isset($request['hs_qty']) && count($request['hs_qty'])>0){
      foreach($request['hs_qty'] as $key => $val){
        $hs_qty[$key] = $val;
        $m_hs_qty[$key] = Helpers::input_rupiah($val);
      }
    }

    if(count($m_hs_qty)>0){
      $request->merge(['hs_qty'=>$m_hs_qty]);
    }

    $rules = [];
    $new_lamp_up=[];
    if(in_array($status,['0','2'])){
      $rules['doc_title']        =  'required|max:500|min:5|regex:/^[a-z0-9 .\-]+$/i';
      $rules['doc_desc']         =  'sometimes|nullable|min:30|regex:/^[a-z0-9 .\-\,\_\'\&\%\!\?\"\:\+\(\)\@\#\/]+$/i';
      $rules['doc_template_id']  =  'required|min:1|max:20|regex:/^[0-9]+$/i';
      $rules['doc_startdate']    =  'required|date_format:"Y-m-d"';
      $rules['doc_enddate']      =  'required|date_format:"Y-m-d"';
      $rules['doc_pihak1']       =  'required|min:5|max:500|regex:/^[a-z0-9 .\-\,\_\'\&\%\!\?\"\:\+\(\)\@\#\/]+$/i';
      $rules['supplier_id']      =  'required|min:1|max:20|regex:/^[0-9]+$/i';
      $rules['doc_pihak1_nama']  =  'required|min:5|max:500|regex:/^[a-z0-9 .\-\,\_\'\&\%\!\?\"\:\+\(\)\@\#\/]+$/i';
      $rules['doc_pihak2_nama']  =  'required|min:5|max:500|regex:/^[a-z0-9 .\-\,\_\'\&\%\!\?\"\:\+\(\)\@\#\/]+$/i';
      $rules['doc_proc_process'] =  'required|min:1|max:20|regex:/^[a-z0-9 .\-]+$/i';
      $rules['doc_mtu']          =  'required|min:1|max:20|regex:/^[a-z0-9 .\-]+$/i';
      if($type!='khs'){
        $rules['doc_value']        =  'required|max:500|min:3|regex:/^[0-9 .]+$/i';
      }

      $check_new_lampiran = false;
      foreach($request->doc_lampiran_old as $k => $v){
        if(isset($request->doc_lampiran[$k]) && is_object($request->doc_lampiran[$k]) && !empty($v)){//jika ada file baru
          $new_lamp[] = '';
          $new_lamp_up[] = $request->doc_lampiran[$k];
          $rules['doc_lampiran.'.$k] = 'required|mimes:pdf';
        }
        else if(empty($v)){
          $rules['doc_lampiran.'.$k] = 'required|mimes:pdf';
          if(!isset($request->doc_lampiran[$k])){
            $new_lamp[] = $v;
            $new_lamp_up[] = $v;
          }
          else{
            $new_lamp[] = '';
            $new_lamp_up[] = $request->doc_lampiran[$k];
          }
        }
        else{
          $new_lamp[] = $v;
          $new_lamp_up[] = $v;
        }
      }
      $request->merge(['doc_lampiran' => $new_lamp]);
    }

    $rules['doc_sow']          =  'sometimes|nullable|min:30|regex:/^[a-z0-9 .\-\,\_\'\&\%\!\?\"\:\+\(\)\@\#\/]+$/i';
    $rules['hs_kode_item.*']   =  'sometimes|nullable|max:500|min:5|regex:/^[a-z0-9 .\-]+$/i';
    $rules['hs_item.*']        =  'sometimes|nullable|max:500|min:5|regex:/^[a-z0-9 .\-]+$/i';
    $rules['hs_satuan.*']      =  'sometimes|nullable|max:50|min:2|regex:/^[a-z0-9 .\-]+$/i';
    $rules['hs_mtu.*']         =  'sometimes|nullable|max:5|min:1|regex:/^[a-z0-9 .\-]+$/i';
    $rules['hs_harga.*']       =  'sometimes|nullable|max:500|min:1|regex:/^[0-9 .]+$/i';
    $rules['hs_qty.*']         =  'sometimes|nullable|max:500|min:1|regex:/^[0-9 .]+$/i';
    $rules['hs_keterangan.*']  =  'sometimes|nullable|max:500|regex:/^[a-z0-9 .\-]+$/i';
    if(\Laratrust::hasRole('admin')){
      $rules['user_id']        =  'required|min:1|max:20|regex:/^[0-9]+$/i';
    }

    if(in_array($type,['turnkey','sp'])){
        $rule_doc_jaminan = (count($request['doc_jaminan'])>1)?'required':'sometimes|nullable';
        $rule_doc_asuransi = (count($request['doc_asuransi'])>1)?'required':'sometimes|nullable';
        $rule_doc_jaminan_nilai = (count($request['doc_jaminan_nilai'])>1)?'required':'sometimes|nullable';
        $rule_doc_jaminan_startdate = (count($request['doc_jaminan_startdate'])>1)?'required':'sometimes|nullable';
        $rule_doc_jaminan_enddate = (count($request['doc_jaminan_enddate'])>1)?'required':'sometimes|nullable';
        $rules['doc_jaminan.*']           = $rule_doc_jaminan.'|in:PL,PM';
        $rules['doc_asuransi.*']          = $rule_doc_asuransi.'|max:500|min:5|regex:/^[a-z0-9 .\-]+$/i';
        $rules['doc_jaminan_nilai.*']     = $rule_doc_jaminan_nilai.'|max:500|min:3|regex:/^[0-9 .]+$/i';
        $rules['doc_jaminan_startdate.*'] = $rule_doc_jaminan_startdate.'|date_format:"Y-m-d"'; //|date_format:"Y-m-d"
        $rules['doc_jaminan_enddate.*']   = $rule_doc_jaminan_enddate.'|date_format:"Y-m-d"'; //
        $rules['doc_jaminan_desc.*']      = 'sometimes|nullable|regex:/^[a-z0-9 .\-\,\_\'\&\%\!\?\"\:\+\(\)\@\#\/]+$/i';

        $rules['doc_po']                  = 'sometimes|nullable|po_exists|regex:/^[a-z0-9 .\-]+$/i';
        foreach($request->doc_jaminan_file_old as $k => $v){
          if(isset($request->doc_jaminan_file[$k]) && is_object($request->doc_jaminan_file[$k]) && !empty($v)){//jika ada file baru
            $new_jfile[] = '';
            $new_jfile_up[] = $request->doc_jaminan_file[$k];
            $rules['doc_jaminan_file.'.$k] = 'sometimes|nullable|mimes:pdf';
          }
          else if(empty($v)){
            $rules['doc_jaminan_file.'.$k] = 'sometimes|nullable|mimes:pdf';
            if(!isset($request->doc_jaminan_file[$k])){
              $new_jfile[] = $v;
              $new_jfile_up[] = $v;
            }
            else{
              $new_jfile[] = '';
              $new_jfile_up[] = $request->doc_jaminan_file[$k];
            }
          }
          else{
            $new_jfile[] = $v;
            $new_jfile_up[] = $v;
          }
        }
        $request->merge(['doc_jaminan_file' => $new_jfile]);
    }

    $rules['lt_desc.*']  =  'required|regex:/^[a-z0-9 .\-\,\_\'\&\%\!\?\"\:\+\(\)\@\#\/]+$/i';
    $rules['lt_name.*']  =  'required|max:500|regex:/^[a-z0-9 .\-]+$/i';

    $check_new_lt_file = false;
    foreach($request->lt_file_old as $k => $v){
      if(isset($request->lt_file[$k]) && is_object($request->lt_file[$k]) && !empty($v)){//jika ada file baru
        $new_lt_file[] = '';
        $new_lt_file_up[] = $request->lt_file[$k];
        $rules['lt_file.'.$k]  =  'required|mimes:pdf';
      }
      else if(empty($v)){
        $rules['lt_file.'.$k]  =  'required|mimes:pdf';
        if(!isset($request->lt_file[$k])){
          $new_lt_file[] = $v;
          $new_lt_file_up[] = $v;
        }
        else{
          $new_lt_file[] = '';
          $new_lt_file_up[] = $request->lt_file[$k];
        }
      }
      else{
        $new_lt_file[] = $v;
        $new_lt_file_up[] = $v;
      }
    }
    $request->merge(['lt_file' => $new_lt_file]);

    $rule_ps_judul = (count($request['ps_judul'])>1)?'required':'sometimes|nullable';
    $rule_ps_isi = (count($request['ps_isi'])>1)?'required':'sometimes|nullable';
    $rules['ps_judul.*']      =  $rule_ps_judul.'|in:Jangka Waktu Penerbitan Surat Pesanan,Jangka Waktu Penyerahan Pekerjaan,Tata Cara Pembayaran,Tanggal Efektif dan Masa Laku Perjanjian,Jaminan Pelaksanaan,Jaminan Uang Muka,Jaminan Pemeliharaan,Masa Laku Jaminan,Harga Kontrak,Lainnya';
    $rules['ps_isi.*']        =  $rule_ps_isi.'|regex:/^[a-z0-9 .\-\,\_\'\&\%\!\?\"\:\+\(\)\@\#\/]+$/i';

    foreach($request->ps_judul as $k => $v){
      if(isset($request->ps_judul[$k]) && $request->ps_judul[$k]=="Lainnya" && !empty($v)){//jika ada file baru
        $new_pasal[] = $request->ps_judul_new[$k];
        $rules['ps_judul_new.'.$k] = 'required|max:500|min:5|regex:/^[a-z0-9 .\-]+$/i';
      }
      else{
        $new_pasal[] = $v;
      }
    }
    $request->merge(['ps_judul_new' => $new_pasal]);

    

    $validator = Validator::make($request->all(), $rules,\App\Helpers\CustomErrors::documents());
    
    if(in_array($status,['0','2'])){
      $rules['pic_posisi.*']    =  'required|max:500|min:2|regex:/^[a-z0-9 .\-]+$/i';
      $validator->after(function ($validator) use ($request) {
          if (!isset($request['pic_nama'][0])) {
              $validator->errors()->add('pic_nama_err', 'Unit Penanggung jawab harus dipilih!');
          }
      });
    }

    $request->merge(['doc_value' => $doc_value]);
    if(isset($hs_harga) && count($hs_harga)>0){
      $request->merge(['hs_harga'=>$hs_harga]);
    }
    if(isset($hs_qty) && count($hs_qty)>0){
      $request->merge(['hs_qty'=>$hs_qty]);
    }
    if ($validator->fails ()){
      return redirect()->back()
                  ->withInput($request->input())
                  ->withErrors($validator);
    }

    if(in_array($status,['0','2'])){
      $doc = Documents::where('id',$id)->first();
      $doc->doc_title = $request->doc_title;
      $doc->doc_desc = $request->doc_desc;
      $doc->doc_template_id = $request->doc_template_id;
      $doc->doc_date = $request->doc_startdate;
      $doc->doc_startdate = $request->doc_startdate;
      $doc->doc_enddate = $request->doc_enddate;
      $doc->doc_pihak1 = $request->doc_pihak1;
      $doc->doc_pihak1_nama = $request->doc_pihak1_nama;
      $doc->doc_pihak2_nama = $request->doc_pihak2_nama;
      $doc->doc_signing = intval($request->statusButton);
      if((\Laratrust::hasRole('admin'))){
        $doc->user_id = $request->user_id;
      }
      $doc->supplier_id = $request->supplier_id;
      if(in_array($type,['turnkey','sp'])){
        $doc->doc_po_no = $request->doc_po;
      }
      $doc->doc_proc_process = $request->doc_proc_process;
      $doc->doc_mtu = $request->doc_mtu;
      $doc->doc_value = Helpers::input_rupiah($request->doc_value);
      $doc->doc_sow = $request->doc_sow;
      $doc->doc_data = Helpers::json_input($doc->doc_data,['edited_by'=>\Auth::id()]);
      $doc->save();
    }else{
      $doc = Documents::where('id',$id)->first();
      $doc->doc_sow = $request->doc_sow;
      $doc->save();
    }

    if(count($request->ps_judul)>0){
      DocMeta::where([
        ['documents_id','=',$doc->id],
        ['meta_type','=','pasal_pasal'],
        ])->delete();
      foreach($request->ps_judul as $key => $val){

        if(!empty($val)
        ){
          $doc_meta2 = new DocMeta();
          $doc_meta2->documents_id = $doc->id;
          $doc_meta2->meta_type = 'pasal_pasal';
          $doc_meta2->meta_name = $val;
          $doc_meta2->meta_title =$request['ps_judul_new'][$key];
          $doc_meta2->meta_desc = $request['ps_isi'][$key];
          $doc_meta2->save();

          //dd($doc_meta2);
        }
      }
    }

    if(count($new_lamp_up)>0){
      DocMeta::where([
        ['documents_id','=',$doc->id],
        ['meta_type','=','lampiran_ttd']
        ])->delete();
      foreach($new_lamp_up as $key => $val){
        if(!empty($val)){
          $doc_meta = new DocMeta();
          $doc_meta->documents_id = $doc->id;
          $doc_meta->meta_type = 'lampiran_ttd';
          if(is_object($val)){
            $fileName   = Helpers::set_filename('doc_lampiran_',strtolower($val));
            $file = $request['doc_lampiran'][$key];
            $file->storeAs('document/'.$request->type.'_lampiran_ttd', $fileName);
            $doc_meta->meta_file = $fileName;
          }
          else{
            $doc_meta->meta_file = $val;
          }
          $doc_meta->save();
        }
      }
    }

    if(in_array($type,['turnkey','khs'])){
      if(isset($request->f_no_surat_pengikatan)){
        DocMeta::where([
          ['documents_id','=',$doc->id],
          ['meta_type','=','latar_belakang_surat_pengikatan']
          ])->delete();
        $doc_meta = new DocMeta();
        $doc_meta->documents_id = $doc->id;
        $doc_meta->meta_type = "latar_belakang_surat_pengikatan";
        $doc_meta->meta_title = "Latar Belakang Surat Pengikatan";
        $doc_meta->meta_desc = $request->f_no_surat_pengikatan;
        $doc_meta->save();
      }

      if(isset($request->f_no_mou)){
        DocMeta::where([
          ['documents_id','=',$doc->id],
          ['meta_type','=','latar_belakang_mou']
          ])->delete();
        $doc_meta = new DocMeta();
        $doc_meta->documents_id = $doc->id;
        $doc_meta->meta_type = "latar_belakang_mou";
        $doc_meta->meta_title = "Latar Belakang Mou";
        $doc_meta->meta_desc = $request->f_no_mou;
        $doc_meta->save();
      }
    }

    if(in_array($type,['turnkey','sp'])){
      if(count($request['doc_jaminan'])>0){
        DocAsuransi::where([
          ['documents_id','=',$doc->id]
          ])->delete();
        foreach($request['doc_jaminan'] as $key => $val){
          if(!empty($request['doc_jaminan'][$key])
              && !empty($request['doc_asuransi'][$key])
              && !empty($request['doc_jaminan_nilai'][$key])
              && !empty($request['doc_jaminan_startdate'][$key])
              && !empty($request['doc_jaminan_enddate'][$key])
          ){
            $asr = new DocAsuransi();
            $asr->documents_id = $doc->id;
            $asr->doc_jaminan = $request['doc_jaminan'][$key];
            $asr->doc_jaminan_name = $request['doc_asuransi'][$key];
            $asr->doc_jaminan_nilai = Helpers::input_rupiah($request['doc_jaminan_nilai'][$key]);
            $asr->doc_jaminan_startdate = $request['doc_jaminan_startdate'][$key];
            $asr->doc_jaminan_enddate = $request['doc_jaminan_enddate'][$key];
            $asr->doc_jaminan_desc = $request['doc_jaminan_desc'][$key];
            // dd($asr);
            if(is_object($new_jfile_up[$key])){
              $fileName   = Helpers::set_filename('doc_',strtolower($val));
              $file = $new_jfile_up[$key];
              $file->storeAs('document/'.$request->type.'_asuransi', $fileName);
              $asr->doc_jaminan_file = $fileName;
            }
            else{
              $asr->doc_jaminan_file = $new_jfile_up[$key];
            }
            $asr->save();
          }
        }
      }
    }

    if(count($request->pic_nama)>0){
      DocPic::where([
        ['documents_id','=',$doc->id]
        ])->delete();
      foreach($request['pic_nama'] as $key => $val){
        if(!empty($request['pic_email'][$key])
            && !empty($request['pic_jabatan'][$key])
            && !empty($request['pic_posisi'][$key])
        ){
          $pic = new DocPic();
          $pic->documents_id = $doc->id;
          $pic->pegawai_id = $request['pic_id'][$key];
          $pic->nama = $val;
          $pic->email = $request['pic_email'][$key];
          $pic->jabatan = $request['pic_jabatan'][$key];
          $pic->telp = $request['pic_telp'][$key];
          $pic->posisi = $request['pic_posisi'][$key];
          $pic->save();
        }
      }
    }

    if(count($request['lt_name'])>0){
      DocMeta::where([
        ['documents_id','=',$doc->id],
        ['meta_type','=','latar_belakang']
        ])->delete();
      foreach($request['lt_name'] as $key => $val){
        if(!empty($request['lt_name'][$key])
            && !empty($request['lt_desc'][$key])
        ){
          $doc_meta = new DocMeta();
          $doc_meta->documents_id = $doc->id;
          $doc_meta->meta_type = 'latar_belakang';
          $doc_meta->meta_name = $request['lt_name'][$key];
          $doc_meta->meta_desc = $request['lt_desc'][$key];
          if(is_object($new_lt_file_up[$key])){
            $fileName   = Helpers::set_filename('doc_',strtolower($request['lt_name'][$key]));
            $file = $new_lt_file_up[$key];
            $file->storeAs('document/'.$request->type.'_latar_belakang', $fileName);
            $doc_meta->meta_file = $fileName;
          }
          else{
            $doc_meta->meta_file = $new_lt_file_up[$key];
          }
          $doc_meta->save();
        }
      }
    }

    if(count($request->hs_harga)>0){
      DocBoq::where([
        ['documents_id','=',$doc->id]
        ])->delete();
      foreach($request->hs_harga as $key => $val){
        if(!empty($val)
            && !empty($request['hs_kode_item'][$key])
            && !empty($request['hs_item'][$key])
            && !empty($request['hs_satuan'][$key])
            && !empty($request['hs_mtu'][$key])
        ){
          $doc_boq = new DocBoq();
          $doc_boq->documents_id = $doc->id;
          $doc_boq->kode_item = $request['hs_kode_item'][$key];
          $doc_boq->item = $request['hs_item'][$key];
          $doc_boq->satuan = $request['hs_satuan'][$key];
          $doc_boq->mtu = $request['hs_mtu'][$key];
          $q_harga = Helpers::input_rupiah($val);
          $doc_boq->harga = Helpers::input_rupiah($q_harga);
          $hs_type = 'harga_satuan';
          if(in_array($type,['turnkey','sp'])){
            $q_qty = Helpers::input_rupiah($request['hs_qty'][$key]);
            $q_total = $q_qty*$q_harga;
            $doc_boq->qty = $q_qty;
            $doc_boq->harga_total = $q_total;
            $hs_type = 'boq';
          }
          $doc_boq->desc = $request['hs_keterangan'][$key];
          $doc_boq->data = json_encode(array('type'=>$hs_type));
          $doc_boq->save();
        }
      }
    }

    $request->session()->flash('alert-success', 'Data berhasil disimpan');
    if($request->statusButton == '0'){
      return redirect()->route('doc',['status'=>'tracking']);
    }else{
      return redirect()->route('doc',['status'=>'draft']);
    }
  }
}
