<div style="text-align: center">
    <button wire:click="increment">+</button>
    <h1>{{ $count }}</h1>
</div>



<form class="mt-4 row g-2 align-items-end">
    <div class="col-sm-12 col-xl-8">
        <div class="row gap-0">
            <div class="col-4">
                <label>Bulan</label>
                <input type="month" class="form-control"
                    name="bulan"
                    value="{{ $req_month }}"
                    lang="id-ID"
                    placeholder="Pilih Bulan" role="button" />
            </div>
            <div class="col-4">
                <label>Pengirim</label>
                <select x-ref="fromFilter" class="form-control" name="from" placeholder="Pengirim">
                    <option value="">Semua Pengirim</option>
                    @foreach ($froms as $fr)
                        <option value="{{ $fr->id }}">{{ $fr->username }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-4">
                <label>Grup</label>
                <select x-ref="chatroomFilter" class="form-control" name="chatroom" placeholder="Chatroom">
                    <option value="">Semua Chatroom</option>
                    @foreach ($chatrooms as $cr)
                        <option value="{{ $cr->id }}">{{ $cr->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-xl-4 d-flex gap-2 justify-content-between">
        <div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-funnel-fill"></i>
                <span>Filter</span>
            </button>
        </div>
        <div>
            <button type="button" class="btn btn-secondary" @click="sendReport">
                <i class="bi bi-chat-right-dots-fill"></i>
                <span x-show="! isSendingReport">Kirim Laporan</span>
                <span x-show="isSendingReport">Loading ...</span>
            </button>
        </div>
    </div>
</form>
<div class="table-responsive">
    <table class="table table-hover table-striped table-responsive mt-4" style="min-width: 800px; width: 100%">
        <caption>History Keuangan</caption>
        <thead>
            <th>Waktu</th>
            <th>Nominal</th>
            <th>Deskripsi</th>
            <th>Tipe</th>
            <th>Sender</th>
            <th>Aksi</th>
        </thead>
        <tbody>
            <template x-for="trx in transactions">
                <tr>
                    <td x-text="trx.trx_date_format"></td>
                    <td x-text="trx.amount_format" :class="getTextColor(trx)"></td>
                    <td x-text="trx.description"></td>
                    <td x-text="getTextKind(trx)" :class="getTextColor(trx)"></td>
                    <td x-text="trx.from.username"></td>
                    <td style="width: 100px">
                        <button type="button" @click="setModalTrx(trx, 'edit')"
                            data-bs-toggle="modal" data-bs-target="#editModal"
                            class="btn btn-sm btn-primary" title="Ubah">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button type="button"  @click="setModalTrx(trx, 'delete')"
                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                            class="btn btn-sm btn-danger" title="Hapus">
                            <i class="bi bi-trash3-fill"></i>
                        </button>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Hapus Item?</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form x-ref="deleteform" method="post" action="{{ url('/delete') }}">
                @csrf
                <input type="hidden" name="id" :value="modalTrx?.id">
            </form>
            <div>
                Data ini akan dihapus, apakah anda yakin?
                <div>Waktu: <span x-text="modalTrx?.trx_date_format"></span></div>
                <div>Nominal: <span x-text="modalTrx?.amount_format"
                    :class="getTextColor(modalTrx)"></span></div>
                <div>Tipe: <span x-text="getTextKind(modalTrx)"
                    :class="getTextColor(modalTrx)"></span></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            <button type="button" class="btn btn-danger" @click="$refs.deleteform.submit()">Ya, Hapus</button>
        </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel"
                x-text="modalKind == 'add' ? 'Tambah Baru' : 'Edit Item'">Edit Item</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form x-ref="editform" method="post" :action="modalKind == 'add' ? '/add' : '/edit'">
                @csrf
                <input type="hidden" name="id" :value="modalTrx?.id">
                <input type="hidden" name="is_expense" :value="modalTrx?.is_expense">
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-2 col-form-label">Tipe</label>
                    <div class="col-sm-10">
                        <span x-text="getTextKind(modalTrx)" :class="getTextColor(modalTrx)"></span>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Waktu</label>
                    <div class="col-sm-10">
                        <input role="button" name="trx_date"
                            type="date" class="form-control" :value="modalTrx?.trx_date" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Nominal</label>
                    <div class="col-sm-10">
                        <input type="number" name="amount" min="1"
                            class="form-control" :value="modalTrx?.amount" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Deskripsi</label>
                    <div class="col-sm-10">
                        <input type="text" name="description"
                            class="form-control" :value="modalTrx?.description" required>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            <button type="button" class="btn btn-primary"
                {{-- @click="$refs.editform.submit()" --}}
                @click="submitForm($refs.editform)"
                >Simpan</button>
        </div>
        </div>
    </div>
</div>
