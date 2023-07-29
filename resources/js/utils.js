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
Util.getToday = function({monthYear=false}) {
    const today = new Date();
    const dd = String(today.getDate()).padStart(2, '0');
    const mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
    const yyyy = today.getFullYear();

    if(monthYear) {
        return [yyyy, mm].join('-')
    } // endif
    return [yyyy, mm, dd].join('-')
}

export const MODAL_KIND = {
    DELETE: 'delete',
    EDIT: 'edit',
    ADD: 'add',
}
export const EMPTY_CATEGORY = {
    id: 0,
    name: 'Lainnya',
    color: '#525252'
}

export default Util;
