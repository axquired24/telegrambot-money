import React, { useEffect, useState } from "react";
import Util, { MODAL_KIND } from "../utils";
import { Button, Spinner, Table } from "react-bootstrap";
import CategoryCapsule from "./CategoryCapsule";
import MoneyModalElm from "./MoneyModalElm";

const MoneyTable = ({
    list,
    categories,
    isLoadingList,
    getMoneyList,
    setErrMsg
}) => {
    const [state, setState] = useState({
        modalKind: null,
        selectedItem: null,
        isFetchingToday: false,
        isFetchingTodayDone: false
    });

    const openModal = (trx, modalKind) => {
        Util.updateState(setState, {selectedItem: trx, modalKind})
    }
    const closeModal = (trx) => {
        Util.updateState(setState, {selectedItem: null, modalKind: null})
    }
    const newTrx = (is_expense=1) => {
        const trx = {
            id: '',
            is_expense,
            trx_date: Util.getToday(),
            amount: 0,
            description: ''
        }
        openModal(trx, MODAL_KIND.ADD)
    }

    const errSync = () => {
        Util.updateState(setState, {isFetchingToday: false })
        setErrMsg('Error saat sinkronisasi, cek log untuk detail.')
    }

    const syncToday = async () => {
        setErrMsg()
        Util.updateState(setState, {isFetchingToday: true})
        await axios.get('/api/bot/daily').then(() => {
            axios.get('/api/db/parse').then(() => {
                Util.updateState(setState, {isFetchingToday: false, isFetchingTodayDone: true})
                getMoneyList()
                setTimeout(() => {
                    Util.updateState(setState, {isFetchingTodayDone: false})
                }, 600)
            }).catch(err => {
                errSync()
            });
        }).catch(err => {
            errSync()
        })
    }

    const loadingElm = <div className='mt-5 d-flex flex-column justify-content-center align-items-center'>
        <div>
            <Spinner />
        </div>
        <div className='block mt-1'>Loading ...</div>
    </div>

    return (
        isLoadingList ? loadingElm :
        <>
            {/* @TODO(albert): required fromID & chatroomID */}
            <div className="d-flex justify-content-start mt-4 gap-2">
                {/* <Button variant="success" onClick={() => newTrx(0)}>
                    + Pemasukan
                </Button>
                <Button variant="danger" onClick={() => newTrx(1)}>
                    + Pengeluaran
                </Button> */}
                <Button variant={state.isFetchingTodayDone ? "success" : "primary"}
                    disabled={state.isFetchingToday}
                    onClick={syncToday}>
                    <Spinner size="sm" animation="grow" />
                    <span className="mx-1">
                        { state.isFetchingTodayDone ? 'Sukses!' : 'Fetch Hari Ini' }
                    </span>
                </Button>
            </div>
            <div className="table-responsive mt-4">
                <Table striped bordered hover style={{minWidth: '800px', width: '100%'}}>
                    <caption>History Keuangan</caption>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Nominal</th>
                            <th>Deskripsi</th>
                            <th>Tipe</th>
                            <th>Sender</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {
                            list.map((trx, idx) => (
                                <tr key={idx}>
                                    <td>{trx.trx_date_format}</td>
                                    <td className={Util.getTextColor(trx)}>{trx.amount_format}</td>
                                    <td>{trx.description}</td>
                                    <td>
                                        <div>
                                            <span className={Util.getTextColor(trx)}> {Util.getTextKind(trx)} </span> <br />
                                            <CategoryCapsule categories={categories} trx={trx} />
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            {trx.from.username} <br />
                                            <code>👥 {trx.topic.name}</code>
                                        </div>
                                    </td>
                                    <td style={{ width: '100px' }}>
                                        <Button size="sm" variant="primary"
                                            onClick={() => openModal(trx, MODAL_KIND.EDIT)}
                                            className="mx-1" title="Ubah">
                                            <i className="bi bi-pencil-square"></i>
                                        </Button>
                                        <Button size="sm" variant="danger"
                                            onClick={() => openModal(trx, MODAL_KIND.DELETE)}
                                            title="Hapus">
                                            <i className="bi bi-trash3-fill"></i>
                                        </Button>
                                    </td>
                                </tr>
                            ))
                        }
                    </tbody>
                </Table>
            </div>

            <MoneyModalElm
                selectedItem={state.selectedItem}
                closeModal={closeModal}
                getMoneyList={getMoneyList}
                modalKind={state.modalKind} />
        </>
    );
}

export default MoneyTable;
