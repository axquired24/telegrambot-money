import React, {useState, useEffect} from 'react';
import axios from 'axios';
import Util from '../utils';
import { Button, Col, Row } from 'react-bootstrap';

const MoneyFilter = ({
    isLoadingList,
    froms,
    topics,

    // function
    onGetList,
    setErrMsg
}) => {
    const [state, setState] = useState({
        // selected item
        fromID: "",
        topicID: "",
        bulan: Util.getToday({monthYear: true}),
        isSendingReport: false,
        reportSentNotif: false
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
            topicID: e.target.value
        })
    }

    const onSubmitFilter = () => {
        onGetList({
            fromID: state.fromID,
            topicID: state.topicID,
            bulan: state.bulan
        })
    }

    const onSendReport = async () => {
        setErrMsg()
        if([state.fromID, state.topicID].filter(x => !! x).length < 2 ) {
            setErrMsg('Pengirim dan Subgroup tidak boleh kosong')
            return false
        } // endif

        try {
            Util.updateState(setState, {isSendingReport: true})
            await axios.post('/api/bot/sendreport', {
                bulan: state.bulan,
                from: state.fromID,
                topic: state.topicID
            })
            Util.updateState(setState, {isSendingReport: false, reportSentNotif: true})
            setTimeout(() => {
                Util.updateState(setState, {reportSentNotif: false})
            }, 600)
        } catch (e) {
            Util.updateState(setState, {isSendingReport: false})
            setErrMsg('Gagal mengirim Laporan')
        }
    }

    // useEffect(() => {
    //     const getMasterData = async () => {
    //         try {
    //             const response = await axios.get('/api/masterdata')
    //             Util.updateState(setState, {
    //                 froms: response.data.froms,
    //                 topics: response.data.topics,
    //                 chatrooms: response.data.chatrooms,
    //                 bulan: response.data.bulan
    //             })
    //         } catch {
    //             console.log(e)
    //         } // endtry catch
    //     }

    //     getMasterData().catch(e => {})
    // }, []);

    return (
        <>
            <Row className="g-2 mx-auto">
                <Col xs={12} xl={4}>
                    <label>Bulan</label>
                    <input type="month" className="form-control"
                        name="bulan"
                        onChange={onChangeBulan}
                        value={state.bulan}
                        lang="id-ID"
                        placeholder="Pilih Bulan" role="button" />
                </Col>
                <Col xs={12} xl={4}>
                    <label>Pengirim</label>
                    <select className="form-control" name="from" placeholder="Pengirim"
                        onChange={onChangeFrom}
                        >
                        <option value="">Semua Pengirim</option>
                        {
                            froms.map((from) => {
                                return (
                                    <option key={from.id} value={from.id}>{from.username}</option>
                                )
                            })
                        }
                    </select>
                </Col>
                <Col xs={12} xl={4}>
                    <label>Subgroup</label>
                    <select className="form-control" name="chatroom" placeholder="Chatroom"
                        onChange={onChangeChatroom}>
                        <option value="">Semua Subgroup</option>
                        {
                            topics.map((cr) => {
                                return (
                                    <option key={cr.id} value={cr.id}>{cr.name}</option>
                                )
                            })
                        }
                    </select>
                </Col>
            </Row>
            <div className="row g-2 gap-1 mt-2 justify-content-end">
                <div className="col-xs-6 col-lg-3 d-grid">
                    {
                        state.reportSentNotif ?
                        <Button variant="success" size="lg">
                            <i className="bi bi-chat-right-dots-fill"></i>
                            <span className='px-1'>Terkirim!</span>
                        </Button> :
                        <Button variant="secondary"
                            onClick={onSendReport} disabled={state.isSendingReport}>
                            <i className="bi bi-chat-right-dots-fill"></i>
                            <span className='px-1'>{ state.isSendingReport ? 'Loading ...' : 'Kirim Laporan' }</span>
                        </Button>
                    }
                </div>
                <div className="col-xs-6 col-lg-3 d-grid">
                    <Button variant="primary"
                        onClick={onSubmitFilter} disabled={isLoadingList}>
                        <i className="bi bi-funnel-fill"></i>
                        <span className='px-1'>{ isLoadingList ? 'Loading ' : 'Filter' }</span>
                    </Button>
                </div>
            </div>
        </>
    );
}


export default MoneyFilter;
