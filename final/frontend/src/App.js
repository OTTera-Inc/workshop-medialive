import {BrowserRouter, Route, Routes} from "react-router-dom";
import Home from "./layout/Home";
import Container from "react-bootstrap/Container";
import Navbar from "react-bootstrap/Navbar";
import React from "react";

function App() {
    return (
        <div className="App">
            <Navbar bg="light" expand="lg">
                <Container>
                    <Navbar.Brand href="/">Workshop AWS MediaLive</Navbar.Brand>
                    <a href="https://ottera.tv" target="_blank" rel="noreferrer">
                        <img src={process.env.PUBLIC_URL + '/logo.png'} alt="OTTera" />
                    </a>
                </Container>
            </Navbar>
            <BrowserRouter>
                <Routes>
                    <Route path="/" element={<Home/>}/>
                </Routes>
            </BrowserRouter>
        </div>
    );
}

export default App;
