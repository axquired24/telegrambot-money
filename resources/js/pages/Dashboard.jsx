import React, {useState, useEffect} from 'react';
import Layout from '../components/Layout';
import MoneySummary from '../components/MoneySummary';
import axios from 'axios';
import MoneyFilter from '../components/MoneyFilter';
import Util from '../utils';
import MoneyTable from '../components/MoneyTable';
import { Alert, Spinner } from 'react-bootstrap';

const Dashboard = () => {
    const summary = {
        expense: 0,
        income: 0,
        balance: 0,
        failedParsed: 0
    }
    const [state, setState] = useState({
        isLoadingList: true,
        summary,
        list: [],
        errMsg: null
    });

    const setErrMsg = (msg=null) => {
        Util.updateState(setState, {errMsg: msg});
    }

    const getMoneyList = async (prop=null) => {
        try {
            Util.updateState(setState, {
                isLoadingList: true,
                errMsg: null
            })
            const url = '/api/money/list'
            // const {fromID, chatroomID, bulan} = prop
            const postData = prop ? prop : {}
            const response = await axios.post(url, postData)
            Util.updateState(setState, {...response.data, isLoadingList: false, errMsg: null})
        } catch (e) {
            Util.updateState(setState, {
                isLoadingList: false,
                errMsg: 'Gagal memuat data'
            })
            console.log(e);
        }
    }

    useEffect(() => {
        getMoneyList().catch(e => {})
    }, []);

    return (
        <Layout>
            <h3>
                Dashboard
            </h3>
            <MoneySummary summaryData={state.summary} />
            <MoneyFilter isLoadingList={state.isLoadingList}
                setErrMsg={setErrMsg}
                onGetList={getMoneyList} />
            <Alert hidden={! state.errMsg} className='mt-4' variant='danger'>{state.errMsg}</Alert>
            <MoneyTable
                list={state.list}
                getMoneyList={getMoneyList}
                setErrMsg={setErrMsg}
                isLoadingList={state.isLoadingList} />
        </Layout>
    );
}

export default Dashboard;
