<script>
// import Product from './Product.vue';
export default {
  // components: { Product },
  data() {
    return {
      // for datatables
        columns: [
          {data: 'select_all', searchable: false, sortable: false},
          {data: 'DT_RowIndex', searchable: false, sortable: true},
          {data: 'code'},
          {data: 'name'},
          {data: 'category'},
          {data: 'brand'},
          {data: 'buy'},
          {data: 'sale'},
          {data: 'discountWpres'},
          {data: 'stock'},
          {data: 'action', searchable: false, sortable: false}
        ],
      // data category
        category : {
          'selected' : 'one',
          'data' : {
            'name' : "data category",
            'data':[]
          }
        },
      // for api url
        url: import.meta.env.VITE_APP_URL,
        getApi: import.meta.env.VITE_APP_API+'/product',
      // for post sata
        csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      // for confirm box
        confirm:'',
        pesanErr: ''
    }
  },
  methods: {
    cetakBarcode(url) {
      if ($('input:checked').length < 1) {
        alert('Pilih data yang akan dicetak');
        return;
      } else if ($('input:checked').length < 3) {
        alert('Pilih minimal 3 data untuk dicetak');
        return;
      } else {
        $('.form-product')
        .attr('action', this.url+'product/'+url)
        .attr('target', '_blank')
        .attr('method', 'post')
        .submit();
      }
    },
    closeNotif() {
        // reset alert
        if ($( "#notif" ).hasClass('d-none')) {

        } else {
            $( "#notif" ).addClass( 'd-none');
        }
        // reset alert
        if ($( "#notif-utama" ).hasClass('d-none')) {

        } else {
            $( "#notif-utama" ).addClass( 'd-none');
        }
    },
    getCategory() {
      // get data category
      $.get(this.url+"api/category")
            .done((response) => {
              this.category.data = response.data;
            })
            .fail((error) => {
                // set alert dan munculkan alert
                $("#notif").attr('class', '');
                $( "#notif" ).addClass( 'alert alert-danger alert-dismissible mb-3 show');
                // isi tulisan
                $('#notif .text').html( error.responseJSON.message );
                return;
            })
    },
    addForm() {
        $('#FormModal').modal('show');
        $('#FormModal .modal-title').text('Add New Product');

        // // action
        $('#FormModal form').attr('action', this.getApi)

        // // method form
        $('#FormModal [name=_method]').val('post');

        // // input form
        $('#FormModal [name=name]').focus();

        // close notif
        this.closeNotif();
    },
    editForm(id) {
        $('#FormModal').modal('show');
        $('#FormModal .modal-title').text('Edit Product');

        // // action
        $('#FormModal form').attr('action', this.getApi+"/"+id);

        // // method form
        $('#FormModal [name=_method]').val('put');

        // // input form
        $('#FormModal [name=name]').focus();

        $.get(this.getApi+"/"+id)
            .done((response) => {
                $('#FormModal [name=name]').val(response.data[0].name);
                $('#FormModal [name=category_id]').val(response.data[0].category_id);
                $('#FormModal [name=brand]').val(response.data[0].brand);
                $('#FormModal [name=buying_price]').val(response.data[0].buying_price);
                $('#FormModal [name=selling_price]').val(response.data[0].selling_price);
                $('#FormModal [name=discount]').val(response.data[0].discount);
                $('#FormModal [name=stock]').val(response.data[0].stock);
            })
            .fail((error) => {
                // set alert dan munculkan alert
                $("#notif-utama").attr('class', '');
                $( "#notif-utama" ).addClass( 'alert alert-danger alert-dismissible mb-3 show');
                // membuat pesan error
                this.pesanErr = error.responseJSON;
                return;
            })
        
        // close notif
        this.closeNotif();
    },
    deleteForm (id) {
      // munculkan modal
      $('#modalConfirm').modal('show');
      // ganti judul
      $('#modalConfirm .modal-title').text('Confirm delete data');
      // confirm
      $('#modalConfirm .modal-body').text(`
          Apakan yakin mau menghapus data?
      `);
      // ganti rule
      this.confirm = id;
    },
    resetForm () {
      // reset form
      $('#FormModal form')[0].reset();
      this.closeNotif();
      this.category.selected = "one";
    },
    // get data in datatable
    datatable() {
      $('#table').DataTable({
        ajax: {
          url: this.getApi,
          type: 'GET',
        },//memanggil data dari data api dengan ajax, disimpan di DataTable
        columns: this.columns,
        dom: 'Bfrtip',
        buttons: [
            'pageLength',
            'colvis',
            'spacer',
            'copyHtml5',
            'pdfHtml5',
            'print',
            'csvHtml5',
            {
              extend: 'excel',
              text: 'exel',
              exportOptions: {
                  modifier: {
                    page: 'current'
                  }
              }
            },
        ]
      });
    },
    submitForm() {
        $.ajax({
            url: $('#FormModal form').attr('action'),
            type: $('#FormModal [name=_method]').val(),
            data: $('#FormModal form').serialize()
        }).done((response) => {
            // hilangkan modal
            $('#FormModal').modal('hide');
            // reload table
            $('#table').DataTable().ajax.reload();
            // set alert dan munculkan alert
            $("#notif-utama").attr('class', '');
            $( "#notif-utama" ).addClass( 'alert alert-success alert-dismissible mb-3 show');
            // reset form
            $('#FormModal form')[0].reset();
            // isi tulisan
            if ( $('[name=_method]').val() == 'put' ) {
                $('#notif-utama .text').text("Updated data success");
            } else if ( $('[name=_method]').val() == 'post' ) {
                $('#notif-utama .text').text("Add new data success");
            }
        }).fail((error) => {
            // set alert dan munculkan alert
            $("#notif").attr('class', '');
            $( "#notif" ).addClass( 'alert alert-danger alert-dismissible mb-3 show');
            // membuat pesan error
            this.pesanErr = error.responseJSON;
            console.log(error.responseJSON.message);
            return;
        });
    },
    deleteData(id) {
        let urlApi = this.getApi+"/"+id;
        $.ajax({
            url: urlApi,
            type: 'delete',
        }).done((response) => {
            // hilangkan modal
            $('#modalConfirm').modal('hide');
            // reload table
            $('#table').DataTable().ajax.reload();
            // set alert dan munculkan alert
            $("#notif-utama").attr('class', '');
            $( "#notif-utama" ).addClass( 'alert alert-success alert-dismissible mb-3 show');
            /// isi tulisan
            $('#notif-utama .text').text("Data deleted");
        }).fail((error) => {
            // set alert dan munculkan alert
            $("#notif").attr('class', '');
            $( "#notif" ).addClass( 'alert alert-danger alert-dismissible mb-3 show');
            // membuat pesan error
            this.pesanErr = error.responseJSON;
            return;
        });
    },
    deleteSelected(url) {
      if ($('input:checked').length > 1) {
        if (confirm('Yakin ingin menghapus data terpilih?')) {
          // $.post(this.getApi+url, $('.form-product').serialize())
          $.ajax({
            url: this.getApi+url,
            type: 'post',
            data: $('.form-product').serialize()
          }).done( (response) => {
              // reload table
              $('#table').DataTable().ajax.reload();
              // set alert dan munculkan alert
              $("#notif-utama").attr('class', '');
              $( "#notif-utama" ).addClass( 'alert alert-success alert-dismissible mb-3 show');
              // isi tulisan
              $('#notif-utama .text').text("Delete data success");
              // hide button
              if ($( "#multipledelete" ).hasClass('d-none')) {
                $('#multipledelete').removeClass( "d-none" );
                $('#multipledelete').addClass( "d-block" );
              } else {
                $( "#multipledelete" ).addClass( 'd-block');
                $( "#multipledelete" ).addClass( 'd-none');
              }
              if ($( "#cetak" ).hasClass('d-none')) {
                $('#cetak').removeClass( "d-none" );
                $('#cetak').addClass( "d-block" );
              } else {
                $( "#cetak" ).addClass( 'd-block');
                $( "#cetak" ).addClass( 'd-none');
              }
          }).fail( (error) => {
            console.log(error);
            // set alert dan munculkan alert
            $("#notif-utama").attr('class', '');
            $( "#notif-utama" ).addClass( 'alert alert-danger alert-dismissible mb-3 show');
            // isi tulisan
            $('#notif-utama .text').html( 'Cannot deleting the data' );
            return;
          });
        }
      } else {
        alert('Pilih data yang akan dihapus sehingga lebih dari 1');
      }
    },
  },
  mounted() {
    this.datatable();

    let edit = this.editForm;
    let deleteForm = this.deleteForm;
    let deleteData = this.deleteData;

    // const self = this
    $('tbody', this.$refs.table).on( 'click', '.editData', function(){
        let theid = $(this).attr('data-idedit');
        edit(theid);
    });
    // open delete form
    $('tbody', this.$refs.table).on( 'click', '.deleteData', function(){
        let theid = $(this).attr('data-iddelete');
        deleteForm(theid);
    });
    // deleting data
    $('#confirm').on('click', function(){
      let theid = $('#confirm').attr('data-term');
      deleteData(theid);
    });
    this.getCategory();
    // select all
    $('#select_all').on('click', function () {
      // check all and un check all
      $(':checkbox').prop('checked', this.checked);

      // jika ada yang di check
      if ($('.checking').is(':checked')) {
        if ($( "#multipledelete" ).hasClass('d-none')) {
          $('#multipledelete').removeClass( "d-none" );
          $('#multipledelete').addClass( "d-block" );
        } else {
          // none
        }
        if ($( "#cetak" ).hasClass('d-none')) {
          $('#cetak').removeClass( "d-none" );
          $('#cetak').addClass( "d-block" );
        } else {
          // none
        }
      } else {
        // multiple delete
        if ($( "#multipledelete" ).hasClass('d-none')) {
          $('#multipledelete').removeClass( "d-none" );
          $('#multipledelete').addClass( "d-block" );
        } else {
          $( "#multipledelete" ).addClass( 'd-block');
          $( "#multipledelete" ).addClass( 'd-none');
        }
        $('#multipledelete').addClass( "d-none" );
        // cetak
        if ($( "#cetak" ).hasClass('d-none')) {
          $('#cetak').removeClass( "d-none" );
          $('#cetak').addClass( "d-block" );
        } else {
          $( "#cetak" ).addClass( 'd-block');
          $( "#cetak" ).addClass( 'd-none');
        }
        $('#cetak').addClass( "d-none" );
        $(':checkbox').prop('checked', this.checked);
      }
    });
    // show hide button
    $('tbody', this.$refs.table).on( 'click', '.checking', function(){
      if ($('.checking').is(':checked')) {
        if ($( "#multipledelete" ).hasClass('d-none')) {
          $('#multipledelete').removeClass( "d-none" );
          $('#multipledelete').addClass( "d-block" );
        }
        if ($( "#cetak" ).hasClass('d-none')) {
          $('#cetak').removeClass( "d-none" );
          $('#cetak').addClass( "d-block" );
        }
      } else {
        $('#multipledelete').addClass( "d-none" );
        $('#cetak').addClass( "d-none" );
        $(':checkbox').prop('checked', this.checked);
      }
    });
  }
}
</script>

