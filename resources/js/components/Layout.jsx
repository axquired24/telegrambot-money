import { Link } from "react-router-dom";
import Container from 'react-bootstrap/Container';
import Row from 'react-bootstrap/Row';
import Col from 'react-bootstrap/Col';

const Layout = ({children}) => {
    const menus = (
        <Row>
            <Col><Link to="/dashboard">Dashboard</Link></Col>
            <Col><Link to="/invalid-msg">Invalid Message</Link></Col>
        </Row>
    )
    return (
        <Container className="mt-4">
            {children}
            {/* <Row>
                <Col md={3}>{menus}</Col>
                <Col>{children}</Col>
            </Row> */}
        </Container>
    );
}

export default Layout;
