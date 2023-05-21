function dashboard() {
    return {
        isSyncing: false,
        errMsg: null,
        transactions: transactions,
        modalTrx: {},
        modalKind: 'edit', // edit, delete
        errSync: function(err) {
            this.isSyncing = false;
            this.errMsg = 'Error saat sinkronisasi, cek log untuk detail.'
            console.error('Failed to catch')
        },
        syncToday: async function () {
            const self = this;
            self.isSyncing = true;
            self.errMsg = null;
            await axios.get('/api/bot/daily').then(() => {
                axios.get('/api/db/parse').then(() => {
                    self.isSyncing = false;
                    window.location.reload()
                }).catch(err => {
                    self.errSync()
                });
            }).catch(err => {
                self.errSync()
            })
        },
        goto: function(path) {
            window.location.href = path
        },
        setModalTrx: function(trx, kind='edit') {
            this.modalTrx = trx
            this.modalKind = kind
            console.log(this.modalKind, this.modalTrx)
        }
    }
}
