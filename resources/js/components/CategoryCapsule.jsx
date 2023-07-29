import React, { useEffect, useState } from "react";
import Util from "../utils";
import { Button, Form, Row, Col, Spinner } from "react-bootstrap";
import { EMPTY_CATEGORY } from "../utils";

const CategoryCapsule = (props) => {
    const { trx, categories } = props

    let initialState = EMPTY_CATEGORY
    if(trx.category) {
        initialState = trx.category
    } // endif

    const [state, setState] = useState({
        showButton: 1,
        isUpdating: 0,
        ...initialState
    });

    const updateTrx = async (newId) => {
        try {
            Util.updateState(setState, {isUpdating: 1})
            await axios.post('/api/trx/category/edit', {
                id: trx.id,
                money_category_id: newId
            })
            Util.updateState(setState, {isUpdating: 0})
        } catch (e) {
            Util.updateState(setState, {isUpdating: 0})
        }
    }

    const onChange = async (e) => {
        const value = e.target.value
        // if(! confirm('Ubah kategori?')) {
        //     Util.updateState(setState, {showButton: 1})
        //     return false
        // } // endif

        let newId = parseInt(value)
        let selectedCat = EMPTY_CATEGORY

        if(EMPTY_CATEGORY.id !== newId) {
            selectedCat = categories.find(c => c.id === newId)
        } // endif
        await updateTrx(selectedCat.id)

        Util.updateState(setState, {
            showButton: 1,
            ...selectedCat
        })
    }

    const onSelectBlur = () => {
        Util.updateState(setState, {showButton: 1})
    }

    const onBtnClick = () => {
        Util.updateState(setState, {showButton: 0})
    }

    const selectCat = <Row className="g-2">
        <Col xs={9}>
            <Form.Select
                size="sm"
                tabIndex={0}
                onChange={onChange}
                onBlur={onSelectBlur} value={state.id}>

                <option value={EMPTY_CATEGORY.id}>{EMPTY_CATEGORY.name}</option>
                {
                    categories.filter(x => x.is_expense === trx.is_expense).map(val => (
                        <option key={val.id} value={val.id}>{val.name}</option>
                    ))
                }
            </Form.Select>
        </Col>
        <Col xs={2}>
            <Button size="sm" variant="secondary" onClick={onSelectBlur}>x</Button>
        </Col>
    </Row>

    const button = <div>
        <Button
            style={{backgroundColor: state.color, border: 'none'}}
            size="sm"
            onClick={onBtnClick} variant='secondary'>{state.name}</Button>
    </div>

    const getContent = () => {
        return state.showButton ? button : selectCat
    }

    return state.isUpdating ? <Spinner size="sm" style={{color: "#525252"}} /> : getContent()
}

export default CategoryCapsule;
