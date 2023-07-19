import React, {useState, useEffect} from 'react';
import axios from 'axios';
import Util from '../utils';
import { Button } from 'react-bootstrap';

const MoneyFilter = ({
    isLoadingList,
    onGetList
}) => {
    const [state, setState] = useState({
        froms: [],
        chatrooms: [],

        // selected item
        fromID: "",
        chatroomID: "",
        bulan: ""
    });

    const onChangeBulan = (e) => {
        Util.updateState(setState, {
            bulan: e.target.value
        })
    }

    const onChangeFrom = (e) => {
        Util.updateState(setState, {
            fromID: e.target.value
        })
    }

    const onChangeChatroom = (e) => {
        Util.updateState(setState, {
            chatroomID: e.target.value
        })
    }

    const onSubmitFilter = () => {
        onGetList({
            fromID: state.fromID,
            chatroomID: state.chatroomID,
            bulan: state.bulan
        })
    }

    useEffect(() => {
        const getMasterData = async () => {
            try {
                const response = await axios.get('/api/masterdata')
                Util.updateState(setState, {
                    froms: response.data.froms,
                    chatrooms: response.data.chatrooms,
                    bulan: response.data.bulan
                })
            } catch {
                console.log(e)
            } // endtry catch
        }

        getMasterData().catch(e => {})
    }, []);

    return (
        <div className="mt-4 row g-2 align-items-end">
            <div className="col-sm-12 col-xl-8">
                <div className="row gap-0">
                    <div className="col-4">
                        <label>Bulan</label>
                        <input type="month" className="form-control"
                            name="bulan"
                            onChange={onChangeBulan}
                            value={state.bulan}
                            lang="id-ID"
                            placeholder="Pilih Bulan" role="button" />
                    </div>
                    <div className="col-4">
                        <label>Pengirim</label>
                        <select className="form-control" name="from" placeholder="Pengirim"
                            onChange={onChangeFrom}
                            >
                            <option value="">Semua Pengirim</option>
                            {
                                state.froms.map((from) => {
                                    return (
                                        <option key={from.id} value={from.id}>{from.username}</option>
                                    )
                                })
                            }
                        </select>
                    </div>
                    <div className="col-4">
                        <label>Grup</label>
                        <select className="form-control" name="chatroom" placeholder="Chatroom"
                            onChange={onChangeChatroom}>
                            <option value="">Semua Chatroom</option>
                            {
                                state.chatrooms.map((cr) => {
                                    return (
                                        <option key={cr.id} value={cr.id}>{cr.title}</option>
                                    )
                                })
                            }
                        </select>
                    </div>
                </div>
            </div>
            <div className="col-sm-12 col-xl-4 d-flex gap-2 justify-content-between">
                <div>
                    <Button variant="primary" onClick={onSubmitFilter}>
                        <i className="bi bi-funnel-fill"></i>
                        <span>Filter</span>
                    </Button>
                </div>
                <div>
                    <Button variant="secondary">
                        <i className="bi bi-chat-right-dots-fill"></i>
                        <span>{ isLoadingList ? 'Loading ...' : 'Kirim Laporan' }</span>
                    </Button>
                </div>
            </div>
        </div>
    );
}


export default MoneyFilter;
