import React, {useState, useEffect} from 'react';
import Layout from '../components/Layout';
import MoneySummary from '../components/MoneySummary';
import axios from 'axios';
import MoneyFilter from '../components/MoneyFilter';
import Util from '../utils';
import MoneyTable from '../components/MoneyTable';
import { Alert } from 'react-bootstrap';
import uniqBy from 'lodash.uniqby';

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
        froms: [],
        topics: [],
        categories: [],
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
            const list = response?.data?.list ?? []

            let froms = list.map(x => x.from)
            froms = uniqBy(froms, 'id')
            let topics = list.map(x => x.topic)
            topics = uniqBy(topics, 'id')

            Util.updateState(setState, {
                ...response.data,
                froms,
                topics,
                isLoadingList: false,
                errMsg: null
            })
        } catch (e) {
            Util.updateState(setState, {
                isLoadingList: false,
                errMsg: 'Gagal memuat data'
            })
            console.log(e);
        }
    }

    const getMasterData = async () => {
        try {
            Util.updateState(setState, {
                errMsg: null
            })
            const url = '/api/masterdata'
            const response = await axios.get(url)
            const categories = response?.data?.categories

            Util.updateState(setState, {
                categories,
                errMsg: null
            })
        } catch (e) {
            Util.updateState(setState, {
                errMsg: 'Gagal memuat masterdata'
            })
            console.log(e);
        }
    }

    useEffect(() => {
        getMasterData().catch(e => {})
        getMoneyList().catch(e => {})
    }, []);

    return (
        <Layout>
            <h3>
                Dashboard
            </h3>
            <MoneySummary summaryData={state.summary} />
            <Alert hidden={! state.errMsg} className='mt-4' variant='danger'>{state.errMsg}</Alert>
            <MoneyFilter isLoadingList={state.isLoadingList}
                froms={state.froms}
                topics={state.topics}
                setErrMsg={setErrMsg}
                onGetList={getMoneyList} />
            <MoneyTable
                categories={state.categories}
                list={state.list}
                getMoneyList={getMoneyList}
                setErrMsg={setErrMsg}
                isLoadingList={state.isLoadingList} />
        </Layout>
    );
}

export default Dashboard;
