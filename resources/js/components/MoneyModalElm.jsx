import React, { useEffect, useState } from "react";
import Util, { MODAL_KIND } from "../utils";
import { Button, Modal } from "react-bootstrap";

const MoneyModalElm = ({
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

export default MoneyModalElm;
