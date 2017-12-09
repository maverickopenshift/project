<div class="box">
    <div class="box-header with-border">
      <h3 class="box-title">
          General Info
      </h3>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
      @include('documents::doc-form-edit.general-info-left')
      @include('documents::partials.button-edit')
    </div>
<!-- /.box-body -->
</div>
@push('scripts')
<script src="{{ mix('js/po_sap.js') }}"></script>
<script>
$(function() {
  // add_select('doc_lampiran');
  // delete_select('doc_lampiran');
  var po = $('.no_po').val()
  if(po!=="" || po!==undefined){
    render_po(po);
  }

  $('.mtu-set').text($('.mata-uang').val());

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
      escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
      minimumInputLength: 0,
      templateResult: templateResultVendor,
      templateSelection: templateSelectionVendor
  });
  function templateResultVendor(state) {
    if (state.id === undefined || state.id === "") { return '<img src="/images/loader.gif" style="width:20px;"/> Searching....' ; }
    var $state = $(
        '<span>' +  state.bdn_usaha+'.'+state.name +' <i>('+  state.username + ')</i></span>'
    );
    return $state;
  }
  function templateSelectionVendor(data) {
    if (data.id === undefined || data.id === "") { // adjust for custom placeholder values
        return "Cari Pihak II....";
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
  $(document).on('click', '.cari-po', function(event) {
    event.preventDefault();
    /* Act on the event */
    var po = $('.no_po').val(),error_po = $('.error-po');
    error_po.html('<img src="{!!asset('/images/loader.gif')!!}" title="please wait..." width="25"/>');
    if(po==""){
      error_po.html('No.PO harus diisi');
      return false;
    }
    render_po(po);
  });
  @php
    $pic=Helper::old_prop($doc,'pic_data');
    $pic_posisi=Helper::old_prop_each($doc,'pic_posisi');
  @endphp
  @if($pic)
      @if(count($pic)>0)
        var table = $('#pictable');
        var loading = table.find('.loading-tr');
        loading.hide();
        var tr_count = table.find('tbody>tr').not('tbody>tr.loading-tr');
        table.parent().show();
        var tr;
        @foreach ($pic as $key=>$value)
          @php
              echo 'var errornya = \''.trim(preg_replace('/\s+/', ' ',Helper::error_help($errors,'pic_posisi.'.$key))).'\';';
              echo 'var data_json=JSON.parse(decodeURIComponent("'.$value.'"));tr += templatePIC(data_json,\''.$pic_posisi[$key].'\',errornya);';
          @endphp
        @endforeach
        table.find('tbody').append(tr);
      @endif
  @endif
});
var tr_pic =[];

