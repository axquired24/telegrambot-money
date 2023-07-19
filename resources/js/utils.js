const Util = {}

Util.baseConfig = () => {
    let response = {
        baseApi: null,
        baseUrl: null
    }
    const storage = localStorage.getItem('MONEY_CONFIG')
    if(storage) {
        response = JSON.parse(storage)
    } // endif

    return response
}

Util.updateState = (setState, newState) => {
    setState(prev => {
        return {...prev,...newState}
    })
}

Util.getTextColor = function (trx) {
    return trx.is_expense == 1 ? 'text-danger' : 'text-success'
}
Util.getTextKind = function (trx) {
    return trx.is_expense == 1 ? 'Pengeluaran' : 'Pemasukan'
}
Util.getToday = function() {
    const today = new Date();
    const dd = String(today.getDate()).padStart(2, '0');
    const mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
    const yyyy = today.getFullYear();

    return [yyyy, mm, dd].join('-')
}

export default Util;
