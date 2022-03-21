import React from "react";
import Api from "../service/Api";
import Alert from "react-bootstrap/Alert";
import Badge from "react-bootstrap/Badge";
import Button from "react-bootstrap/Button";
import Card from "react-bootstrap/Card";
import Col from "react-bootstrap/Col";
import Container from "react-bootstrap/Container";
import Form from "react-bootstrap/Form";
import InputGroup from "react-bootstrap/InputGroup";
import Row from "react-bootstrap/Row";
import Spinner from "react-bootstrap/Spinner";
import Toast from "react-bootstrap/Toast";
import ToastContainer from "react-bootstrap/ToastContainer";

export default class Home extends React.Component {
    state = {
        data: null,
        error: null,
        innerError: null,
        innerSuccess: null,
        selectedInput: {},
        adBreakDuration: {},
        loading: {},
    };

    /**
     * @var {Api}
     */
    #api;

    /**
     * Constructor
     *
     * @param props
     */
    constructor(props) {
        super(props);
        this.switchInput = this.switchInput.bind(this);
        this.addAdBreak = this.addAdBreak.bind(this);
        this.#api = new Api();
    }

    /**
     * Hook para quando o componente é montado pela primeira vez
     */
    componentDidMount() {
        this.#api.listChannels().then((response) => {
            if ((!response.data.statusCode) || (response.data.statusCode !== 200)) {
                this.setState({
                    error: response.data.error || 'Um erro desconhecido ocorreu'
                });
                return;
            }

            const data = {},
                adBreakDuration = {},
                selectedInput = {},
                loading = {};
            if ((response.data.data) && (response.data.data.length)) {
                response.data.data.forEach((channel) => {
                    data[channel.id] = channel;
                    adBreakDuration[channel.id] = 15;
                    loading[channel.id] = false;

                    selectedInput[channel.id] = null;
                    if (channel.inputs?.length) {
                        channel.inputs.sort();
                        const inputActive = channel.inputs?.filter(input => input.active);
                        if ((inputActive) && (inputActive[0])) {
                            selectedInput[channel.id] = inputActive[0]['name'];
                        }
                    }
                });
            }

            this.setState({
                data,
                adBreakDuration,
                selectedInput,
                loading,
            });
        }).catch((err) => {
            console.error(err);
            this.setState({
                error: err.response?.data?.error?.description || 'Um erro desconhecido ocorreu'
            });
        });
    }

    /**
     * Invoca a API para trocar o input do canal
     *
     * @param {number} channelId
     * @returns {boolean}
     */
    switchInput(channelId) {
        channelId = Number(channelId);
        if (channelId < 1) {
            this.setState({
                innerError: 'Por favor, realize essa operação em um canal'
            });
            return false;
        }

        if (!this.state.selectedInput[channelId]) {
            this.setState({
                innerError: 'Por favor, selecione a entrada que será a principal'
            });
            return false;
        }

        const loading = this.state.loading;
        loading[channelId] = true;
        this.setState({
            loading,
        });

        const inputName = this.state.selectedInput[channelId];
        this.#api.switchInputTo(channelId, inputName).then((response) => {
            const loading = this.state.loading;
            loading[channelId] = false;

            if ((!response.data.statusCode) || (response.data.statusCode !== 200)) {
                this.setState({
                    innerSuccess: null,
                    innerError: response.data.error || 'Um erro desconhecido ocorreu',
                    loading,
                });
            }

            if ((this.state.data[channelId]) &&
                ('inputs' in this.state.data[channelId])) {
                this.state.data[channelId].inputs.forEach((input) => {
                    input.active = input.name === this.state.selectedInput[channelId];
                });
            }

            this.setState({
                innerSuccess: `Entrada alterada com sucesso para ${inputName}`,
                loading,
            });
        }).catch((err) => {
            const loading = this.state.loading;
            loading[channelId] = false;

            console.error(err);
            this.setState({
                innerSuccess: null,
                innerError: err.response?.data?.error?.description || 'Um erro desconhecido ocorreu',
                loading,
            });
        });
    }

    /**
     * Invoca a API para inserir um anúncio comercial
     *
     * @param {number} channelId
     * @returns {boolean}
     */
    addAdBreak(channelId) {
        channelId = Number(channelId);
        if ((isNaN(channelId)) || (channelId < 1)) {
            this.setState({
                innerError: 'Por favor, realize essa operação em um canal'
            });
            return false;
        }

        if (this.state.adBreakDuration[channelId] < 1) {
            this.setState({
                innerError: 'Os breaks devem ser maiores que 1s'
            });
            return false;
        }
        if (this.state.adBreakDuration[channelId] > 300) {
            this.setState({
                innerError: 'Os breaks devem ser menores que 300s'
            });
            return false;
        }

        const duration = this.state.adBreakDuration[channelId];
        this.#api.insertAdBreak(channelId, duration).then((response) => {
            const loading = this.state.loading;
            loading[channelId] = false;

            if ((!response.data.statusCode) || (response.data.statusCode !== 200)) {
                this.setState({
                    innerSuccess: null,
                    innerError: response.data.error || 'Um erro desconhecido ocorreu',
                    loading,
                });
            }

            this.setState({
                innerSuccess: `Intervalo de ${duration}s inserido com sucesso`,
                loading,
            });
        }).catch((err) => {
            const loading = this.state.loading;
            loading[channelId] = false;

            console.error(err);
            this.setState({
                innerSuccess: null,
                innerError: err.response?.data?.error?.description || 'Um erro desconhecido ocorreu',
                loading,
            });
        });
    }


    render() {
        return (
            <>
                <Container className="py-4">
                    <h1 className="h4 mb-0">Canais</h1>
                    <p className="lead">Assista e gerencie seus canais ao vivo</p>
                    {this.doRender()}
                </Container>
                <ToastContainer className="p-3" position="bottom-end">
                    {(this.state.innerError) ? (
                        <Toast bg="warning"
                               onClose={() => this.setState({innerError: null})}
                               delay={5000} autohide>
                            <Toast.Body>{this.state.innerError}</Toast.Body>
                        </Toast>
                    ) : null}
                    {(this.state.innerSuccess) ? (
                        <Toast bg="success"
                               onClose={() => this.setState({innerSuccess: null})}
                               delay={5000} autohide>
                            <Toast.Body>{this.state.innerSuccess}</Toast.Body>
                        </Toast>
                    ) : null}
                </ToastContainer>
            </>
        )
    }

    doRender() {
        if (this.state.error !== null) {
            return (
                <Alert variant="warning">
                    {this.state.error}
                </Alert>
            );
        }

        if (this.state.data === null) {
            return (
                <Spinner animation="border" role="status">
                    <span className="visually-hidden">Carregando...</span>
                </Spinner>
            );
        }

        const channelIds = Object.keys(this.state.data),
            count = channelIds.length;
        return (
            <>
                <Alert variant="outline-info" className="alert-sm">
                    Atenção: cada ação executada demora aproximadamente 30 segundos para ser refletida
                </Alert>
                <Row>
                    {channelIds.map((channelId) => this.renderChannel(this.state.data[channelId]))}
                </Row>
                <p className="mt-4 text-muted">
                    {count} cana{(count === 1) ? 'l' : 'is'} encontrado{(count === 1) ? '' : 's'}
                </p>
            </>
        );
    }

    renderChannel(channel) {
        let stateClassName;
        switch (channel['state']) {
            case 'RUNNING':
                stateClassName = 'success';
                break;

            case 'CREATE_FAILED':
            case 'UPDATE_FAILED':
                stateClassName = 'warning';
                break;

            case 'DELETING':
            case 'DELETED':
                stateClassName = 'danger';
                break;

            default:
                stateClassName = 'dark';
        }
        return (
            <Col key={channel.id} md={6}>
                <Card key={channel.id}>
                    <Card.Header className="d-flex justify-content-between align-items-center">
                        <Card.Title as="h6" className="my-1">
                            {channel.name}
                        </Card.Title>
                    </Card.Header>
                    <Card.Body>
                        <Form.Group as={Row} className="align-items-center mb-3" controlId="form-name">
                            <Form.Label column sm={2} className="mb-0">ID</Form.Label>
                            <Col md={10}>
                                <code>{channel.id}</code>
                            </Col>
                        </Form.Group>

                        <Form.Group as={Row} className="align-items-center mb-3" controlId="form-name">
                            <Form.Label column sm={2} className="mb-0">Estado</Form.Label>
                            <Col md={10}>
                                <Badge bg={stateClassName}>{channel.state}</Badge>
                            </Col>
                        </Form.Group>

                        <Form.Group as={Row} className="align-items-center mb-3" controlId="form-name">
                            <Form.Label column sm={2} className="mb-0">URL</Form.Label>
                            <Col md={10}>
                                <a href={channel.destination} target="_blank" rel="noreferrer">
                                    {channel.destination}
                                </a>
                            </Col>
                        </Form.Group>

                        <Form.Group as={Row} className="align-items-center mb-3" controlId="form-inputs">
                            <Form.Label column sm={2} className="mb-0">Entrada</Form.Label>
                            <Col md={7}>
                                <Form.Select name="inputs"
                                             value={this.state.selectedInput[channel.id] ?? ''}
                                             onChange={(e) => {
                                                 const selectedInput = this.state.selectedInput || {};
                                                 selectedInput[channel.id] = e.target.value;
                                                 this.setState({selectedInput});
                                             }}>
                                    {channel.inputs?.map((input) => (
                                        <option key={input['id']} value={input['name']}>{input['name']}</option>
                                    ))}
                                </Form.Select>
                            </Col>
                            <Col md={3} className="text-nowrap">
                                <Button className="w-100"
                                        title="Mudar entrada atual para a selecionada"
                                        variant="primary"
                                        onClick={() => this.switchInput(channel.id)}
                                        disabled={this.state.loading[channel.id]}>
                                    Chavear
                                </Button>
                            </Col>
                        </Form.Group>

                        <Form.Group as={Row} className="align-items-center mb-3" controlId="form-inputs">
                            <Form.Label column sm={2} className="mb-0">Anúncio</Form.Label>
                            <Col md={7}>
                                <InputGroup>
                                    <Form.Control
                                        type="number"
                                        min={15}
                                        max={300}
                                        placeholder="Duration"
                                        aria-label="Duration"
                                        aria-describedby="addon-seconds"
                                        value={this.state.adBreakDuration[channel.id]}
                                        onChange={(e) => {
                                            if (e.target.reportValidity()) {
                                                const adBreakDuration = this.state.adBreakDuration;
                                                adBreakDuration[channel.id] = parseInt(e.target.value);
                                                this.setState({adBreakDuration});
                                            }
                                        }}
                                    />
                                    <InputGroup.Text id="addon-seconds">segundos</InputGroup.Text>
                                </InputGroup>
                            </Col>
                            <Col md={3} className="text-nowrap">
                                <Button className="w-100"
                                        disabled={this.state.loading[channel.id]}
                                        variant="primary"
                                        onClick={() => this.addAdBreak(channel.id)}
                                        title="Inserir um intervalo comercial com a duração informada">
                                    Inserir
                                </Button>
                            </Col>
                        </Form.Group>
                    </Card.Body>
                </Card>
            </Col>
        );
    }
}
