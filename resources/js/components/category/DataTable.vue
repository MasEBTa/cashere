<script>
export default {
  data() {
    return {
      // for datatables
        columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: true},
                {data: 'name'},
                {data: 'action', searchable: false, sortable: false},
        ],
      // for api url
        url: import.meta.env.VITE_APP_URL,
        getApi: import.meta.env.VITE_APP_API+'/category',
      // for post sata
        csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      // for confirm box
        confirm:''
    }
  },
  methods: {
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
    addForm() {
        $('#FormModal').modal('show');
        $('#FormModal .modal-title').text('Add New Kategory');

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
        $('#FormModal .modal-title').text('Edit Kategory');

        // // action
        $('#FormModal form').attr('action', this.getApi+"/"+id);

        // // method form
        $('#FormModal [name=_method]').val('put');

        // // input form
        $('#FormModal [name=name]').focus();

        $.get(this.getApi+"/"+id)
            .done((response) => {
                $('#FormModal [name=name]').val(response.data[0].name);
                // console.log('yey');
                // console.log(response.data[0].name);
            })
            .fail((error) => {
                // set alert dan munculkan alert
                $("#notif").attr('class', '');
                $( "#notif" ).addClass( 'alert alert-danger alert-dismissible mb-3 show');
                // membuat pesan error
                let pesan = '';
                let pesanErr = '';
                if (error.responseJSON.message) {
                  pesan = error.responseJSON.message;
                  pesanErr = pesan;
                } else {
                  pesan = Object.entries(error.responseJSON);
                  pesan.forEach(element => {
                    pesanErr = pesanErr+element[1]+'<br>';
                  });
                }
                // isi tulisan
                $('#notif .text').html( pesanErr );
                return;
            })
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
    },
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
            // membuat pesan error
            let pesan = '';
            let pesanErr = '';
            if (error.responseJSON.message) {
              pesan = error.responseJSON.message;
              pesanErr = pesan;
            } else {
              pesan = Object.entries(error.responseJSON);
              pesan.forEach(element => {
                pesanErr = pesanErr+element[1]+'<br>';
              });
            }
            // membuat notif
            $("#notif").attr('class', '');
            $( "#notif" ).addClass( 'alert alert-danger alert-dismissible mb-3 show');
            // isi tulisan
            $('#notif .text').html( pesanErr );
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
            // munculkan modal
            $('#modalConfirm').modal('hide');
            // membuat pesan error
            const pesan = error.status;
            // if status 500
            let pesanErr = "";
            if (pesan == 500) {
              pesanErr = "Kategori ini masih memiliki data produk di dalamnya. Kosongkan Produk di dalamnya lalu ulangi proses penghapusan."
            } else {
              if (error.responseJSON.message) {
                pesan = error.responseJSON.message;
                pesanErr = pesan;
              } else {
                pesan = Object.entries(error.responseJSON);
                pesan.forEach(element => {
                  pesanErr = pesanErr+element[1]+'<br>';
                });
              }
            }
            // membuat notif
            $("#notif-utama").attr('class', '');
            $( "#notif-utama" ).addClass( 'alert alert-danger alert-dismissible mb-3 show');
            // isi tulisan
            $('#notif-utama .text').html( pesanErr );
            return;
        });
    },
  },
  mounted() {
    this.datatable();
    // const table = $(this.$refs.table).dataTable({
    //   ajax: this.getApi,
    //   columns: this.columns
    // });
    let edit = this.editForm;
    let deleteForm = this.deleteForm;
    let deleteData = this.deleteData;

    // const self = this
    $('tbody', this.$refs.table).on( 'click', '.editData', function(){
        let theid = $(this).attr('data-idedit');
        edit(theid);
    });
    $('tbody', this.$refs.table).on( 'click', '.deleteData', function(){
        let theid = $(this).attr('data-iddelete');
        deleteForm(theid);
    });
    $('#confirm').on('click', function(){
      let theid = $('#confirm').attr('data-term');
      deleteData(theid);
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
                <span class="text">This</span>
                <button type="button" class="btn-close" @click="closeNotif()"></button>
            </div>
            <!-- /.alert -->
            <div class="modal-body">
              <!-- csrf token dan method -->
              <input type="hidden" name="_token" :value="csrf">
              <input type="hidden" name="_method" value="post">
              <!-- end csrf token dan method -->
              <div class="form-group row">
                <label for="name" class="col-md-4 control-label">Category Name</label>
                <div class="col-md-8">
                    <input type="text" name="name" id="name" class="form-control" autocomplete="off">
                    <span class="help-block with-errors"></span>
                </div>
              </div>
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
              <!-- Button trigger modal -->
              <button type="button" class="btn btn-primary" @click="addForm('/')"><i class="bi bi-patch-plus"></i> Add</button>
            </div>
            <!-- /.card-header -->

            <div class="card-body table-responsive">
                <!-- <table class="table table-stiped table-bordered"> -->
                <table id="table" class="table table-bordered table-striped">
                  <thead>
                    <tr role="row">
                      <th width="5%">No</th>
                      <th>Category</th>
                      <th width="15%" class="fs-4"><i class="bi bi-gear-wide-connected"></i></th>
                    </tr>
                  </thead>
                  <tbody>

                  </tbody>
                </table>
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