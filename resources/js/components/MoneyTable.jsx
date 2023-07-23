import React, { useEffect, useState } from "react";
import Util from "../utils";
import { Button, Modal, Spinner, Table } from "react-bootstrap";
import { MODAL_KIND } from "../utils";

const ModalElm = ({
    selectedItem=null,
    modalKind,
    closeModal,
    getMoneyList
}) => {
    const [modalSt, setModalSt] = useState({
        isDeletingTrx: false,
        isUpdatingTrx: false,

        // form
        form: selectedItem
    })

    const deleteTrx = async () => {
        try {
            Util.updateState(setModalSt, { isDeletingTrx: true })
            await axios.post('/api/trx/delete', {
                id: selectedItem.id
            })
            Util.updateState(setModalSt, { isDeletingTrx: false })
            getMoneyList()
            closeModal()
        } catch (e) {
            Util.updateState(setModalSt, { isDeletingTrx: false })
        }
    }

    const updateTrx = async () => {
        try {
            Util.updateState(setModalSt, { isUpdatingTrx: true })
            const url = modalKind === MODAL_KIND.EDIT ? '/api/trx/edit' : '/api/trx/add'
            await axios.post(url, {
                id: modalSt.form.id,
                is_expense: modalSt.form.is_expense,
                trx_date: modalSt.form.trx_date,
                amount: parseInt(modalSt.form.amount),
                description: modalSt.form.description
            })
            Util.updateState(setModalSt, { isUpdatingTrx: false })
            getMoneyList()
            closeModal()
        } catch (e) {
            Util.updateState(setModalSt, { isUpdatingTrx: false })
        }
    }

    const handleChange = (e, formKey) => {
        if(formKey === 'amount') {
            e.target.value = parseInt(e.target.value, 10)
        } // endif
        Util.updateState(setModalSt, {
            form: {
                ...modalSt.form,
                [formKey]: e.target.value
            }
        })
    }

    const formValidated = () => {
        const hasEmptyValue = [
            modalSt.form.trx_date,
            modalSt.form.amount,
            modalSt.form.description
        ].filter(x => !x).length > 0
        return ! hasEmptyValue
    }

    useEffect(() => {
        Util.updateState(setModalSt, { form: selectedItem })
    }, [selectedItem]);

    return (
        (modalSt.form && selectedItem) &&
        <>
            <Modal show={modalKind === MODAL_KIND.DELETE}>
                <Modal.Header>Hapus Item?</Modal.Header>
                <Modal.Body>
                    <div>
                        Data ini akan dihapus, apakah anda yakin?
                        <div>Waktu: { selectedItem.trx_date_format }</div>
                        <div className={ Util.getTextColor(selectedItem) }>
                            Nominal: { selectedItem.amount_format }
                        </div>
                        <div className={ Util.getTextColor(selectedItem) }>
                            Tipe: { Util.getTextKind(selectedItem) }
                        </div>
                    </div>
                </Modal.Body>
                <Modal.Footer>
                    <Button variant="secondary" onClick={closeModal}>
                        Batal
                    </Button>
                    <Button variant="danger" disabled={modalSt.isDeletingTrx} onClick={deleteTrx}>
                        Hapus
                    </Button>
                </Modal.Footer>
            </Modal>

            <Modal show={ [MODAL_KIND.EDIT, MODAL_KIND.ADD].includes(modalKind) }>
                <Modal.Header>
                    {
                        modalKind === MODAL_KIND.EDIT ? "Edit Item" : "Tambah Baru"
                    }
                </Modal.Header>
                <Modal.Body>
                    <>
                        <div className="row mb-3 align-items-center">
                            <label className="col-sm-2 col-form-label">Tipe</label>
                            <div className={ 'col-sm-10 ' + Util.getTextColor(modalSt.form) }>
                                { Util.getTextKind(modalSt.form) }
                            </div>
                        </div>
                        <div className="row mb-3">
                            <label className="col-sm-2 col-form-label">Waktu</label>
                            <div className="col-sm-10">
                                <input role="button" name="trx_date"
                                    type="date"
                                    onChange={(e) => handleChange(e, "trx_date")}
                                    className="form-control" value={modalSt.form.trx_date} required />
                            </div>
                        </div>
                        <div className="row mb-3">
                            <label className="col-sm-2 col-form-label">Nominal</label>
                            <div className="col-sm-10">
                                <input type="number" name="amount" min="1"
                                    onChange={(e) => handleChange(e, "amount")}
                                    className="form-control" value={modalSt.form.amount} required />
                            </div>
                        </div>
                        <div className="row mb-3">
                            <label className="col-sm-2 col-form-label">Deskripsi</label>
                            <div className="col-sm-10">
                                <input type="text" name="description"
                                    onChange={(e) => handleChange(e, "description")}
                                    className="form-control" value={modalSt.form.description} required />
                            </div>
                        </div>
                    </>
                </Modal.Body>
                <Modal.Footer>
                    <Button variant="secondary" onClick={closeModal}>
                        Batal
                    </Button>
                    <Button variant="primary"
                        disabled={modalSt.isUpdatingTrx || (! formValidated())}
                        onClick={updateTrx}>
                        Simpan
                    </Button>
                </Modal.Footer>
            </Modal>
        </>
    )
}

const MoneyTable = ({
    list,
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
                                    <td className={Util.getTextColor(trx)}>{Util.getTextKind(trx)}</td>
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

            <ModalElm
                selectedItem={state.selectedItem}
                closeModal={closeModal}
                getMoneyList={getMoneyList}
                modalKind={state.modalKind} />
        </>
    );
}

export default MoneyTable;
