<script>
import supplierData from './supplierData.vue';
// import Show from './Show.vue';
// import Show from '../purchasing_detail/Show.vue';
export default {
  // components: { Show },
  components: { supplierData },
  data() {
    return {
      // for datatables
        columns: [
              {data: 'DT_RowIndex', searchable: false, sortable: true},
              {data: 'date'},
              {data: 'supplier'},
              {data: 'total_item'},
              {data: 'total_price'},
              {data: 'discount'},
              {data: 'total_payment'},
              {data: 'action', searchable: false, sortable: false}
          ],
        detil: [ // for datatable
              {data: 'DT_RowIndex', searchable: false, sortable: true},
              {data: 'code'},
              {data: 'product_name'},
              {data: 'pricing_label'},
              {data: 'item_qty'},
              {data: 'subtotal'},
              {data: 'action', searchable: false, sortable: false}
          ],
      // for api url
        url: import.meta.env.VITE_APP_URL,
        getApi: import.meta.env.VITE_APP_API+'/purchasing',
      // for post sata
        csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      // for confirm box
        pesanErr: '',
        suppliers: '',
        confirm: '',
        modalkey: '',
        detil: [],
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
    addForm(key,note) {
        $('#FormModal').modal('show');
        $('#FormModal .modal-title').text(note);
        this.modalkey = key;

        // close notif
        this.closeNotif();
    },
    // get data in datatable
    datatable() {
      $('#table').DataTable({
        ajax: {
          url: this.getApi+'/data',
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
    getDetail(id) {
      // get data purchasing detail
      $.get(this.url+"api/purchasing-detail/"+id+"/data")
            .done((response) => {
              this.detil = response.data;
            })
            .fail((error) => {
              // set alert dan munculkan alert
              $("#notif-utama").attr('class', '');
                $( "#notif-utama" ).addClass( 'alert alert-danger alert-dismissible mb-3 show');
                $( "#notif-utama .text" ).text( error.responseJSON.message);
                return;
            });
    },
    redirectDetil(theid, supplay) {
      this.$router.push({name: 'purchasing-detail', params: { id: theid }});
    },
    deleteModal (id, notes, lanjut) {
      // munculkan modal
      $('#modalConfirm').modal('show');
      // ganti judul
      $('#modalConfirm .modal-title').text('Confirm delete data');
      // confirm
      $('#modalConfirm .modal-body').text(notes);
      // ganti rule
      if (lanjut == 1) {
        $('#confirm').show();
      } else {
        $('#confirm').hide();
      }
      this.confirm = id;
    },
    deleteData(id) {
        let urlApi = this.getApi+"/"+id;
        $.ajax({
            url: urlApi,
            type: 'delete',
        }).done((response) => {
          console.log(response);
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
  },
  mounted() {
    this.datatable();

    // inisialisasi
    let redirectDetil = this.redirectDetil;
    let deleteModal = this.deleteModal;
    let deleteData = this.deleteData;
    let getDetail = this.getDetail;
    let addForm = this.addForm;

    // const self = this === make finish
    $('tbody', this.$refs.table).on( 'click', '.finishData', function(){
        let theid = $(this).attr('data-idfinish');
        redirectDetil(theid);
    });
    // const self = this === open modal delete data
    $('tbody', this.$refs.table).on( 'click', '.deleteData', function(){
        let theid = $(this).attr('data-iddelete');
        let note;
        let lanjut = 1;
        if ($(this).attr('data-total') == 0) {
          note = "Yakin Mau menghapus data?";
          lanjut = 1;
        } else {
          note = "Silahkan Hapus item dalam transaksi ini sebelum menghapus data transaksi ini.";    
          lanjut = 0;      
        }
        deleteModal(theid, note, lanjut);
    });

    // const self = this === open modal delete data
    $('tbody', this.$refs.table).on( 'click', '.see', function(){
        let theid = $(this).attr('data-idsee');
        getDetail(theid);
        addForm(2, 'Detail Produk of Transaction');
    });

    // const self = this === open modal delete data
    $('#confirm').on('click', function(){
      let theid = $('#confirm').attr('data-term');
      deleteData(theid);
    });
  }
}
</script>

<style scoped>
  #tableDetil tbody tr:last-child {
    display: none;
  }
</style>

<template>
  <section class="content">
    <!-- modal box for form -->
    <div class="modal fade" id="FormModal" tabindex="-1" aria-labelledby="FormModalLabel" aria-hidden="true">
      <div class="modal-dialog  modal-lg">
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
          <div class="modal-body" v-if="modalkey == 1">
            <supplierData></supplierData>
          </div>
          <div class="modal-body" v-else-if="modalkey == 2">
            <div class="modal-body table-responsive">
              <!--  -->
              <table id="tableDetil" class="table table-bordered table-striped" width="100%">
                <thead>
                  <tr role="row">
                    <th width="5%">No</th>
                    <th>code</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Total Item</th>
                    <th>Total Price</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, key) in detil" :key="key">
                    <td>{{ item.DT_RowIndex }}</td>
                    <td>{{ item.code }}</td>
                    <td>{{ item.product_name }}</td>
                    <td>{{ item.pricing_label }}</td>
                    <td>{{ item.item_qty }}</td>
                    <td>{{ item.subtotal }}</td>
                  </tr>
                </tbody>
              </table>
              <!--  -->
            </div>
          </div>
          <!-- /.modal-body -->
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
          </div>
        </div>
      </div>
    </div>
    <!-- /.modal box for form -->

    <!-- confirm box -->
    <!-- modal box for delete form -->
    <div class="modal fade" id="modalConfirm" tabindex="-1" aria-labelledby="modalConfirmLabel" aria-hidden="true">
      <div class="modal-dialog  modal-lg">
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
                <button type="button" class="btn btn-primary xs btn-flat rounded" @click="addForm(1, 'Add New Product')"><i class="bi bi-patch-plus"></i> Add</button>
              </div>
            </div>
            <!-- /.card-header -->

            <div class="card-body table-responsive">
                <input type="hidden" name="_token" :value="csrf">
                <table id="table" class="table table-bordered table-striped">
                  <thead>
                    <tr role="row">
                      <th width="5%">No</th>
                      <th>Date</th>
                      <th>Supplier</th>
                      <th>Total Item</th>
                      <th>Total Price</th>
                      <th>Discount</th>
                      <th>Total Payment</th>
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