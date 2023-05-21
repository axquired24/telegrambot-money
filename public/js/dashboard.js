function dashboard() {
    return {
        isSyncing: false,
        errMsg: null,

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
        }
    }
}
