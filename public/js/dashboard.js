function dashboard() {
    return {
        isSyncing: false,
        isSendingReport: false,
        reportMonth: yearMonth,
        errMsg: null,
        transactions: transactions,
        modalTrx: {},
        modalKind: 'edit', // edit, delete, add
        init: function () {
            this.$refs.fromFilter.value = fromParam ?? ''
            this.$refs.chatroomFilter.value = chatroomParam ?? ''
        },
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
        sendReport: async function () {
            const self = this;
            if(! (fromParam && chatroomParam)) {
                alert('Filter Pengirim dan Chatroom dahulu.')
            } // endif

            self.isSendingReport = true;
            self.errMsg = null;
            await axios.post('/api/bot/sendreport', {
                bulan: self.reportMonth,
                from: fromParam,
                chatroom: chatroomParam
            }).then(() => {
                self.isSendingReport = false
                alert("Terkirim")
            }).catch(err => {
                self.isSendingReport = false
                console.log(err)
            })
        },
        goto: function(path) {
            window.location.href = path
        },
        setModalTrx: function(trx, kind='edit') {
            this.modalTrx = trx
            this.modalKind = kind
        },
        getTextColor: function (trx) {
            return trx.is_expense == 1 ? 'text-danger' : 'text-success'
        },
        getTextKind: function (trx) {
            return trx.is_expense == 1 ? 'Pengeluaran' : 'Pemasukan'
        },
        getToday: function() {
            const today = new Date();
            const dd = String(today.getDate()).padStart(2, '0');
            const mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
            const yyyy = today.getFullYear();

            return [yyyy, mm, dd].join('-')
        },
        newTrx: function(is_expense=1) {
            const modalTrx = {
                is_expense: is_expense,
                amount: 0,
                description: '',
                trx_date: this.getToday()
            }
            this.setModalTrx(modalTrx, 'add');
        },
        submitForm: function (ref) {
            if (!ref.checkValidity()) {
                if (ref.reportValidity) {
                    ref.reportValidity()
                } else {
                    //warn IE users somehow
                }
            } else {
                ref.submit()
            }
        }
    }
}
