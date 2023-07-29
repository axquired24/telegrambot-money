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

const isFloat = (n) => {
    return Number(n) === n && n % 1 !== 0;
}

const formatter = (amt) => {
    if (amt) {
        amt = amt.toString()?.split('.');
        return amt[0]?.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    return "0"
}

Util.formatRupiah = (amount, prefix = 'Rp ', rounding = true) => {
    if (rounding) {
        amount = Math.floor(amount)
    } // endif

    const isNegative = amount < 0
    let finalResp = amount ? formatter(Math.abs(amount)) : '0'
    finalResp = prefix + finalResp

    if (isFloat(amount) && (!rounding)) {
        const split = amount.toString().split('.')
        finalResp += `,${split[1]}`
    }

    if (isNegative) {
        finalResp = '-' + finalResp
    } // endif

    return finalResp
}

Util.percentageFrom = (amount, total) => {
    return Math.round(amount * 100 / total)
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
