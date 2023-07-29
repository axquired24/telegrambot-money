import React, {useState, useEffect} from 'react';
import Layout from '../components/Layout';
import axios from 'axios';
import Util from '../utils';
import { useNavigate } from 'react-router-dom';
import { Alert, Button, Col, Modal, Row } from 'react-bootstrap';
import copy from 'copy-to-clipboard';

const prettyJsonOf = (selectedChat) => {
    return (
        <pre>
            { `Update ID: #${selectedChat.update_id}` }
            <br /><br />
            { JSON.stringify(JSON.parse(selectedChat?.message), undefined, 2) }
        </pre>
    )
}
const InvalidMsg = () => {
    const goTo = useNavigate()
    const [state, setState] = useState({
        isLoadingList: true,
        isSolvingChat: false,
        isParsingChat: false,

        showModal: false,
        selectedChat: null,

        errMsg: null,
        list: [],
    });

    const getInvalidList = async () => {
        try {
            Util.updateState(setState, {
                isLoadingList: true,
                errMsg: null
            })
            const response = await axios.get('/api/invalid/list')
            Util.updateState(setState, { isLoadingList: false, errMsg: null, list: response.data})
        } catch (e) {
            Util.updateState(setState, {
                isLoadingList: false,
                errMsg: 'Gagal memuat data'
            })
        }
    }

    const handleModalClose = () => {
        Util.updateState(setState, {
            showModal: false,
            selectedChat: null
        })
    }

    const showChatDetail = (chat) => {
        Util.updateState(setState, {
            showModal: true,
            selectedChat: chat
        })
    }

    const copyJson = () => {
        copy(
            JSON.stringify(JSON.parse(state.selectedChat?.message), undefined, 2)
        )
    }

    const markAsSolved = async () => {
        try {
            Util.updateState(setState, {
                isSolvingChat: true,
                errMsg: null
            })
            await axios.post('/api/invalid/solve', {
                update_id: state.selectedChat.update_id,
                error_solved: 1
            })
            Util.updateState(setState, { isSolvingChat: false, errMsg: null })
            getInvalidList().catch(e => {})
            handleModalClose()
        } catch (e) {
            Util.updateState(setState, {
                isSolvingChat: false,
                errMsg: 'Gagal tandai selesai untuk #' + state.selectedChat.update_id
            })
        }
    }

    const parseChat = async () => {
        try {
            Util.updateState(setState, {
                isParsingChat: true,
                errMsg: null
            })
            const resp = await axios.post('/api/chat/parse', {
                update_id: state.selectedChat.update_id
            })

            if(resp.data?.has_error) {
                throw new Error('Failed to parse chat')
            } else {
                Util.updateState(setState, { isParsingChat: false, errMsg: null })
                getInvalidList().catch(e => {})
                handleModalClose()
            } // endif
            // handleModalClose()
        } catch (e) {
            Util.updateState(setState, {
                isParsingChat: false,
                errMsg: 'Gagal parse chat #' + state.selectedChat.update_id
            })
        }
    }

    useEffect(() => {
        getInvalidList().catch(e => {})
    }, []);

    return (
        <Layout>
            <h3 role="button" onClick={ () => goTo('/dashboard') }>
                {"< Invalid Chats"}
            </h3>

            <Alert hidden={! state.errMsg} className='mt-4' variant='danger'>{state.errMsg}</Alert>
            <div className='mt-4 d-flex justify-content-end'>
                <Button hidden={state.isLoadingList} onClick={getInvalidList}>
                    { state.isLoadingList ? 'Loading...' : 'Refresh' }
                </Button>
            </div>
            <table class="table table-hover table-responsive mt-4">
                <caption>Invalid Chats</caption>
                <thead>
                    <tr>
                        <th>Update ID</th>
                        <th>Pesan</th>
                        <th>Last Proceeed</th>
                    </tr>
                </thead>
                <tbody>
                    {
                        state.list.map(chat => (
                            <tr key={chat.update_id}>
                                <td>{chat.update_id}</td>
                                <td>
                                    <a href="#" onClick={() => showChatDetail(chat)}>
                                        Click to Check
                                    </a>
                                </td>
                                <td>{chat.human_parsed_at}</td>
                            </tr>
                        ))
                    }
                </tbody>
            </table>

            <Modal size='lg' show={state.showModal} onHide={handleModalClose}>
                <Modal.Header closeButton>
                    <Modal.Title>Detail Chat</Modal.Title>
                </Modal.Header>
                <Modal.Body>
                    { state.selectedChat && prettyJsonOf(state.selectedChat) }
                </Modal.Body>
                <Modal.Footer>
                    <Button variant="primary" onClick={markAsSolved} disabled={state.isSolvingChat}>
                        { state.isSolvingChat ? 'Loading ...' : 'Tandai Selesai' }
                    </Button>
                    <Button variant="warning" onClick={parseChat}>
                        Parse Ulang
                    </Button>
                    <Button variant="secondary" onClick={copyJson}>
                        Copy Json
                    </Button>
                    <Button variant="secondary" onClick={handleModalClose}>
                        Close
                    </Button>
                </Modal.Footer>
            </Modal>
        </Layout>
    );
}

export default InvalidMsg;
