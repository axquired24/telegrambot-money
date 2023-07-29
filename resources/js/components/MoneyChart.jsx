import React, { useEffect, useState } from 'react'
import sumBy from 'lodash.sumby'
import Util, { EMPTY_CATEGORY } from '../utils';

const CustomProgress = ({amount, total, category}) => {
    const percent = Util.percentageFrom(amount, total)

    return (
        <div>
            <div className="fs-6 mb-1" style={{color: category.color}}>{category.name} ({Util.formatRupiah(amount)})</div>
            <div className="progress">
                <div className="progress-bar"
                    role="progressbar"
                    style={{width: percent + "%", backgroundColor: category.color}}>
                </div>
            </div>
        </div>
    )
}
const MoneyChart = ({list, categories}) => {
    const [state, setState] = useState({
        totalExpense: 0,
        totalIncome: 0,
        amountPerCategory: []
    });

    useEffect(() => {
        const formatList = (listParam, categoriesParam) => {
            const slimList = listParam.map(item => {
                return {
                    amount: item.amount,
                    category_id: item.category?.id,
                    is_expense: item.is_expense
                }
            })

            const totalExpense = sumBy(slimList.filter(x => x.is_expense), 'amount')
            const totalIncome = sumBy(slimList.filter(x => !x.is_expense), 'amount')
            const amountPerCategory = []

            categoriesParam.forEach(item => {
                const tempResult = {
                    category: item,
                    is_expense: item.is_expense
                }

                tempResult.amount = sumBy(slimList.filter(x => x.category_id === item.id), 'amount')

                if(item.is_expense) {
                    tempResult.total = totalExpense
                } else {
                    tempResult.total = totalIncome
                }

                amountPerCategory.push(tempResult)
            })

            const otherIncome = {
                category: {...EMPTY_CATEGORY, is_expense: 0},
                is_expense: 0,
                total: totalIncome,
                amount: sumBy(slimList.filter(x => {
                    return (! x.category_id) && (! x.is_expense)
                }), 'amount')
            }
            const otherExpense = {
                category: {...EMPTY_CATEGORY, is_expense: 1},
                is_expense: 1,
                total: totalExpense,
                amount: sumBy(slimList.filter(x => {
                    return (! x.category_id) && (x.is_expense)
                }), 'amount')
            }
            amountPerCategory.push(otherIncome)
            amountPerCategory.push(otherExpense)

            setState({
                totalExpense,
                totalIncome,
                amountPerCategory
            })
        }

        formatList(list, categories)
    }, [list, categories]);

    return (
        <div>
            {
                (state.amountPerCategory.length > 0) &&
                (
                    <>
                        <h5 className={Util.getTextColor({is_expense: 0})}>Pemasukan</h5>
                        <div className="d-flex flex-column gap-2">
                            {
                                state.amountPerCategory.filter(x => (!x.is_expense)).map((item) => {
                                    return (
                                        <CustomProgress
                                            key={item.category.id}
                                            amount={item.amount}
                                            total={item.total}
                                            category={item.category}
                                        />
                                    )
                                })
                            }
                        </div>

                        <hr className="mt-4" />
                        <h5 className={Util.getTextColor({is_expense: 1})}>Pengeluaran</h5>
                        <div className="d-flex flex-column gap-2">
                            {
                                state.amountPerCategory.filter(x => x.is_expense).map((item) => {
                                    return (
                                        <CustomProgress
                                            key={item.category.id}
                                            amount={item.amount}
                                            total={item.total}
                                            category={item.category}
                                        />
                                    )
                                })
                            }
                        </div>
                    </>
                )
            }
        </div>
    );
}

export default MoneyChart;
