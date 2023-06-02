function dashboard() {
    return {
        isUpdating: false,
        errMsg: null,
        chats: chats,
        chatDetail: {},
        goto: function(path) {
            window.location.href = path
        },
        setChatDetail: function(chat) {
            this.chatDetail = chat
            console.log("setChatDetail", chat)
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
