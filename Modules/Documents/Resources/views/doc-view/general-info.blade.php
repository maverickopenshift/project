<div class="box">
    <div class="box-header with-border">
      <h3 class="box-title">
          General Info
      </h3>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
      <div class="form-horizontal">
          <div class="form-group ">
            <label class="col-sm-2 control-label">No.Kontrak</label>
            <div class="col-sm-10 text-me">{{$doc->doc_no or '-'}}</div>
          </div>
          <div class="form-group ">
            <label class="col-sm-2 control-label">Judul {{$doc_type['title']}}</label>
            <div class="col-sm-10 text-me">{{$doc->doc_title}}</div>
          </div>
          <div class="form-group">
            <label for="deskripsi_kontrak" class="col-sm-2 control-label">Deskripsi {{$doc_type['title']}}</label>
            <div class="col-sm-10 text-me">{{$doc->doc_desc}}</div>
          </div>
          <div class="form-group">
            <label for="akte_awal_tg" class="col-sm-2 control-label">Jenis {{$doc_type['title']}}</label>
            <div class="col-sm-10 text-me">{{$doc->jenis->category->title}}</div>
          </div>
          @if($doc_type->name=="sp")
            <div class="form-group">
              <label for="akte_awal_tg" class="col-sm-2 control-label">Tanggal Mulai {{$doc_type['title']}}</label>
              <div class="col-sm-10 text-me">{{Carbon\Carbon::parse($doc->doc_startdate)->format('l, d F Y')}}</div>
            </div>
            <div class="form-group">
              <label for="akte_awal_tg" class="col-sm-2 control-label">Tanggal Akhir {{$doc_type['title']}}</label>
              <div class="col-sm-10 text-me">{{Carbon\Carbon::parse($doc->doc_enddate)->format('l, d F Y')}}</div>
            </div>
          @else
            <div class="form-group">
              <label class="col-sm-2 control-label">Tanggal {{$doc_type['title']}}</label>
              <div class="col-sm-10 text-me">{{Carbon\Carbon::parse($doc->doc_date)->format('l, d F Y')}}</div>
            </div>
          @endif
          <div class="form-group">
            <label class="col-sm-2 control-label">Pihak I</label>
            <div class="col-sm-10 text-me">{{$doc->doc_pihak1}}</div>
          </div>
          <div class="form-group">
            <label for="ttd_pihak1" class="col-sm-2 control-label">Penandatangan Pihak I</label>
            <div class="col-sm-10 text-me">{{$doc->doc_pihak1_nama}}</div>
          </div>
          <div class="form-group">
            <label for="akte_awal_tg" class="col-sm-2 control-label">Pihak II</label>
            <div class="col-sm-10 text-me">{{$doc->supplier->bdn_usaha.'.'.$doc->supplier->nm_vendor}}</div>
          </div>
          <div class="form-group">
            <label for="ttd_pihak2" class="col-sm-2 control-label">Penandatangan Pihak II</label>
            <div class="col-sm-10 text-me">{{$doc->doc_pihak2_nama}}</div>
          </div>
          <div class="form-group">
            <label for="ttd_pihak2" class="col-sm-2 control-label">Lampiran 1 <br/><small style="font-weight:normal" class="text-info"><i>(Lembar Tanda Tangan)</i></small></label>
            <div class="col-sm-10 text-me">
              @if(!empty($doc->doc_lampiran))
                  <a class="btn btn-primary btn-sm" target="_blank" href="{{route('doc.file',['filename'=>$doc->doc_lampiran,'type'=>$doc_type['name']])}}"><i class="glyphicon glyphicon-paperclip"></i> Lihat Lampiran</a>
              @endif
            </div>
          </div>
          @if($doc_type->name!="sp")
            <div class="form-group">
              <label for="prinsipal_st" class="col-sm-2 control-label">Cara Pengadaan</label>
              <div class="col-sm-10 text-me">
                {{($doc->doc_proc_process=='P')?'Pelanggan':''}}
                {{($doc->doc_proc_process=='PL')?'Pemilihan Langsung':''}}
                {{($doc->doc_proc_process=='TL')?'Penunjukan Langsung':''}}
              </div>
            </div>
          @endif
            <div class="form-group">
              <label class="col-sm-2 control-label">Mata Uang</label>
              <div class="col-sm-10 text-me">{{$doc->doc_mtu}}</div>
            </div>
          @if($doc_type->name!="sp")
            <div class="form-group">
              <label for="bdn_usaha" class="col-sm-2 control-label">Nilai Kontrak</label>
              <div class="col-sm-10 text-me">{{$doc->doc_value}}</div>
            </div>
          @endif
          @if($doc_type->name=="sp")
            <div class="form-group">
              <label for="ttd_pihak2" class="col-sm-2 control-label">Nilai SP</label>
              <div class="col-sm-10">
                <table class="table table-bordered table-latar">
                  <thead>
                  <tr>
                    <th>Material</th>
                    <th>Jasa</th>
                    <th>Total</th>
                    <th>PPN</th>
                    <th>Total PPN</th>
                  </tr>
                </thead>
                <tbody>
                    <tr>
                      <td>
                        <div class="input-group {{ $errors->has('doc_nilai_material') ? ' has-error' : '' }}">
                          <span class="input-group-addon mtu-set"></span>
                          <input type="text" class="form-control" name="doc_nilai_material" value="{{Helper::old_prop($doc,'doc_nilai_material')}}" autocomplete="off">
                        </div>
                          {!!Helper::error_help($errors,'doc_nilai_material')!!}
                      </td>
                      <td>
                        <div class="input-group {{ $errors->has('doc_nilai_jasa') ? ' has-error' : '' }}">
                          <span class="input-group-addon mtu-set"></span>
                          <input type="text" class="form-control" name="doc_nilai_jasa" value="{{Helper::old_prop($doc,'doc_nilai_jasa')}}"  autocomplete="off">
                        </div>
                          {!!Helper::error_help($errors,'doc_nilai_jasa')!!}
                      </td>
                      <td>
                        <div class="input-group {{ $errors->has('doc_nilai_total') ? ' has-error' : '' }}">
                          <span class="input-group-addon mtu-set"></span>
                          <input type="text" class="form-control" name="doc_nilai_total" value="{{Helper::old_prop($doc,'doc_nilai_total')}}" autocomplete="off">
                        </div>
                          {!!Helper::error_help($errors,'doc_nilai_total')!!}
                      </td>
                      <td>
                        <div class="input-group {{ $errors->has('doc_nilai_ppn') ? ' has-error' : '' }}">
                          <span class="input-group-addon mtu-set"></span>
                          <input type="text" class="form-control" name="doc_nilai_ppn" value="{{Helper::old_prop($doc,'doc_nilai_ppn')}}" autocomplete="off">
                        </div>
                          {!!Helper::error_help($errors,'doc_nilai_ppn')!!}
                      </td>
                      <td>
                        <div class="input-group {{ $errors->has('doc_nilai_total_ppn') ? ' has-error' : '' }}">
                          <span class="input-group-addon mtu-set"></span>
                          <input type="text" class="form-control" name="doc_nilai_total_ppn" value="{{Helper::old_prop($doc,'doc_nilai_total_ppn')}}" autocomplete="off">
                        </div>
                          {!!Helper::error_help($errors,'doc_nilai_total_ppn')!!}
                      </td>
                    </tr>
                </tbody>
                </table>
              </div>
            </div>
          @endif
          <div class="form-group">
            <label for="prinsipal_st" class="col-sm-2 control-label">Unit Penanggungjawab PIC</label>
            <div class="col-sm-10">          
              <div class="parent-pictable">
                  <table class="table table-condensed table-striped">
                      <thead>
                      <tr>
                          <th width="40">No.</th>
                          <th  width="100">NIK</th>
                          <th  width="350">Nama</th>
                          <th>Posisi</th>
                      </tr>
                      </thead>
                      <tbody>
                        @foreach ($doc->pic as $key=>$dt)
                          <tr>
                            <td>{{($key+1)}}</td>
                            <td>{{($dt->pegawai->n_nik)}}</td>
                            <td>{{($dt->pegawai->v_nama_karyawan)}}</td>
                            <td>{{($dt->pegawai->v_short_posisi)}}</td>
                          </tr>
                        @endforeach
                      </tbody>
                  </table>
                </div>
            </div>
          </div>

          @if($doc_type->name=="turnkey" || $doc_type->name=="sp")
          <div class="form-group ">
            <label class="col-sm-2 control-label">No.PO</label>
            <div class="col-sm-10 text-me">{{$doc->doc_po_no}}</div>
          </div>
          <div class="form-group ">
            <label class="col-sm-2 control-label">Nama Pembuat</label>
            <div class="col-sm-10 text-me">{{$doc->doc_po_name}}</div>
          </div>
          <div class="form-group ">
            <label class="col-sm-2 control-label">Tanggal Buat</label>
            <div class="col-sm-10 text-me">{{$doc->doc_po_tgl}}</div>
          </div>
          @endif
          {{-- @include('documents::partials.buttons') --}}
      </div>
    </div>
<!-- /.box-body -->
</div>
@push('scripts')
<script>
$(function() {

});
</script>
@endpush