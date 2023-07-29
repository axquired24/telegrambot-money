import React from 'react';
import { Accordion, Col, Row } from 'react-bootstrap';
import { Link } from 'react-router-dom';
import { useNavigate } from 'react-router-dom';

const MoneySummary = ({summaryData}) => {
    const {
        expense,
        income,
        balance,
        failedParsed
    } = summaryData

    const goTo = useNavigate()

    const overallSum = (
        <Row className="gap-3 mx-auto">
            <Col className="border border-danger rounded rounded-1 py-2 px-3">
                <small className="fw-bold">Pengeluaran Bulan Ini</small>
                <div className="fs-5 text-danger">{ expense }</div>
            </Col>
            <Col className="border border-success rounded rounded-1 py-2 px-3">
                <small className="fw-bold">Pemasukan Bulan Ini</small>
                <div className="fs-5 text-success">{ income }</div>
            </Col>
            <Col className="border border-primary rounded rounded-1 py-2 px-3">
                <small className="fw-bold">Saldo Bulan Ini</small>
                <div className="fs-5 text-primary">{ balance }</div>
            </Col>
            <Col className="border border-danger rounded rounded-1 py-2 px-3"
                role="button" onClick={() => goTo("/invalid-msg")}>
                <small className="fw-bold">Invalid Chat</small>
                <div className="fs-5 text-danger">{ failedParsed } item</div>
            </Col>
        </Row>
    )

    return overallSum
}

export default MoneySummary;
