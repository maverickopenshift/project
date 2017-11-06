<div class="form-group  {{ $errors->has('parent_kontrak') ? ' has-error' : '' }}">
  <label for="nm_vendor" class="col-sm-2 control-label"><span class="text-red">*</span> No Kontrak</label>
  <div class="col-sm-10">
    <input type="hidden" class="select-kontrak-text" name="parent_kontrak_text" value="{{Helper::old_prop($doc,'parent_kontrak_text')}}">
    <select class="form-control select-kontrak" style="width: 100%;" name="parent_kontrak" data-id="{{Helper::old_prop($doc,'parent_kontrak')}}">
        <option value="">Pilih Kontrak</option>
    </select>
    @if ($errors->has('parent_kontrak'))
        <span class="help-block">
            <strong>{{ $errors->first('parent_kontrak') }}</strong>
        </span>
    @endif
    <div class="result-kontrak"></div>
  </div>
</div>
<div class="form-group judul-man" style="display:none;">
  <label for="nm_vendor" class="col-sm-2 control-label">Judul</label>
  <div class="col-sm-10 text-me"></div>
</div>
@push('scripts')
  <script>
  $(function() {
    var selectKontrak = $(".select-kontrak").select2({
      placeholder : "Pilih Kontrak....",
      ajax: {
          url: '{!! route('doc.get-select-kontrak') !!}',
          dataType: 'json',
          delay: 350,
          data: function (params) {
              return {
                  q: params.term, // search term
                  type:'{!!$doc_type->name!!}',
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
                  o.name = v.doc_title;
                  o.value = v.doc_no;
                  o.date = $.format.date(v.doc_date+" 10:54:50", "ddd, dd MMMM yyyy");
                  o.type = v.type;
                  o.jenis = v.jenis;
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
      templateResult: function (state) {
          if (state.id === undefined || state.id === "") { return ; }
          var $state = $(
              '<span>' +  state.name +' <i>('+  state.value + ')</i></span>'
          );
          return $state;
      },
      templateSelection: function (data) {
          if (data.id === undefined || data.id === "") { // adjust for custom placeholder values
              return;
          }
          var render = data.value;
          if(data.value === undefined){
            render = $('.select-kontrak :selected').text();
          }
          $('.select-kontrak-text').val(render);
          return render ;
      }
    });
    selectKontrak.on('select2:select', function (e) {
        var data = e.params.data;
        templateKontrakSelect(data);
    });
    var kontrak_set = $(".select-kontrak");
    if(kontrak_set.data('id')!==""){
      var text_kontrak = $(".select-kontrak-text").val();
      var newOption_ = new Option(text_kontrak, kontrak_set.data('id'), false, true);
      kontrak_set.append(newOption_);
      kontrak_set.val(kontrak_set.data('id')).change();
      $.ajax({
        url: '{!! route('doc.get-select-kontrak') !!}',
        type: 'GET',
        dataType: 'json',
        data: {
            q: text_kontrak, // search term
            type:'{!!$doc_type->name!!}'
        }
      })
      .done(function(data) {
        var o = {};
        $.each(data.data, function (i, v) {
            //var o = {};
            o.id = v.id;
            o.name = v.doc_title;
            o.value = v.doc_no;
            o.date = $.format.date(v.doc_date+" 10:54:50", "ddd, dd MMMM yyyy");
            o.type = v.type;
            o.jenis = v.jenis;
        })
        templateKontrakSelect(o);
        //console.log(JSON.stringify(o));
      });
      
    }
  });
  function templateKontrakSelect(data){
    $('.judul-man').hide().find('.text-me').html('');
    var table = $('.result-kontrak'),judul,t_type='';
    //console.log(JSON.stringify(data));
    table.html('');
    var s_type = JSON.parse(data.type);
    console.log(JSON.stringify(s_type.length));
    var t_table = '<table class="table">\
                    <thead>\
                      <tr >\
                            <th width="400">Judul</th>\
                            <th>Tanggal</th>\
                            <th  width="200">Jenis</th>\
                      </tr>\
                    </thead>\
                    <tbody>\
                      <tr>\
                            <td>'+data.name+'</td>\
                            <td>'+data.date+'</td>\
                            <td>'+data.jenis.type.title+'</td>\
                      </tr>\
                    </tbody>\
                  </table>';
      if(s_type.length>0){
        t_type = '<table class="table">\
                        <thead>\
                          <tr width="400">\
                                <th>Judul</th>\
                                <th>Tanggal Mulai</th>\
                                <th>Tanggal Akhir</th>\
                          </tr>\
                        </thead>\
                        <tbody>';
                        $.each(s_type,function(index, el) {
                          t_type += '<tr>\
                                          <td>'+this.doc_title+'</td>\
                                          <td>'+$.format.date(this.doc_startdate+" 10:54:50", "dd MMMM yyyy")+'</td>\
                                          <td>'+$.format.date(this.doc_enddate+" 10:54:50", "dd MMMM yyyy")+'</td>\
                                    </tr>';
                        });
          t_type +=    '</tbody>\
                      </table>';
          judul = '{!!strtoupper($doc_type->name)!!} #'+(s_type.length+1);
      }
      else{
        judul = '{!!strtoupper($doc_type->name)!!} #1';
      }
    $('.judul-man').show().find('.text-me').html(judul+'<input type="hidden" value="'+judul+'" name="doc_title"/>');        
    table.html(t_table+t_type);
  }
  </script>
@endpush