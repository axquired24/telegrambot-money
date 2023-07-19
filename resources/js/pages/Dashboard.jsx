import React, {useState, useEffect} from 'react';
import Layout from '../components/Layout';
import MoneySummary from '../components/MoneySummary';
import axios from 'axios';
import MoneyFilter from '../components/MoneyFilter';
import Util from '../utils';
import MoneyTable from '../components/MoneyTable';

const Dashboard = () => {
    const summary = {
        expense: 0,
        income: 0,
        balance: 0,
        failedParsed: 0
    }
    const [state, setState] = useState({
        summary,
        list: [],
        isLoadingList: false
    });

    const getMoneyList = async (prop=null) => {

        try {
            Util.updateState(setState, {
                isLoadingList: true
            })
            const url = '/api/money/list'
            // const {fromID, chatroomID, bulan} = prop
            const postData = prop ? prop : {}
            const response = await axios.post(url, postData)
            setState({...response.data, isLoadingList: false})
        } catch (e) {
            Util.updateState(setState, {
                isLoadingList: false
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
            <MoneyFilter isLoadingList={state.isLoadingList} onGetList={getMoneyList} />
            <MoneyTable list={state.list} />
        </Layout>
    );
}

export default Dashboard;
