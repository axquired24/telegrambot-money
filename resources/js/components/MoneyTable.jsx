import React from "react";
import Util from "../utils";
import { Table } from "react-bootstrap";

const MoneyTable = ({list}) => {
    return (
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
                                <td>{trx.from.username}</td>
                                <td style={{ width: '100px' }}>
                                    <button type="button"
                                        data-bs-toggle="modal" data-bs-target="#editModal"
                                        className="btn btn-sm btn-primary" title="Ubah">
                                        <i className="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button"
                                        data-bs-toggle="modal" data-bs-target="#deleteModal"
                                        className="btn btn-sm btn-danger" title="Hapus">
                                        <i className="bi bi-trash3-fill"></i>
                                    </button>
                                </td>
                            </tr>
                        ))
                    }
                </tbody>
            </Table>
        </div>
    );
}

export default MoneyTable;
