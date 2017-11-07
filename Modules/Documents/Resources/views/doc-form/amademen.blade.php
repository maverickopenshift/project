<div class="box">
    <div class="box-header with-border">
      <h3 class="box-title">
          General Info
      </h3>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
      <div class="form-horizontal">
          @include('documents::doc-form.no-sp')
          <div class="form-group {{ $errors->has('doc_date') ? ' has-error' : '' }}">
            <label for="akte_awal_tg" class="col-sm-2 control-label"><span class="text-red">*</span> Tanggal {{$doc_type['title']}}</label>
            <div class="col-sm-3">
              <div class="input-group date" data-provide="datepicker">
                  <div class="input-group-addon">
                      <span class="fa fa-calendar"></span>
                  </div>
                  <input type="text" class="form-control" name="doc_date" value="{{old('doc_date',Helper::prop_exists($doc,'doc_date'))}}" autocomplete="off">
              </div>
            </div>
            <div class="col-sm-10 col-sm-offset-2">
              {!!Helper::error_help($errors,'doc_date')!!}
            </div>
          </div>
          <div class="form-group {{ $errors->has('doc_pihak1') ? ' has-error' : '' }}">
            <label for="akte_awal_tg" class="col-sm-2 control-label"><span class="text-red">*</span> Pihak I</label>
            <div class="col-sm-6">
              <input type="text" class="form-control" name="doc_pihak1" id="pihak1" value="{{old('doc_pihak1',Helper::prop_exists($doc,'doc_pihak1'))}}" autocomplete="off">
            </div>
            <div class="col-sm-10 col-sm-offset-2">
              {!!Helper::error_help($errors,'doc_pihak1')!!}
            </div>
          </div>
          <div class="form-group {{ $errors->has('doc_pihak1_nama') ? ' has-error' : '' }}">
            <label for="ttd_pihak1" class="col-sm-2 control-label"><span class="text-red">*</span>Penandatangan Pihak I</label>
            <div class="col-sm-6">
              <input type="text" class="form-control" name="doc_pihak1_nama" value="{{old('doc_pihak1_nama',Helper::prop_exists($doc,'doc_pihak1_nama'))}}"  placeholder="Masukan Nama Penandatanganan Pihak I" autocomplete="off">
            </div>
            <div class="col-sm-10 col-sm-offset-2">
              {!!Helper::error_help($errors,'doc_pihak1_nama')!!}
            </div>
          </div>
          <div class="form-group {{ $errors->has('supplier_id') ? ' has-error' : '' }}">
            <label for="akte_awal_tg" class="col-sm-2 control-label"><span class="text-red">*</span> Pihak II</label>
            <div class="col-sm-6">
              <input type="hidden" class="select-user-vendor-text" name="supplier_text" value="{{old('supplier_text',Helper::prop_exists($doc,'supplier_text'))}}">
              <select class="form-control select-user-vendor" style="width: 100%;" name="supplier_id"  data-id="{{Helper::old_prop($doc,'supplier_id')}}">
                  <option value="">Pilih Pihak II</option>
              </select>
            </div>
            <div class="col-sm-10 col-sm-offset-2">
              {!!Helper::error_help($errors,'supplier_id')!!}
            </div>
          </div>
          <div class="form-group {{ $errors->has('doc_pihak2_nama') ? ' has-error' : '' }}">
            <label for="ttd_pihak2" class="col-sm-2 control-label"><span class="text-red">*</span>Penandatangan Pihak II</label>
            <div class="col-sm-6">
              <input type="text" class="form-control" name="doc_pihak2_nama"  value="{{old('doc_pihak2_nama',Helper::prop_exists($doc,'doc_pihak2_nama'))}}"  placeholder="Masukan Nama Penandatanganan Pihak II" autocomplete="off">
            </div>
            <div class="col-sm-10 col-sm-offset-2">
              {!!Helper::error_help($errors,'doc_pihak2_nama')!!}
            </div>
          </div>
          {{-- @include('documents::partials.buttons') --}}
      </div>
    </div>
<!-- /.box-body -->
</div>
@push('scripts')
<script>
$(function() {
  var selectUserVendor = $(".select-user-vendor").select2({
      placeholder : "Pilih Pihak II....",
      ajax: {
          url: '{!! route('supplier.get-select') !!}',
          dataType: 'json',
          delay: 350,
          data: function (params) {
              return {
                  q: params.term, // search term
                  page: params.page
              };
          },
          //id: function(data){ return data.store_id; },
          processResults: function (data, params) {
              // parse the results into the format expected by Select2
              // since we are using custom formatting functions we do not need to
              // alter the remote JSON data, except to indicate that infinite
              // scrolling can be used

              var results = [];

              $.each(data.data, function (i, v) {
                  var o = {};
                  o.id = v.id;
                  o.name = v.nm_vendor;
                  o.value = v.id;
                  o.username = v.kd_vendor;
                  o.bdn_usaha = v.bdn_usaha;
                  results.push(o);
              })
              params.page = params.page || 1;
              return {
                  results: results,
                  pagination: {
                      more: (data.next_page_url ? true: false)
                  }
              };
          },
          cache: true
      },
      //escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
      minimumInputLength: 0,
      templateResult: templateResultVendor,
      templateSelection: templateSelectionVendor
  });
  function templateResultVendor(state) {
    if (state.id === undefined || state.id === "") { return ; }
    var $state = $(
        '<span>' +  state.bdn_usaha+'.'+state.name +' <i>('+  state.username + ')</i></span>'
    );
    return $state;
  }
  function templateSelectionVendor(data) {
    if (data.id === undefined || data.id === "") { // adjust for custom placeholder values
        return;
    }
    var render = data.bdn_usaha+'.'+data.name +' - '+  data.username ;
    if(data.bdn_usaha === undefined){
      render = $('.select-user-vendor :selected').text();
    }
    $('.select-user-vendor-text').val(render);
    return render ;
  }

  var user_vendor = $(".select-user-vendor");
  if(user_vendor.data('id')!==""){
    var newOption = new Option($(".select-user-vendor-text").val(), user_vendor.data('id'), false, true);
    user_vendor.append(newOption);
    user_vendor.val(user_vendor.data('id')).change();
  }
})
</script>
@endpush