$(document).on('change', '#pict', function(event) {
  event.preventDefault();
  /* Act on the event */
  var nik = $(this).val();
  $(this).val('');
  $('#select2-pict-container').html('');
  var table = $('#pictable');
  var loading = table.find('.loading-tr');
  var render = true;
  $.each(table.find('.data-nik'),function(index, el) {
    if($(this).text()==nik){
      render = false;
    }
  });
  if(render){
    loading.show();
    var tr_count = table.find('tbody>tr').not('tbody>tr.loading-tr');
    table.parent().show();
    $.ajax({
      url: '{!! route('users.get-select-user-telkom-by-nik') !!}',
      type: 'GET',
      dataType: 'json',
      data: {nik: nik}
    })
    .done(function(response) {
      if(response.status){
        var data = response.data;
        loading.hide();
        var pic_data = {
          no     : (tr_count.length+1),
          id     : data.id,
          nik    : data.n_nik,
          nama   : data.v_nama_karyawan,
          posisi : (data.v_short_posisi==null?"-":data.v_short_posisi)
        }
        var tr = templatePIC(pic_data);
        table.find('tbody').append(tr);
      }
    });
  }
});
function templatePIC(pic_data,pic_posisi,thiserror) {
  if(pic_posisi===undefined){
    pic_posisi = "";
  }
  var has_error = 'class="has-error"';
  if(thiserror===undefined || thiserror===""){
    thiserror = "";
    has_error = '';
  }
  var json_render =encodeURIComponent(JSON.stringify(pic_data));
  return '<tr><td class="data-no">'+pic_data.no+'<input type="hidden" name="pic_data[]" value="'+json_render+'" /></td><td class="data-nik">'+pic_data.nik+'</td><td>'+pic_data.nama+'</td><td '+has_error+'><input type="text" class="form-control" name="pic_posisi[]" value="'+pic_posisi+'" />'+thiserror+'</td><td><button type="button" class="btn btn-danger btn-xs delete-pic"><i class="glyphicon glyphicon-remove"></i> batal</button></td></tr>';
}
$(document).on('click', '.delete-pic', function(event) {
  event.preventDefault();
  /* Act on the event */
  $(this).parent().parent().remove();
  var table = $('#pictable');
  var tr_count = table.find('tbody>tr').not('tbody>tr.loading-tr');
  var loading = table.find('.loading-tr');
  $.each(table.find('.data-no'),function(index, el) {
    $(this).html(index+1);
  });
  if(tr_count.length==0){
    loading.show();
    table.parent().hide();
  }

});
$(document).on('change', '.mata-uang', function(event) {
  event.preventDefault();
  /* Act on the event */
  $('.mtu-set').html($(this).val())
  //console.log($('.select-user-vendor').val());
});
$(document).on('keyup', '.hitung_sp', function(event) {
  var _this = $(this),ppn={!!config('app.ppn_set')!!},total_ppn=0,total=0,td=_this.parent().parent().parent();
  var td_length = td.find('td');
  console.log(td_length.length);
  if(td_length.length==5){
    if(_this.attr('name')=='doc_nilai_material'){
      var material = _this.val();
    }
    else{
      var material = td.find('input[name="doc_nilai_material"]').val();
    }
    if(_this.attr('name')=='doc_nilai_jasa'){
      var jasa = _this.val();
    }
    else{
      var jasa = td.find('input[name="doc_nilai_jasa"]').val();
    }
    if(material==""){
      material = 0
    }
    if(jasa==""){
      jasa = 0
    }
    material = backNominal(material);
    jasa = backNominal(jasa);
    //console.log('qty => '+qty);
    //console.log('harga => '+harga);
    total = material+jasa;
    if(material!=0 && jasa!=0){
      total_ppn = ((ppn/100)*total)+total;
    }
    total_ppn = convertNumber(total_ppn);
    total = convertNumber(total);
    td.find('td').eq(2).text(formatRupiah(total));
    td.find('td').eq(4).text(formatRupiah(total_ppn));
  }
});

function add_select(attr) {
  $(document).on('click','.add-'+attr,function(){
    var _this = $(this);
    var content = _this.parent().parent();
    var btnDelete = content.find('.delete-'+attr);
    if(btnDelete.length==0){
      content.find('.input-group-btn').append('<button type="button" class="btn btn-default delete-'+attr+'"><i class="glyphicon glyphicon-trash"></i></button>');
    }
    content.after(create_content(attr));
  });
}
function delete_select(attr) {
  $(document).on('click','.delete-'+attr,function(){
    var _this = $(this);
    var content = _this.parent().parent();
    content.remove();
    var attrNya = $('.'+attr);
    if(attrNya.length==1){
      attrNya.parent().find('.delete-'+attr).remove();
    }
  });
}
function create_content(attr){
  var content = $('.'+attr),btnDelete;
  //console.log(content.length)
  if(content.length>0){
    btnDelete = '<button type="button" class="btn btn-default delete-'+attr+'"><i class="glyphicon glyphicon-trash"></i></button>'
  }
  return '<div class="input-group">\
    <input type="file" class="hide" name="'+attr+'[]">\
    <input class="form-control" type="text" name="name_lampiran[]" val="lampiran"  disabled>\
    <div class="input-group-btn">\
      <button class="btn btn-default click-upload" type="button">Browse</button>\
      <button type="button" class="btn btn-default add-'+attr+'"><i class="glyphicon glyphicon-plus"></i></button>\
      '+btnDelete+'\
    </div>\
  </div>';
}

</script>
@endpush