<template>
  <section class="content">
    <!-- modal box for form -->
    <div class="modal fade" id="FormModal" tabindex="-1" aria-labelledby="FormModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form action="" method="post" class="form-horizontal" @submit.prevent="submitForm()">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="FormModalLabel">Modal title</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Alert -->
            <div class="d-none" id="notif" data-not="1" role="alert">
              <span class="text">
                <span v-for="(value, key) in pesanErr" class="d-block" :key="key">
                  {{ value[0] }}
                </span>
              </span>
              <button type="button" class="btn-close" @click="closeNotif()"></button>
            </div>
            <!-- /.alert -->
            <div class="modal-body">
              <!-- csrf token dan method -->
              <input type="hidden" name="_token" :value="csrf">
              <input type="hidden" name="_method" value="post">
              <!-- end csrf token dan method -->
              <div class="form-group row">
                <label for="name" class="col-md-4 control-label">Product Name</label>
                <div class="col-md-8">
                  <input type="text" name="name" id="name" class="form-control" placeholder="add name of product" autocomplete="off">
                  <span class="help-block with-errors"></span>
                </div>
              </div>
              <div class="form-group row mt-1">
                <label for="category_id" class="col-md-4 control-label">Category Name</label>
                <div class="col-md-8">
                  <select v-model="category.selected" id="category_id" name="category_id" class="form-control">
                    <option value="one">Pilih data</option>
                    <option v-bind:value="category.id"  v-for="(category, key) in category.data" :key="key">
                      {{ category.name }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="form-group row mt-1">
                <label for="brand" class="col-md-4 control-label">Product brand</label>
                <div class="col-md-8">
                  <input type="text" name="brand" id="brand" class="form-control" placeholder="add brand of product" autocomplete="off">
                  <span class="help-block with-errors"></span>
                </div>
              </div>
              <div class="form-group row mt-1">
                <label for="buying_price" class="col-md-4 control-label">Harga Beli</label>
                <div class="col-md-8">
                  <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" min="0" name="buying_price" id="buying_price" class="form-control" placeholder="Harga Pembelian" value="0" autocomplete="off">
                  </div>
                </div>
              </div>
              <div class="form-group row mt-1">
                <label for="selling_price" class="col-md-4 control-label">Harga Jual</label>
                <div class="col-md-8">
                  <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" min="0" class="form-control" name="selling_price" placeholder="Harga Jual" id="selling_price" value="0">
                  </div>
                </div>
              </div>
              <div class="form-group row mt-1">
                <label for="discount" class="col-md-4 control-label">Diskon</label>
                <div class="col-md-8">
                  <div class="input-group">
                    <input type="number" min="0" max="100" name="discount" id="discount" class="form-control" placeholder="Diskon" value="0" autocomplete="off">
                    <span class="input-group-text">%</span>
                  </div>
                </div>
              </div>
              <!-- <div class="form-group row mt-1">
                <label for="stock" class="col-md-4 control-label">Stock</label>
                <div class="col-md-8"> -->
                  <input type="hidden" name="stock" min="0" id="stock" class="form-control" placeholder="Stock" value="0" autocomplete="off">
                  <!-- <span class="help-block with-errors"></span>
                </div>
              </div> -->
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">Save</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="resetForm()">Cancel</button>
            </div>
          </div>
        </form>
      </div>
    </div>
    <!-- confirm box -->
    <!-- modal box for delete form -->
    <div class="modal fade" id="modalConfirm" tabindex="-1" aria-labelledby="modalConfirmLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fs-5" id="modalConfirmLabel">Konfirmasi</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <span :data-term="confirm" id="confirm" class="btn confirm btn-primary">Yes</span>
          </div>
        </div>
      </div>
    </div>

    <!-- main page -->
    <div class="container-fluid pb-5">

      <div class="row">
        <div class="col-md-12">

            <!-- Alert -->
            <div class="d-none" id="notif-utama" role="alert">
                <span class="text"></span>
                <button type="button" class="btn-close" @click="closeNotif()"></button>
            </div>
            <!-- /.alert -->

          <div class="card">
            <!-- card-header -->
            <div class="card-header">
              <div class="btn-group">
                <!-- Button trigger modal -->
                <button type="button" class="btn btn-primary xs btn-flat rounded" @click="addForm()"><i class="bi bi-patch-plus"></i> Add</button>
                <button id="multipledelete" class="btn btn-danger xs btn-flat rounded mx-1 d-none" @click="deleteSelected('/delete-selected')"><i class="bi bi-trash"></i> Delete</button>
                <button id="cetak" class="btn btn-secondary xs btn-flat rounded mx-1 d-none" @click="cetakBarcode('cetak-barcode')"><i class="bi bi-upc-scan"></i> Cetak Label Price</button>
              </div>
            </div>
            <!-- /.card-header -->

            <div class="card-body table-responsive">
              <form action="" class="form-product">
                <input type="hidden" name="_token" :value="csrf">
                <table id="table" class="table table-bordered table-striped">
                  <thead>
                    <tr role="row">
                      <th>
                        <div class="mx-3">
                          <input type="checkbox" name="select_all" id="select_all">
                        </div>
                      </th>
                      <th width="5%">No</th>
                      <th>Code</th>
                      <th>Name</th>
                      <th>Category</th>
                      <th>Brand</th>
                      <th>Buying Price</th>
                      <th>Selling Price</th>
                      <th>Discount</th>
                      <th>Stoct</th>
                      <th width="15%" class="fs-4"><i class="bi bi-gear-wide-connected"></i></th>
                    </tr>
                  </thead>
                  <tbody>

                  </tbody>
                </table>
              </form>
            </div>
            <!-- ./card-body -->
            <div class="card-footer">
            </div>
            <!-- /.card-footer -->
          </div>
          <!-- /.card -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->

    </div><!--/. container-fluid -->
  </section>
</template>