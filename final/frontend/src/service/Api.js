import axios from "axios";

export default class Api {
    #uri = process.env.API_BASE_URL || 'http://localhost:8080';

    async listChannels() {
        return axios.get(this.#uri + '/channels');
    }

    async switchInputTo(channelId, inputName) {
        return axios.post(this.#uri + '/channels/' + encodeURIComponent(channelId) + '/input', {
            input: inputName
        });
    }

    async insertAdBreak(channelId, duration) {
        return axios.post(this.#uri + '/channels/' + encodeURIComponent(channelId) + '/ad-break', {
            duration: duration
        });
    }
}
