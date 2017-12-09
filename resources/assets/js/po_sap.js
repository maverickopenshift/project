function render_po(po){
  var error_po = $('.error-po');
  var table = $('#potables');
  var parentPO = $('#parentPO');
  var loading = table.find('.loading-tr');
  loading.show();
  var tr_count = table.find('tbody>tr').not('tbody>tr.loading-tr');
  table.parent().hide();
    $.ajax({
      url: routes.getPO,
      type: 'GET',
      dataType: 'json',
      data: {po: po}
    })
    .done(function(response) {
      if(response.status){
        if(response.length==0){
          error_po.html(response.data);
        }
        else{
          var total = response.length;
          var data = response.data.Poitem['item'];
          var dataHeader = response.data.Poheader['item'];
          loading.hide();
          table.parent().show();
          var tr;
          table.find('tbody').html('');
          parentPO.find('tbody').html('');
          if(total>1){
            $.each(data,function(index, el) {
              var po_data = parseItem(this,index,dataHeader.Currency);
              var tr = templatePO(po_data);
              table.find('tbody').append(tr);
            });
          }
          else{
            var po_data = parseItem(data,0,dataHeader.Currency);
            var tr = templatePO(po_data);
            table.find('tbody').append(tr);
          }
          var td = ParentPO(response.data);
          parentPO.find('tbody').append(td);
          error_po.html('');
        }
      }
    });
}
function parseItem(data,index,mtu){
  var hargaTotal = data.Quantity*data.NetPrice;
  var po_data = {
    no         : (index+1),
    kode_item  : data.PoItem,
    item       : data.ShortText,
    qty        : formatRupiah(data.Quantity),
    satuan     : data.PoUnit,
    mtu        : mtu,
    harga      : formatRupiah(data.NetPrice),
    harga_total: formatRupiah(hargaTotal),
    keterangan : data.Trackingno,
    date       : data.PriceDate,
    no_pr      : data.PreqNo,
  };
  return po_data;
}
function templatePO(data) {
  var dates = data.date;
  var split_date = dates.split('-');
  var hDate_year = split_date[0]; 
  var hDate_month = split_date[1]; 
  var hDate_day = split_date[2]; 
  return '<tr>\
              <td>'+data.no+'</th>\
              <td>'+data.kode_item+'</th>\
              <td>'+data.item+'</th>\
              <td>'+data.qty+'</th>\
              <td>'+data.satuan+'</th>\
              <td>'+data.mtu+'</th>\
              <td>'+data.harga_total+'</th>\
              <td>'+hDate_day+'-'+hDate_month+'-'+hDate_year+'</th>\
              <td>'+data.no_pr+'</th>\
              <td>'+data.keterangan+'</th>\
          </tr>\
          <!--<tr>\
            <td>Nama Approval PO</td>\
            <td> : </td>\
            <td>- <input type="hidden" name="po_approval" value=""></td>\
          </tr>\
          <tr>\
            <td>Nama Penandatangan PO</td>\
            <td> : </td>\
            <td>- <input type="hidden" name="po_penandatangan" value=""></td>\
          </tr>-->';
}

function ParentPO(data) {
  var nopo = $('.no_po').val();
  var dataHeader = data.Poheader['item'];
  var dataVendor = data.VendorInf['item'];
  var dataUser = data.UsrInf['item'];
  var dates = dataHeader.CreatDate;
  var split_date = dates.split('-');
  var hDate_year = split_date[0]; 
  var hDate_month = split_date[1]; 
  var hDate_day = split_date[2]; 
  return '<tr>\
            <td width="150">No PO </td>\
            <td width="10">:</td>\
            <td>'+dataHeader.PoNumber+'<input type="hidden" name="po_no" value="'+dataHeader.PoNumber+'"></td>\
          </tr>\
          <tr>\
            <td>Tanggal PO</td>\
            <td> : </td>\
            <td>'+hDate_day+'-'+hDate_month+'-'+hDate_year+'<input type="hidden" name="po_date" value="'+hDate_year+'-'+hDate_month+'-'+hDate_day+'"></td>\
          </tr>\
          <tr>\
            <td>Nama Vendor</td>\
            <td> : </td>\
            <td>'+dataVendor.Lifnr+' - '+dataVendor.Name1+' '+dataVendor.Stras+' '+dataVendor.Ort01+' '+dataVendor.Pstlz+'<input type="hidden" name="po_vendor" value="'+dataVendor.Lifnr+' - '+dataVendor.Name1+' '+dataVendor.Stras+' '+dataVendor.Ort01+' '+dataVendor.Pstlz+'"></td>\
          </tr>\
          <tr>\
            <td>Nama Pembuat/nik</td>\
            <td> : </td>\
            <td>'+dataUser.Bname+' - '+dataUser.NameText+'<input type="hidden" name="po_pembuat" value="'+dataUser.NameText+'"><input type="hidden" name="po_nik" value="'+dataUser.NameText+'"></td>\
          </tr>';
}